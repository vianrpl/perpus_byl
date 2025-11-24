<?php

namespace App\Http\Controllers;

use App\Models\kategoris;
use Illuminate\Http\Request;

class KategorisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kategoris = kategoris::when($search, function ($query, $search) {
            $query->where('id_kategori', 'like', "%{$search}%")
                ->orWhere('nama_kategori', 'like', "%{$search}%");
        })
            ->orderBy('id_kategori', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('kategoris.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategoris.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:50',
        ]);

        kategoris::create($data);
        return redirect()->route('kategoris.index')
            ->with('success','Kategori berhasil ditambahkan');
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
    public function edit(kategoris $kategori)
    {
        return view('kategoris.edit',compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, kategoris $kategori)
    {
        $data = $request->validate([
            'nama_kategori'=>'required|string|max:50'
        ]);
        $kategori->update($data);
        return redirect()->route('kategoris.index')
            ->with('success','Kategori berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(kategoris $kategori)
    {
        $kategori->delete();
        return redirect()->route('kategoris.index')
            ->with('success','kategori berhasil dihapus');
    }

    public function getSubKategoris($id_kategori, Request $request)
    {
        try {
            // Cari kategori berdasarkan ID
            $kategori = kategoris::findOrFail($id_kategori);

            // Ambil sub kategori dari tabel pivot
            $sub_kategoris = $kategori->sub_kategoris()
                ->select('sub_kategoris.*')
                ->get();

            // Hitung jumlah buku per sub kategori
            $sub_kategoris_with_count = $sub_kategoris->map(function($sub) use ($id_kategori) {
                // Hitung buku yang pakai kombinasi kategori + sub ini
                $jumlah_buku = \DB::table('bukus')
                    ->where('id_kategori', $id_kategori)
                    ->where('id_sub', $sub->id_sub)
                    ->count();

                return [
                    'id_sub' => $sub->id_sub,
                    'nama_sub_kategori' => $sub->nama_sub_kategori,
                    'jumlah_buku' => $jumlah_buku
                ];
            });

            // Manual pagination (5 per halaman)
            $perPage = 5;
            $currentPage = $request->get('page', 1);
            $total = $sub_kategoris_with_count->count();

            // Slice data sesuai halaman
            $paginated = $sub_kategoris_with_count->slice(($currentPage - 1) * $perPage, $perPage)->values();

            // Return JSON response
            return response()->json([
                'success' => true,
                'kategori' => [
                    'id_kategori' => $kategori->id_kategori,
                    'nama_kategori' => $kategori->nama_kategori
                ],
                'sub_kategoris' => $paginated,
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

}
