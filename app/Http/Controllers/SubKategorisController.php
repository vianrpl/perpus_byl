<?php

namespace App\Http\Controllers;

use App\Models\sub_kategoris;
use App\Models\kategoris;
use Illuminate\Http\Request;

class SubKategorisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sub_kategoris = sub_kategoris::when($search, function ($query, $search){
            $query->where('id_sub', 'like', "%{$search}%")
                ->orWhere('nama_sub_kategori', 'like', "%{$search}%");
        })
            ->orderBy('id_sub', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Ambil semua kategori untuk dropdown
        $kategoris = \App\Models\kategoris::orderBy('nama_kategori', 'asc')->get();

        return view('sub_kategoris.index', compact('sub_kategoris', 'kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = kategoris::orderBy('nama_kategori', 'asc')->get();
        return view('sub_kategoris.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_sub_kategori' => 'required|string|max:50',
            'kategori_ids' => 'nullable|array', // Array kategori yang dipilih
            'kategori_ids.*' => 'exists:kategoris,id_kategori', // Validasi tiap ID
        ]);

        // Buat sub kategori baru
        $sub_kategori = sub_kategoris::create([
            'nama_sub_kategori' => $data['nama_sub_kategori'],
        ]);

        // Attach kategori yang dipilih ke tabel pivot
        if (!empty($data['kategori_ids'])) {
            $sub_kategori->kategoris()->attach($data['kategori_ids']);
        }

        return redirect()->route('sub_kategoris.index')
            ->with('success', 'Sub kategori berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(sub_kategoris $sub_kategori)
    {
        $kategoris = kategoris::orderBy('nama_kategori', 'asc')->get();
        return view('sub_kategoris.edit', compact('sub_kategori', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */


    public function update(Request $request, sub_kategoris $sub_kategori)
    {
        $data = $request->validate([
            'nama_sub_kategori' => 'required|string|max:50'
        ]);
        $sub_kategori->update($data);
        return redirect()->route('sub_kategoris.index')
            ->with('success','Sub kategori berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(sub_kategoris $sub_kategori)
    {
        $sub_kategori->delete();
        return redirect()->route('sub_kategoris.index')
            ->with('success','sub_kategori berhasil dihapus');
    }

    /**
     * Get kategoris by sub kategori (BARU)
     */
    public function getKategoris($id_sub, Request $request)
    {
        try {
            // Cari sub kategori berdasarkan ID
            $sub_kategori = sub_kategoris::findOrFail($id_sub);

            // Ambil kategori dari tabel pivot
            $kategoris = $sub_kategori->kategoris()
                ->select('kategoris.*')
                ->get();

            // Hitung jumlah buku per kategori
            $kategoris_with_count = $kategoris->map(function($kat) use ($id_sub) {
                // Hitung buku yang pakai kombinasi kategori + sub ini
                $jumlah_buku = \DB::table('bukus')
                    ->where('id_kategori', $kat->id_kategori)
                    ->where('id_sub', $id_sub)
                    ->count();

                return [
                    'id_kategori' => $kat->id_kategori,
                    'nama_kategori' => $kat->nama_kategori,
                    'jumlah_buku' => $jumlah_buku
                ];
            });

            // Manual pagination (5 per halaman)
            $perPage = 5;
            $currentPage = $request->get('page', 1);
            $total = $kategoris_with_count->count();

            // Slice data sesuai halaman
            $paginated = $kategoris_with_count->slice(($currentPage - 1) * $perPage, $perPage)->values();

            // Return JSON response
            return response()->json([
                'success' => true,
                'sub_kategori' => [
                    'id_sub' => $sub_kategori->id_sub,
                    'nama_sub_kategori' => $sub_kategori->nama_sub_kategori
                ],
                'kategoris' => $paginated,
                'pagination' => [
                    'current_page' => (int) $currentPage,
                    'last_page' => (int) ceil($total / $perPage),
                    'per_page' => $perPage,
                    'total' => $total
                ]
            ]);

        } catch (\Exception $e) {
            // Kalau error, return JSON error
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all kategoris untuk dropdown (TAMBAHKAN METHOD INI)
     */
    public function getAllKategoris()
    {
        $kategoris = \App\Models\kategoris::orderBy('nama_kategori', 'asc')->get();
        return response()->json($kategoris);
    }
    /**
     * Get all kategoris untuk dropdown (BARU)
     */
}
