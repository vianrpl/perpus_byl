<?php

namespace App\Http\Controllers;

use App\Models\penataan_bukus; // Import model penataan_bukus untuk operasi database
use App\Models\bukus; // Import model bukus untuk relasi dan dropdown
use App\Models\raks;// Import model raks untuk relasi dan dropdown
use App\Models\buku_items; // Atau use App\Models\BukuItems; jika pakai camelCase
use App\Models\User;
use Illuminate\Http\Request; // Import Request untuk menangani input form
use Illuminate\Support\Facades\Validator; // Import Validator untuk validasi input
use Illuminate\Support\Facades\Auth; // Untuk auto user login
use Illuminate\Support\Facades\DB;

class PenataanBukusController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar penataan buku dengan pencarian dan pagination.
     */
    public function index(Request $request)
    {
        // Ambil query pencarian dari request
        $search = $request->query('search');

        // Buat query dengan relasi bukus, raks, dan user
        $query = penataan_bukus::with(['bukus', 'raks', 'user']);

        // Terapkan filter pencarian jika ada
        if ($search) {
            $query->whereHas('bukus', function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%');  // Sesuaikan field buku
            })->orWhereHas('raks', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Ambil data dengan pagination (10 per halaman)
        $penataanBukus = $query->paginate(10);

        // Ambil data buku dan rak untuk dropdown di modal
        $bukus = bukus::all();
        $raks = raks::all();

        // Kembalikan view index dengan data
        return view('penataan_bukus.index', compact('penataanBukus', 'bukus', 'raks'));
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan form untuk membuat penataan buku baru.
     */
    public function create()
    {
        // Ambil data buku dan rak untuk dropdown
        $bukus = bukus::all();
        $raks = raks::all();

        // Kembalikan view create
        return view('penataan_bukus.create', compact('bukus', 'raks'));
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan data penataan buku baru ke database setelah validasi.
     */
    public function store(Request $request)
    {
        // Validasi input dasar (sudah ada, ok)
        $validator = Validator::make($request->all(), [
            'id_buku' => 'required|exists:bukus,id_buku',
            'id_rak' => 'required|exists:raks,id_rak',
            'kolom' => 'required|integer|min:1',
            'baris' => 'required|integer|min:1',
            'jumlah' => 'required|integer|min:1',
            'sumber' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $buku = bukus::findOrFail($request->id_buku);
        $rak = raks::findOrFail($request->id_rak);

        // 1) Pastikan kategori sama (sudah ada, ok)
        if ($buku->id_kategori != $rak->id_kategori) {
            return redirect()->back()
                ->withErrors(['id_rak' => 'Rak tidak sesuai kategori buku. Pilih rak yang kategorinya sama.'])
                ->withInput();
        }

        // BARU: Validasi stok buku total (sum semua penataan + new)
        $existingSumBuku = $buku->penataan_bukus()->sum('jumlah');  // Sum existing penataan untuk buku ini
        $totalRequestedBuku = $existingSumBuku + $request->jumlah;
        if ($totalRequestedBuku > $buku->jumlah) {
            return redirect()->back()
                ->withErrors(['jumlah' => "Jumlah melebihi total eksemplar buku! Sisa: " . ($buku->jumlah - $existingSumBuku) . "."])
                ->withInput();
        }

        // BARU: Validasi kapasitas rak (sum semua penataan di rak + new)
        $existingSumRak = $rak->penataan_bukus()->sum('jumlah');
        $totalRequestedRak = $existingSumRak + $request->jumlah;
        if ($totalRequestedRak > $rak->kapasitas) {
            return redirect()->back()
                ->withErrors(['jumlah' => "Jumlah melebihi kapasitas rak! Sisa: " . ($rak->kapasitas - $existingSumRak) . "."])
                ->withInput();
        }

        // Lanjut transaction (kode lama ok, tapi tambah komentar)
        DB::transaction(function() use ($request, $buku, $rak) {
            // Cek existing penataan di posisi persis sama (rak, kolom, baris, buku)
            $existingPenataan = penataan_bukus::where('id_buku', $request->id_buku)
                ->where('id_rak', $request->id_rak)
                ->where('kolom', $request->kolom)
                ->where('baris', $request->baris)
                ->first();

            if ($existingPenataan) {
                // Merge: Tambah jumlah ke existing
                $existingPenataan->jumlah += $request->jumlah;
                $existingPenataan->id_user = Auth::id();
                $existingPenataan->modified_date = now();
                $existingPenataan->save();

                $desiredTotal = $existingPenataan->jumlah;
            } else {
                // Buat baru
                $penataan = new penataan_bukus();
                $penataan->id_buku = $request->id_buku;
                $penataan->id_rak = $request->id_rak;
                $penataan->kolom = $request->kolom;
                $penataan->baris = $request->baris;
                $penataan->jumlah = $request->jumlah;
                $penataan->sumber = $request->sumber;
                $penataan->id_user = Auth::id();
                $penataan->insert_date = now();
                $penataan->modified_date = now();
                $penataan->save();

                $desiredTotal = $penataan->jumlah;
            }

            // Hitung existing items di rak ini untuk buku ini
            $existingItemsCount = buku_items::where('id_buku', $request->id_buku)
                ->where('id_rak', $request->id_rak)
                ->count();

            // Delta: Buat item baru sebanyak kekurangan (sudah aman karena validasi atas)
            $toCreate = max(0, $desiredTotal - $existingItemsCount);

            for ($i = 0; $i < $toCreate; $i++) {
                buku_items::create([
                    'id_buku' => $request->id_buku,
                    'id_rak' => $request->id_rak,
                    'kondisi' => 'baik',
                    'status' => 'tersedia',
                    'sumber' => $request->sumber,
                ]);
            }
        });

        return redirect()->route('penataan_bukus.index')->with('success', 'Penataan berhasil disimpan dan eksemplar dibuat.');
    }


    /**
     * Display the specified resource.
     * Menampilkan detail penataan buku berdasarkan ID.
     */
    public function show(string $id)
    {
        // Ambil data penataan dengan relasi
        $penataan = penataan_bukus::with(['bukus', 'raks', 'user'])->findOrFail($id);

        // Kembalikan view show
        return view('penataan_bukus.show', compact('penataan'));
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan form untuk mengedit penataan buku.
     */
    public function edit(string $id)
    {
        // Ambil data penataan dan data untuk dropdown
        $penataan = penataan_bukus::findOrFail($id);
        $bukus = bukus::all();
        $raks = raks::all();

        // Kembalikan view edit
        return view('penataan_bukus.edit', compact('penataan', 'bukus', 'raks'));
    }

    /**
     * Update the specified resource in storage.
     * Memperbarui data penataan buku di database.
     */
    public function update(Request $request, string $id)
    {
        // Validasi input dasar (sama seperti store)
        $validator = Validator::make($request->all(), [
            'id_buku' => 'required|exists:bukus,id_buku',
            'id_rak' => 'required|exists:raks,id_rak',
            'kolom' => 'required|integer|min:1',
            'baris' => 'required|integer|min:1',
            'jumlah' => 'required|integer|min:1',
            'sumber' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $penataan = penataan_bukus::findOrFail($id);
        $oldJumlah = $penataan->jumlah;
        $oldRakId = $penataan->id_rak;

        // Di update(), setelah $penataan = penataan_bukus::findOrFail($id);
        $oldBukuId = $penataan->id_buku;
        $isBukuChanged = ($oldBukuId != $request->id_buku);

        if ($isBukuChanged) {
            // Pindah item lama ke buku baru (asumsi jumlah tetap, pindah sebanyak oldJumlah)
            buku_items::where('id_buku', $oldBukuId)
                ->where('id_rak', $oldRakId)
                ->take($oldJumlah)
                ->update(['id_buku' => $request->id_buku]);
        }

        $buku = bukus::withSum(['penataan_bukus' => fn($q) => $q->where('id_penataan', '!=', $id)], 'jumlah')->findOrFail($request->id_buku);
        $existingSumBuku = $buku->penataan_bukus_sum_jumlah ?? 0;
        $totalRequestedBuku = $existingSumBuku + $request->jumlah;

        // Validasi jumlah buku total
        if ($totalRequestedBuku > $buku->jumlah) {
            return redirect()->back()
                ->withErrors(['jumlah' => "Jumlah melebihi total eksemplar buku! Sisa: " . ($buku->jumlah - $existingSumBuku) . "."])
                ->withInput();
        }

        // Validasi kapasitas rak (hitung exclude old jika rak berubah)
        $rak = raks::withSum(['penataan_bukus' => fn($q) => $q->where('id_penataan', '!=', $id)], 'jumlah')->findOrFail($request->id_rak);
        $existingSumRak = $rak->penataan_bukus_sum_jumlah ?? 0;
        $totalRequestedRak = $existingSumRak + $request->jumlah;
        if ($totalRequestedRak > $rak->kapasitas) {
            return redirect()->back()
                ->withErrors(['jumlah' => "Jumlah melebihi kapasitas rak! Sisa: " . ($rak->kapasitas - $existingSumRak) . "."])
                ->withInput();
        }

        // Cek jika lokasi berubah
        $isLocationChanged = ($penataan->id_rak != $request->id_rak || $penataan->kolom != $request->kolom || $penataan->baris != $request->baris);

        if ($isLocationChanged) {
            // Cek target lokasi untuk merge
            $targetPenataan = penataan_bukus::where('id_buku', $request->id_buku)
                ->where('id_rak', $request->id_rak)
                ->where('kolom', $request->kolom)
                ->where('baris', $request->baris)
                ->where('id_penataan', '!=', $id)
                ->first();

            if ($targetPenataan) {
                // Merge: Update target, hapus old penataan, pindah items
                $newJumlahTarget = $targetPenataan->jumlah + $request->jumlah;
                $targetPenataan->jumlah = $newJumlahTarget;
                $targetPenataan->id_user = Auth::id();
                $targetPenataan->modified_date = now();
                $targetPenataan->save();

                // Pindah items existing ke rak baru
                buku_items::where('id_buku', $penataan->id_buku)
                    ->where('id_rak', $oldRakId)
                    ->take($oldJumlah) // Asumsi items sesuai jumlah old
                    ->update(['id_rak' => $request->id_rak]);

                $penataan->delete();

                return redirect()->route('penataan_bukus.index')->with('success', 'Penataan merged & dipindah. Eksemplar updated.');
            }
        }

        // Jika jumlah berubah (tidak merge), adjust eksemplar
        $delta = $request->jumlah - $oldJumlah;
        if ($delta > 0) {
            // Tambah items baru
            for ($i = 0; $i < $delta; $i++) {
                buku_items::create([
                    'id_buku' => $request->id_buku,
                    'id_rak' => $request->id_rak,
                    'kondisi' => 'baik',
                    'status' => 'tersedia',
                    'sumber' => $request->sumber,
                ]);
            }
        } elseif ($delta < 0) {
            // Hapus items excess (asumsi hapus yang terakhir/tersedia)
            buku_items::where('id_buku', $request->id_buku)
                ->where('id_rak', $request->id_rak)
                ->where('status', 'tersedia') // Hanya hapus yang tersedia
                ->orderBy('id_item', 'desc')
                ->take(abs($delta))
                ->delete();
        }

        // Update penataan
        $penataan->id_buku = $request->id_buku;
        $penataan->id_rak = $request->id_rak;
        $penataan->kolom = $request->kolom;
        $penataan->baris = $request->baris;
        $penataan->jumlah = $request->jumlah;
        $penataan->sumber = $request->sumber;
        $penataan->id_user = Auth::id();
        $penataan->modified_date = now();
        $penataan->save();

        // 🟢 Sinkronkan sumber di buku_items
        buku_items::where('id_buku', $request->id_buku)
            ->where('id_rak', $request->id_rak)
            ->update(['sumber' => $request->sumber]);

        return redirect()->route('penataan_bukus.index')->with('success', 'Penataan diperbarui. Eksemplar adjusted.');
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus data penataan buku dari database.
     */
    public function destroy(string $id)
    {
        $penataan = penataan_bukus::findOrFail($id);

        // Hapus eksemplar terkait (asumsi hapus sebanyak jumlah)
        buku_items::where('id_buku', $penataan->id_buku)
            ->where('id_rak', $penataan->id_rak)
            ->take($penataan->jumlah)
            ->delete();

        $penataan->delete();

        return redirect()->route('penataan_bukus.index')->with('success', 'Penataan dihapus. Eksemplar terkait dihapus.');
    }

    public function getRakByBuku($id_buku)
    {
        $buku = \App\Models\bukus::find($id_buku);
        if (!$buku) {
            return response()->json([]);
        }

        // Misal: ambil rak dengan kategori yang sama dengan buku
        $raks = \App\Models\raks::where('id_kategori', $buku->id_kategori)->get();

        return response()->json($raks);
    }



    // ... (show dan edit tetap sama)

}
