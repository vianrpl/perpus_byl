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

        // Terapkan filter pencarian jika ada (nama buku, nama rak atau nama petugas)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('bukus', function ($qb) use ($search) {
                    $qb->where('judul', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('raks', function ($qr) use ($search) {
                        $qr->where('nama', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // ✅ Filter berdasarkan petugas (id_user)
        if ($request->filled('filter_petugas')) {
            $query->where('id_user', $request->filter_petugas);
        }

        // ✅ Filter berdasarkan status penataan (penuh / belum)
        // 'penuh'  => bukus.jumlah_tata >= bukus.jumlah
        // 'belum'  => bukus.jumlah_tata < bukus.jumlah
        if ($request->filled('filter_status')) {
            $status = $request->filter_status;
            if ($status === 'penuh') {
                // whereHas on relation bukus, cek kondisi jumlah_tata >= jumlah
                $query->whereHas('bukus', function($qb) {
                    $qb->whereColumn('jumlah_tata', '>=', 'jumlah');
                });
            } elseif ($status === 'belum') {
                $query->whereHas('bukus', function($qb) {
                    $qb->whereColumn('jumlah_tata', '<', 'jumlah');
                });
            }
        }

        // Ambil data dengan pagination (10 per halaman) dan simpan query params agar page mempertahankan filter
        $penataanBukus = $query->paginate(10)->appends($request->except('page'));

        // Ambil data buku dan rak untuk dropdown di modal (tidak diubah)
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
        $request->validate([
            'id_buku' => 'required|exists:bukus,id_buku',
            'id_rak' => 'required|exists:raks,id_rak',
            'kolom' => 'required|integer',
            'baris' => 'required|integer',
            'jumlah' => 'required|integer|min:1',
            'sumber' => 'required|string',
        ]);

        $id_buku = $request->id_buku;
        $tambahJumlah = (int) $request->jumlah;

        DB::beginTransaction();
        try {
            $buku = \App\Models\bukus::lockForUpdate()->find($id_buku);
            if (!$buku) {
                return $request->ajax()
                    ? response()->json(['success'=>false, 'message'=>'Buku tidak ditemukan'], 404)
                    : redirect()->back()->with('error','Buku tidak ditemukan.');
            }

            if (($buku->jumlah_tata + $tambahJumlah) > $buku->jumlah) {
                return $request->ajax()
                    ? response()->json(['success'=>false, 'message'=>'Jumlah melebihi kapasitas'], 422)
                    : redirect()->back()->with('error','Jumlah penataan melebihi kapasitas maksimal buku.');
            }

            // simpan penataan
            $pen = new \App\Models\penataan_bukus();
            $pen->id_buku = $id_buku;
            $pen->id_rak = $request->id_rak;
            $pen->kolom = $request->kolom;
            $pen->baris = $request->baris;
            $pen->jumlah = $tambahJumlah;
            $pen->id_user = auth()->id();
            $pen->sumber = $request->sumber;
            $pen->save();

            // buat eksemplar otomatis sesuai jumlah (barcode di-handle oleh trigger SQL)
            for ($i=0; $i<$tambahJumlah; $i++) {
                $it = new \App\Models\buku_items();
                $it->id_buku = $id_buku;
                $it->id_rak  = $request->id_rak;
                $it->sumber  = $request->sumber;
                $it->status  = 'tersedia';
                $it->kondisi = 'baik';
                $it->barcode = ''; // biarkan trigger DB generate
                $it->save();
            }

            // update jumlah_tata
            $buku->jumlah_tata = $buku->jumlah_tata + $tambahJumlah;
            $buku->save();

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success'=>true, 'added'=>$tambahJumlah], 200);
            }
            return redirect()->route('penataan_bukus.index')->with('success','Penataan berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
            }
            return redirect()->back()->with('error','Terjadi kesalahan: '.$e->getMessage());
        }
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

        $realJumlah = buku_items::where('id_buku', $request->id_buku)->count();
        $realJumlahTata = penataan_bukus::where('id_buku', $request->id_buku)->sum('jumlah');

        if ($realJumlahTata > $realJumlah) {
            $realJumlahTata = $realJumlah;
        }

        bukus::where('id_buku', $request->id_buku)->update([
            'jumlah_tata' => $realJumlahTata,
        ]);

// === Sinkronisasi jumlah & jumlah_tata setelah edit penataan ===
        $realJumlah = \App\Models\buku_items::where('id_buku', $request->id_buku)->count();
        $realJumlahTata = \App\Models\penataan_bukus::where('id_buku', $request->id_buku)->sum('jumlah');

        if ($realJumlahTata > $realJumlah) {
            $realJumlahTata = $realJumlah;
        }

        \App\Models\bukus::where('id_buku', $request->id_buku)->update([
            'jumlah_tata' => $realJumlahTata,
        ]);
// === Akhir tambahan ===

// === Sinkronisasi jumlah real penataan sesuai jumlah item sebenarnya ===
        $realCountItems = \App\Models\buku_items::where('id_buku', $request->id_buku)
            ->where('id_rak', $request->id_rak)
            ->count();

        $penataan->jumlah = $realCountItems;
        $penataan->save();
// === Akhir tambahan sinkronisasi ===

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

        // === Sinkronisasi setelah hapus penataan ===
        $realJumlah = \App\Models\buku_items::where('id_buku', $penataan->id_buku)->count();
        $realJumlahTata = \App\Models\penataan_bukus::where('id_buku', $penataan->id_buku)->sum('jumlah');

        if ($realJumlahTata > $realJumlah) {
            $realJumlahTata = $realJumlah;
        }

        // 🟢 Update jumlah_tata di tabel bukus agar sinkron
        \App\Models\bukus::where('id_buku', $penataan->id_buku)->update([
            'jumlah_tata' => $realJumlahTata,
        ]);

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
