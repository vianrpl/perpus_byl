<?php

namespace App\Http\Controllers;

use App\Models\penerbits;
use App\Models\bukus; // TAMBAHKAN INI
use Illuminate\Http\Request;

class PenerbitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $penerbits = penerbits::when($search, function ($query, $search) {
            $query->where('id_penerbit', 'like', "%{$search}%")
                ->orWhere('nama_penerbit', 'like', "%{$search}%");
        })
            ->orderBy('id_penerbit', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('penerbits.index', compact('penerbits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('penerbits.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_penerbit' => 'required|string|max:50',
            'alamat'  => 'required|string|max:100',
            'no_telepon'   => 'nullable|string|max:50',
            'email'       => 'nullable|string|max:50',
        ]);

        penerbits::create($data);
        return redirect()->route('penerbits.index')
            ->with('success','Data penerbit berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(penerbits $penerbit)
    {
        return view('penerbits.show', compact('penerbit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(penerbits $penerbit)
    {
        return view('penerbits.edit', compact('penerbit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, penerbits $penerbit)
    {
        $data = $request->validate([
            'nama_penerbit' => 'required|string|max:50',
            'alamat'  => 'required|string|max:100',
            'no_telepon'   => 'nullable|string|max:50',
            'email'       => 'nullable|string|max:50',
        ]);

        $penerbit->update($data);
        return redirect()->route('penerbits.index')
            ->with('success','Data penerbit berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(penerbits $penerbit)
    {
        $penerbit->delete();
        return redirect()->route('penerbits.index')
            ->with('success','Data penerbit berhasil dihapus');
    }

    /**
     * Get bukus by penerbit dengan pagination
     */
    public function getBukus($id_penerbit, Request $request)
    {
        try {
            // Cari penerbit
            $penerbit = penerbits::findOrFail($id_penerbit);

            // Ambil buku dengan relasi
            $bukus = bukus::with(['penerbits', 'kategoris', 'sub_kategoris'])
                ->where('id_penerbit', $id_penerbit)
                ->orderBy('judul', 'asc')
                ->paginate(5);

            return response()->json([
                'success' => true,
                'penerbits' => [
                    'id_penerbit' => $penerbit->id_penerbit,
                    'nama_penerbit' => $penerbit->nama_penerbit
                ],
                'bukus' => $bukus->items(),
                'pagination' => [
                    'current_page' => $bukus->currentPage(),
                    'last_page' => $bukus->lastPage(),
                    'per_page' => $bukus->perPage(),
                    'total' => $bukus->total()
                ]
            ]);

        } catch (\Exception $e) {
            // Tangkap error dan return JSON
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
