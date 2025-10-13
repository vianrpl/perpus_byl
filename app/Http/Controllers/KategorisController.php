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
        $kategoris=kategoris::when($search, function ($query, $search) {
            $query->where('id_kategori', 'like', "%{$search}%")
                ->orWhere('nama_kategori', 'like', "%{$search}%");
        })

            ->orderBy('id_kategori', 'asc') // untuk pagination
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
        $data=$request->validate([
            'nama_kategori'=>'required|string|max:50',
        ]);

        kategoris::create($data);
        return redirect()->route('kategoris.index')
            ->with('success','kategoris berhasil di tambahkan');
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
            ->with('success','kategoris berhasil diperbarui');
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
}
