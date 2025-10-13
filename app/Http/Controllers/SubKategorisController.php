<?php

namespace App\Http\Controllers;

use App\Models\sub_kategoris;
use Illuminate\Http\Request;

class SubKategorisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sub_kategoris=sub_kategoris::when($search, function ($query, $search){
            $query->where('id_sub', 'like', "%{$search}%")
                ->orWhere('nama_sub_kategori', 'like', "%{$search}%");
        })


            ->orderBy('id_sub', 'asc') // untuk pagination
            ->paginate(10)
            ->withQueryString();
        return view('sub_kategoris.index',compact('sub_kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sub_kategoris.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data=$request->validate([
            'nama_sub_kategori'=>'required|string|max:50',
        ]);

        sub_kategoris::create($data);
        return redirect()->route('sub_kategoris.index')
            ->with('success','sub kategoris berhasil di tambahkan');
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
        return view('sub_kategoris.edit',compact('sub_kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, sub_kategoris $sub_kategori)
    {
        $data = $request->validate([
            'nama_sub_kategori'=>'required|string|max:50'
        ]);
        $sub_kategori->update($data);
        return redirect()->route('sub_kategoris.index')
            ->with('success','sub_kategoris berhasil diperbarui');
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
}
