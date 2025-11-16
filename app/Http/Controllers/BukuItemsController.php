<?php

namespace App\Http\Controllers;

use App\Models\bukus;
use App\Models\buku_items;
use App\Models\raks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\penataan_bukus;


class BukuItemsController extends Controller
{
    public function index(Request $request, $buku)
    {
        $search = $request->input('search');
        $filterKondisi = $request->input('filter_kondisi');
        $filterStatus = $request->input('filter_status');

        $buku = bukus::findOrFail($buku);

        $items = buku_items::where('id_buku', $buku->id_buku)
            ->when($search, function ($query, $search) {
                $query->where('barcode', 'like', "%{$search}%");
            })

            ->when($filterKondisi,function ($query,$filterKondisi){
                $query->where('kondisi',$filterKondisi);
            })
            ->when($filterStatus, function ($query, $filterStatus) {
                $query->where('status', $filterStatus);
            })



            ->orderBy('id_item', 'asc')
            ->paginate(10)
            ->withQueryString();

        $raks = raks::all();

        return view('buku_items.index', compact('buku', 'items', 'raks', 'search'));
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

        // Create item
        buku_items::create([
            'id_buku'   => $id_buku,
            'id_rak'    => $request->id_rak,
            'kondisi'   => $request->kondisi,
            'status'    => $request->status,
            'sumber'    => $request->sumber
        ]);

        // Update jumlah_tata (count eksemplar tertata)
        $totalItem = buku_items::where('id_buku', $id_buku)->count();
        bukus::where('id_buku', $id_buku)->update(['jumlah_tata' => $totalItem]);

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


        if ($item->kondisi === 'hilang') {
            return redirect()->route('bukus.items.index', $buku->id_buku)
                ->with('error', 'Item hilang tidak bisa diedit.');
        }

        return view('bukus.items.edit', compact('buku','item'));
    }

    public function update(Request $request, $buku, $id_item)
    {
        $item = \App\Models\buku_items::findOrFail($id_item);

        if ($item->kondisi === 'hilang') {
            return redirect()->route('bukus.items.index', $item->id_buku)
                ->with('error', 'Item hilang tidak bisa diperbarui.');
        }

        $data = $request->validate([
            'kondisi' => 'required|in:baik,rusak,hilang',
            'status' => 'required|in:tersedia,dipinjam,hilang',
            'sumber' => 'nullable|string|max:255',
            'id_rak' => 'required|exists:raks,id_rak',
        ]);

        // tambahan proteksi: jika request hendak mengubah status ke "tersedia" padahal kondisi "hilang" (paranoid)
        if ($item->kondisi === 'hilang' && ($request->status ?? '') === 'tersedia') {
            return redirect()->route('bukus.items.index', $item->id_buku)
                ->with('error', 'Tidak boleh mengubah status ke tersedia untuk item yang hilang.');
        }

        $item->update($data);

        return redirect()->route('bukus.items.index', $item->id_buku)
            ->with('success', 'Item buku berhasil diperbarui.');
    }

    // Hapus eksemplar 1 per 1
    public function destroy(Request $request, $id_item)
    {
        DB::beginTransaction();
        try {
            $item = \App\Models\buku_items::findOrFail($id_item);

            // === TAMBAHAN: jika sedang dipinjam atau diperpanjang -> blokir hapus ===
            if (in_array($item->status, ['dipinjam', 'diperpanjang'])) {
                DB::rollBack();
                // untuk AJAX (JS)
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'blocked' => [$item->id_item],
                        'message' => 'Eksemplar tidak bisa dihapus karena masih sedang dipinjam / diperpanjang.'
                    ], 422);
                }
                // untuk request normal (redirect)
                return redirect()->back()->with('error', 'Eksemplar tidak bisa dihapus karena masih sedang dipinjam / diperpanjang.');
            }
            // === akhir tambahan ===

            $buku = \App\Models\bukus::lockForUpdate()->find($item->id_buku);

            $item->delete();

            $buku->jumlah_tata = max(0, $buku->jumlah_tata - 1);
            $buku->save();

            DB::commit();

            if ($request->ajax()) return response()->json(['success'=>true,'deleted'=>[$id_item]],200);
            return redirect()->back()->with('success','Eksemplar dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
            return redirect()->back()->with('error','Gagal hapus: '.$e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return $request->ajax()
                ? response()->json(['success'=>false,'message'=>'Pilih minimal 1 eksemplar'],422)
                : redirect()->back()->with('error','Pilih minimal satu eksemplar.');
        }

        DB::beginTransaction();
        try {
            $items = \App\Models\buku_items::whereIn('id_item',$ids)->get();
            if ($items->isEmpty()) {
                DB::rollBack();
                return $request->ajax()
                    ? response()->json(['success'=>false,'message'=>'Tidak ada eksemplar ditemukan'],404)
                    : redirect()->back()->with('error','Tidak ada eksemplar ditemukan.');
            }

            // Pisahkan yang boleh dihapus dan yang diblokir karena status pinjam/perpanjang
            $canDelete = $items->filter(fn($it) => !in_array($it->status, ['dipinjam','diperpanjang']));
            $blocked  = $items->filter(fn($it) => in_array($it->status, ['dipinjam','diperpanjang']));

            // Jika tidak ada yang boleh dihapus, kembalikan blocked info
            if ($canDelete->isEmpty()) {
                DB::rollBack();
                $blockedIds = $blocked->pluck('id_item')->values()->all();
                return $request->ajax()
                    ? response()->json([
                        'success'=>false,
                        'blocked'=>$blockedIds,
                        'message'=>'Beberapa atau semua eksemplar dipinjam/diperpanjang sehingga tidak bisa dihapus.'
                    ], 422)
                    : redirect()->back()->with('error','Beberapa atau semua eksemplar dipinjam/diperpanjang sehingga tidak bisa dihapus.');
            }

            // Hapus yang boleh dihapus — grup per buku untuk update jumlah_tata
            $grouped = $canDelete->groupBy('id_buku');

            $deletedIds = [];
            $updatedBooks = []; // optional: kembalikan jumlah_tata terbaru per buku

            foreach ($grouped as $id_buku => $group) {
                $idsToDelete = $group->pluck('id_item')->toArray();

                \App\Models\buku_items::whereIn('id_item', $idsToDelete)->delete();
                $deletedIds = array_merge($deletedIds, $idsToDelete);

                $buku = \App\Models\bukus::lockForUpdate()->find($id_buku);
                $buku->jumlah_tata = max(0, $buku->jumlah_tata - count($idsToDelete));
                $buku->save();

                $updatedBooks[] = [
                    'id' => $buku->id_buku,
                    'jumlah_tata' => $buku->jumlah_tata,
                ];
            }

            DB::commit();

            // Kembalikan info: deleted + blocked (jika ada)
            $response = [
                'success' => true,
                'deleted' => $deletedIds,
                'updated_books' => $updatedBooks,
                'message' => 'Bulk delete selesai.'
            ];
            if ($blocked->isNotEmpty()) {
                $response['blocked'] = $blocked->pluck('id_item')->values()->all();
                $response['message'] .= ' Namun ada beberapa eksemplar yang diblokir karena status pinjam/perpanjang.';
            }

            return $request->ajax()
                ? response()->json($response, 200)
                : redirect()->back()->with('success','Bulk delete selesai.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $request->ajax()
                ? response()->json(['success'=>false,'message'=>$e->getMessage()],500)
                : redirect()->back()->with('error','Gagal bulk delete: '.$e->getMessage());
        }
    }








}
