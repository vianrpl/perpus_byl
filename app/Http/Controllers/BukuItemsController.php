<?php

namespace App\Http\Controllers;

use App\Models\bukus;
use App\Models\buku_items;
use App\Models\raks;
use Illuminate\Http\Request;

class BukuItemsController extends Controller
{
    public function index($buku)
    {
        $buku = bukus::with('items')->findOrFail($buku);
        // Ambil items per buku dengan paginate
        $items = $buku->items()
            ->orderBy('id_item', 'asc')
            ->paginate(10)
            ->withQueryString();
        $raks = raks::all();

        return view('buku_items.index', compact('buku','items','raks'));
    }

    public function create($buku)
    {
        $buku = bukus::findOrFail($buku);
        $raks = raks::all();
        return view('bukus.items.create', compact('buku','raks'));
    }

    public function store(Request $request, $id_buku)
    {
        $data = $request->validate([
            'kondisi' => 'required|in:baik,rusak,hilang',
            'status' => 'required|in:tersedia,dipinjam,hilang',
            'sumber' => 'nullable|string|max:255',
            'id_rak' => 'required|exists:raks,id_rak',
        ]);
        buku_items::create([
            'id_buku'   => $id_buku,
            'id_rak'    => $request->id_rak,
            'kondisi'   => $request->kondisi,
            'status'    => $request->status,
            'sumber'    => $request->sumber]);

        return redirect()->route('bukus.items.index', $id_buku)
            ->with('success', 'Item buku berhasil ditambahkan.');
    }



    public function show($buku, $item)
    {
        $buku = bukus::findOrFail($buku);
        $item = buku_items::where('id_buku', $buku->id_buku)
            ->where('id_item', $item)
            ->firstOrFail();

        return view('bukus.items.show', compact('buku','item'));
    }

    public function edit($buku, $item)
    {
        $buku = bukus::findOrFail($buku);
        $item = buku_items::where('id_buku', $buku->id_buku)->findOrFail($item);

        return view('bukus.items.edit', compact('buku','item'));
    }

    public function update(Request $request, $buku, $id_item)
    {
        $data = $request->validate([
            'kondisi' => 'required|in:baik,rusak,hilang',
            'status' => 'required|in:tersedia,dipinjam,hilang',
            'sumber' => 'nullable|string|max:255',
            'id_rak' => 'required|exists:raks,id_rak',
        ]);
        $item = \App\Models\buku_items::findOrFail($id_item);
        $item->update($data);

        return redirect()->route('bukus.items.index', $item->id_buku)
            ->with('success', 'Item buku berhasil diperbarui.');
    }

    public function destroy($buku, $item)
    {
        $item = buku_items::where('id_buku', $buku)->findOrFail($item);
        $item->delete();

        return redirect()->route('bukus.items.index', $buku)
            ->with('success', 'Item berhasil dihapus');
    }

    public function allItems(Request $request)
    {
        $query = \App\Models\buku_items::with(['bukus', 'raks']);

        // Kalau ada id_buku di query string, filter berdasarkan itu
        if ($request->has('id_buku')) {
            $query->where('id_buku', $request->id_buku);
        }

        $items = $query->get();

        return view('buku_items.all', compact('items'));
    }

}
