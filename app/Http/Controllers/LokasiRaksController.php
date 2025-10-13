<?php

namespace App\Http\Controllers;

use App\Models\lokasi_raks;
use Illuminate\Http\Request;

class LokasiRaksController extends Controller
{
    public function index(Request $request)
    {
     $search=$request->input('search');

        $lokasi_raks = lokasi_raks::when($search, function ($query, $search) {
            $query->where('lantai', 'like', "%{$search}%")
                ->orWhere('ruang', 'like', "%{$search}%")
                ->orWhere('sisi', 'like', "%{$search}%");
        })

        ->orderBy('id_lokasi', 'asc') // untuk pagination
        ->paginate(10)
        ->withQueryString();
        return view('lokasi_raks.index', compact('lokasi_raks'));
    }

    public function create()
    {
        return view('lokasi_raks.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lantai' => 'required|string|max:50',
            'ruang'  => 'required|string|max:50',
            'sisi'   => 'nullable|string|max:50',
        ]);

        lokasi_raks::create($data);

        return redirect()->route('lokasi_raks.index')
            ->with('success', 'Lokasi rak berhasil ditambahkan.');
    }

    public function show(lokasi_raks $lokasi_rak)
    {
        return view('lokasi_raks.show', compact('lokasi_rak'));
    }

    public function edit(lokasi_raks $lokasi_rak)
    {
        return view('lokasi_raks.edit', compact('lokasi_rak'));
    }

    public function update(Request $request, lokasi_raks $lokasi_rak)
    {
        $data = $request->validate([
            'lantai' => 'required|string|max:50',
            'ruang'  => 'required|string|max:50',
            'sisi'   => 'nullable|string|max:50',
        ]);

        $lokasi_rak->update($data);

        return redirect()->route('lokasi_raks.index')
            ->with('success', 'Lokasi rak berhasil diperbarui.');
    }

    public function destroy(lokasi_raks $lokasi_rak)
    {
        $lokasi_rak->delete();

        return redirect()->route('lokasi_raks.index')
            ->with('success', 'Lokasi rak berhasil dihapus.');
    }
}
