<?php

namespace App\Http\Controllers;

use App\Models\bukus;
use App\Models\kategoris;
use App\Models\sub_kategoris;
use App\Models\penerbits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BukusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = bukus::with(['penerbits', 'kategoris', 'sub_kategoris']);

        if ($s = $request->get('search')) {
            $q->where(function($x) use ($s) {
                $x->where('judul', 'like', "%$s%")
                    ->orWhere('pengarang', 'like', "%$s%")
                    ->orWhere('tahun_terbit', 'like', "%$s%")
                    ->orWhere('isbn', 'like', "%$s%");
            })
                ->orWhereHas('penerbits', function($rel) use ($s) {
                    $rel->where('nama_penerbit', 'like', "%$s%");
                })
                ->orWhereHas('kategoris', function($rel) use ($s) {
                    $rel->where('nama_kategori', 'like', "%$s%");
                })
                ->orWhereHas('sub_kategoris', function($rel) use ($s) {
                    $rel->where('nama_sub_kategori', 'like', "%$s%");
                });
        }

        $buku = $q->orderBy('id_buku', 'asc')
            ->paginate(10)
            ->withQueryString();

        // data dropdown untuk modal tambah buku
        $penerbits = penerbits::all();
        $kategoris = kategoris::all();
        $sub_kategoris = sub_kategoris::all();

        return view('bukus.index', compact('buku','penerbits','kategoris','sub_kategoris'));
    }


    /**
     * Show the form for creating a new resource.
     */
   public function create()
{
    if (Auth::user()->role === 'konsumen') {
        // Kalau konsumen, tolak akses
        abort(403, 'Unauthorized');
        // atau bisa juga redirect:
        // return redirect()->route('bukus.index')->with('error', 'Konsumen tidak bisa menambah buku.');
    } else {
        // Kalau admin/petugas, ambil data dropdown
        $penerbits = penerbits::all();
        $kategoris = kategoris::all();
        $sub_kategoris = sub_kategoris::all();

        return view('bukus.create', compact('penerbits','kategoris','sub_kategoris'));
    }
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'            => 'required|string|max:255',
            'id_penerbit'      => 'nullable|integer|exists:penerbits,id_penerbit',
            'pengarang'        => 'nullable|string|max:255',
            'tahun_terbit'     => 'nullable|integer',
            'id_kategori'      => 'nullable|integer|exists:kategoris,id_kategori',
            'id_sub'  => 'nullable|integer|exists:sub_kategoris,id_sub',
            'isbn'             => 'nullable|string|max:100',
            'barcode'          => 'nullable|string|max:100',
            'jumlah' => 'required|integer|min:0',
        ]);

        bukus::create($data);

        return redirect()->route('bukus.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id_item)
    {
        $buku = bukus::findOrFail($id_item);
        $penerbits = penerbits::all();
        $kategoris = kategoris::all();
        $sub_kategoris = sub_kategoris::all();

        return view('bukus.edit', compact('buku','penerbits','kategoris','sub_kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id_item)
    {
        $buku = bukus::findOrFail($id_item);

        $data = $request->validate([
            'judul'            => 'required|string|max:255',
            'id_penerbit'      => 'nullable|integer|exists:penerbits,id_penerbit',
            'pengarang'        => 'nullable|string|max:255',
            'tahun_terbit'     => 'nullable|integer',
            'id_kategori'      => 'nullable|integer|exists:kategoris,id_kategori',
            'id_sub'  => 'nullable|integer|exists:sub_kategoris,id_sub',
            'isbn'             => 'nullable|string|max:100',
            'barcode'          => 'nullable|string|max:100',
            'jumlah' => 'required|integer|min:0',
        ]);

        $buku->update($data);

        // === Sinkronisasi jumlah_tata setelah ubah jumlah buku ===
        $realJumlah = \App\Models\bukus::where('id_buku', $id_item)->value('jumlah');
        $realJumlahTata = \App\Models\penataan_bukus::where('id_buku', $id_item)->sum('jumlah');

        if ($realJumlahTata > $realJumlah) {
            $realJumlahTata = $realJumlah;
        }

        \App\Models\bukus::where('id_buku', $id_item)->update([
            'jumlah_tata' => $realJumlahTata,
        ]);
// === Akhir tambahan ===


        return redirect()->route('bukus.index')->with('success', 'Buku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id_item)
    {
        bukus::findOrFail($id_item)->delete();

        return redirect()->route('bukus.index')->with('success', 'Buku berhasil dihapus.');
    }

    public function getForSelection(Request $request)
    {
        $search = $request->query('search');

        $bukus = bukus::when($search, function ($q) use ($search) {
            $q->where('judul', 'like', "%{$search}%");
        })->paginate(10);

        // Return partial view sebagai string HTML
        return view('penataan_bukus.partials.buku_table', compact('bukus'))->render();
    }

    // ... (method lain)

    /**
     * Method khusus buat welcome page dengan fitur search
     * Dipanggil dari halaman welcome (/)
     */
    public function welcomeSearch(Request $request)
    {
        // Query dasar: ambil semua buku dengan relasi
        $query = bukus::with(['penerbits', 'kategoris', 'sub_kategoris']);

        // ✅ CEK APAKAH ADA KEYWORD PENCARIAN
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            // Cari di 5 kolom: judul, pengarang, penerbit, kategori, tahun
            $query->where(function($q) use ($searchTerm) {
                $q->where('judul', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('pengarang', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tahun_terbit', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('penerbits', function($q) use ($searchTerm) {
                        $q->where('nama_penerbit', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('kategoris', function($q) use ($searchTerm) {
                        $q->where('nama_kategori', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Urutkan dari buku terbaru & pagination 10 item per halaman
        $bukus = $query->orderBy('id_buku', 'desc')->paginate(10);

        // Kirim data ke view welcome
        return view('welcome', compact('bukus'));
    }
}
