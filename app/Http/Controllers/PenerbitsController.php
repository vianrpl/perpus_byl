<?php

namespace App\Http\Controllers;

use App\Models\penerbits;
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
            ->orderBy('id_penerbit', 'asc') // Ganti 'id_item' dengan kolom yang sesuai, misalnya 'id_penerbit'
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
            ->with('success','data penerbits berhasil di tambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(penerbits $penerbit)
    {
        return view('penerbits.show',compact('penerbit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(penerbits $penerbit)
    {
        return view('penerbits.edit',compact('penerbit'));
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
            ->with('success','data penerbits berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(penerbits $penerbit)
    {
        $penerbit->delete();
        return redirect()->route('penerbits.index')
            ->with('success','data penerbit berhasil di hapus');
    }
}
