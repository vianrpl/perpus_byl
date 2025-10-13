<?php

namespace App\Http\Controllers;

use App\Models\raks;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\bukus;

class RaksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $raks = raks::when($search, function ($query, $search) {
            $query->where('id_rak', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('id_lokasi', 'like', "%{$search}%")
                ->orWhereHas('kategoris', function ($q) use ($search) {
                    $q->where('nama_kategori', 'like', "%{$search}%");
                });
        })
            ->with('lokasi_raks', 'kategoris')  // Load relasi untuk view
            ->withSum('penataan_bukus', 'jumlah')      // Hitung relasi untuk tampilan kapasitas
            ->orderBy('id_rak', 'asc') // untuk pagination
            ->paginate(10)
            ->withQueryString();

            return view('raks.index',compact('raks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('raks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string|max:100',
            'nama'    => 'required|string|max:255',
            'kolom'   => 'nullable|string|max:50',
            'baris'   => 'nullable|string|max:50',
            'kapasitas'=> 'nullable|integer',
            'id_lokasi' => 'nullable|integer',
            'id_kategori' => 'nullable|integer',
        ]);

        raks::create($data);

        return redirect()->route('raks.index')->with('success', 'Rak berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */

    public function show($id_rak, Request $request)
    {
        // Ambil data rak (dengan relasi lokasi)
        $rak = raks::with(['lokasi_raks'])->findOrFail($id_rak);

        // Ambil parameter dari URL
        $kategori = $request->query('kategori'); // ?kategori=...
        $search   = $request->query('search');   // ?search=...

        // Query buku yang punya penataan di rak ini (dasar)
        $bukuQuery = bukus::query()
            // eager load penataan yang hanya untuk rak ini,
            // supaya nanti bisa hitung total jumlah tanpa N+1 queries
            ->with(['penataan_bukus' => function($q) use ($id_rak) {
                $q->where('id_rak', $id_rak);
            }])
            // pastikan buku memang ada penataan di rak ini
            ->whereHas('penataan_bukus', function($q) use ($id_rak) {
                $q->where('id_rak', $id_rak);
            });

        // Jika ada parameter kategori, batasi juga ke kategori itu
        if ($kategori) {
            $bukuQuery->where('id_kategori', $kategori);
        }

        // Jika ada pencarian judul
        if ($search) {
            $bukuQuery->where('judul', 'like', "%{$search}%");
        }

        // Pagination (ubah angka 10 sesuai kebutuhan)
        $paginator = $bukuQuery->orderBy('judul')->paginate(10)->withQueryString();

        // Mapping: ubah collection jadi item yg berisi 'buku' + 'total_jumlah'
        $mapped = $paginator->getCollection()->map(function($buku) {
            return (object)[
                'buku' => $buku,
                // total jumlah penataan di rak (karena kita eager load, ini tidak n+1)
                'total_jumlah' => $buku->penataan_bukus->sum('jumlah')
            ];
        });

        // Buat kembali LengthAwarePaginator supaya links() tetap bekerja
        $bukusInRak = new LengthAwarePaginator(
            $mapped,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(), // biar querystring (kategori/search) tetap ada di link
            ]
        );

        // Kirim ke view dengan nama variabel 'bukusInRak' (konsisten, lowercase)
        return view('raks.show', compact('rak', 'bukusInRak'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id_rak)
    {
        $lokasi_raks = \App\Models\lokasi_raks::all();
        $kategoris = \App\Models\kategoris::all();
        $rak= raks::findOrFail($id_rak);
        return view ('raks.edit',compact('rak'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_rak)
    {
        $rak = raks::findOrFail($id_rak);

        $data = $request->validate([
            'barcode' => 'nullable|string|max:100',
            'nama'    => 'required|string|max:255',
            'kolom'   => 'nullable|string|max:50',
            'baris'   => 'nullable|string|max:50',
            'kapasitas'=> 'nullable|integer',
            'id_lokasi' => 'nullable|integer',
            'id_kategori' => 'nullable|integer',
        ]);

        $rak->update($data);

        return redirect()->route('raks.index')->with('success', 'Rak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id_rak)
    {
        $rak = raks::findOrFail($id_rak);
        $rak->delete();

        return redirect()->route('raks.index')->with('success', 'Rak berhasil dihapus.');
    }
}
