<?php

namespace App\Http\Controllers;

use App\Models\bukus;
use App\Models\buku_items;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    // 🔹 Menampilkan semua peminjaman (untuk admin/petugas)
    public function index(Request $request)
    {
        // ambil nilai pencarian dari input
        $search = $request->input('search');

        // query dasar
        $peminjaman = Peminjaman::with(['user'])
            ->orderBy('id_peminjaman', 'asc')
            ->when($search, function ($query, $search) {
                $query->whereHas('item', function ($q) use ($search) {
                    $q->where('barcode', 'like', "%{$search}%");
                })->orWhereHas('item.bukus', function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%");
                });
            })
            ->paginate(10)
            ->withQueryString();

        return view('peminjaman.index', compact('peminjaman', 'search'));
    }


    // 🔹 User mengirim permintaan pinjam
    // 🔹 User mengirim permintaan pinjam
    public function storeRequest(Request $req)
    {
        $req->validate([
            'id_item' => 'required|array|min:1|max:2',
            'id_item.*' => 'required|integer|exists:buku_items,id_item|distinct',
            'id_user' => 'required|exists:users,id_user',        // Petugas
            'id_member' => 'required|exists:users,id_user',      // Member yang pinjam
            'alamat' => 'required|string|max:255',
            'nama_peminjam' => 'required|string|max:255',
            'pengembalian' => 'required|date|after:now',
        ]);

        $idMember = $req->id_member;
        $member = \App\Models\User::findOrFail($idMember);

        // HARUS MEMBER
        if ($member->status !== 'member') {
            return back()->with('error', 'Hanya member yang bisa meminjam buku.');
        }

        // ANTI DUPLIKAT ITEM DI REQUEST
        if (count($req->id_item) !== count(array_unique($req->id_item))) {
            return back()->with('error', 'Eksemplar yang sama tidak boleh dipilih dua kali.');
        }

        $now = Carbon::now();
        $today = $now->format('Ymd');

        DB::transaction(function () use ($req, $now, $today, $idMember, $member) {

            // LOCK agar tidak race condition
            Peminjaman::where('id_member', $idMember)->lockForUpdate()->get();

            // ================================
            //  CEK KUOTA (MAKS 2 BUKU)
            // ================================
            $activeLoans = Peminjaman::where('id_member', $idMember)
                ->whereIn('status', ['dipinjam', 'diperpanjang'])
                ->get();

            $activeItemCount = 0;
            foreach ($activeLoans as $loan) {
                $items = json_decode($loan->id_items, true);
                if (is_array($items)) {
                    $activeItemCount += count($items);
                }
            }

            $newItemCount = count($req->id_item);

            if ($activeItemCount + $newItemCount > 2) {
                throw new \Exception("Member {$member->name} sudah meminjam $activeItemCount buku. Maksimal 2 buku.");
            }

            // ================================
            //  VALIDASI ITEM & STATUS
            // ================================
            $items = [];
            $firstItem = null;

            foreach ($req->id_item as $id_item) {
                $item = buku_items::lockForUpdate()->findOrFail($id_item);

                if ($item->status !== 'tersedia') {
                    throw new \Exception("Buku dengan barcode {$item->barcode} sedang dipinjam!");
                }

                $items[] = $item;
                if (!$firstItem) $firstItem = $item;
            }

            // ================================
            //  BATAS PENGEMBALIAN MAX 7 HARI
            // ================================
            $requestedReturn = Carbon::parse($req->pengembalian);
            $maxReturn = $now->copy()->addDays(7);
            $finalReturn = $requestedReturn->gt($maxReturn) ? $maxReturn : $requestedReturn;

            // ================================
            //  SIMPAN PEMINJAMAN (TANPA no_transaksi dulu)
            // ================================
            $peminjaman = Peminjaman::create([
                'id_buku' => $firstItem->id_buku,
                'id_item' => $firstItem->id_item,
                'id_items' => json_encode($req->id_item),
                'id_user' => Auth::id(),
                'id_member' => $idMember,
                'alamat' => $req->alamat,
                'nama_peminjam' => $req->nama_peminjam,
                'pengembalian' => $finalReturn,
                'kondisi' => $firstItem->kondisi,
                'status' => 'dipinjam',
                'request_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => $now,
                'pinjam' => $now,
                'no_transaksi' => null, // Akan diisi nanti
            ]);

            // ================================
            //  GENERATE NO TRANSAKSI SETELAH CREATE (PAKAI ID YANG SUDAH ADA)
            // ================================
            $noTransaksi = $today . '01' . str_pad($peminjaman->id_peminjaman, 4, '0', STR_PAD_LEFT);
            $peminjaman->no_transaksi = $noTransaksi;
            $peminjaman->save(); // Simpan ulang

            // ================================
            //  UPDATE STATUS ITEM
            // ================================
            foreach ($items as $item) {
                $item->status = 'dipinjam';
                $item->save();
            }
        });

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil untuk member!');
    }



    // 🔹 Perpanjangan (maksimal 7 hari)
    public function extend($id, Request $req)
    {
        $req->validate([
            'new_pengembalian' => 'required|date|after:now',
        ]);

        $p = Peminjaman::findOrFail($id);
        if ($p->request_status !== 'approved') {
            return back()->withErrors('Hanya peminjaman yang telah disetujui bisa diperpanjang.');
        }

        $pinjam = Carbon::parse($p->pinjam);
        $newDue = Carbon::parse($req->new_pengembalian);

        if ($newDue->diffInDays($pinjam, false) > 7) {
            return back()->withErrors(['new_pengembalian' => 'Perpanjangan melebihi maksimum 7 hari sejak tanggal pinjam.']);
        }

        $p->pengembalian = $newDue;
        $p->status = 'diperpanjang';
        $p->save();

        return back()->with('success', 'Tanggal pengembalian berhasil diperpanjang.');
    }

    // 🔹 Update status peminjaman (setujui dari tombol "Setujui")
    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($id) {
            $now = Carbon::now();

            $peminjaman = Peminjaman::lockForUpdate()->findOrFail($id);

            if ($peminjaman->request_status !== 'pending') {
                abort(400, 'Permintaan sudah diproses.');
            }

            $item = buku_items::lockForUpdate()->findOrFail($peminjaman->id_item);
            if ($item->status !== 'tersedia') {
                abort(400, 'Item buku sudah tidak tersedia.');
            }

            // ubah status peminjaman
            $peminjaman->request_status = 'approved';
            $peminjaman->approved_by = Auth::id();
            $peminjaman->approved_at = $now;
            $peminjaman->pinjam = $now;
            $peminjaman->status = 'dipinjam';

            // ambil tanggal pengembalian yang diminta user
            $requestedReturn = Carbon::parse($peminjaman->pengembalian);

            // kalau lebih dari 7 hari dari sekarang, batasi
            if ($requestedReturn->gt($now->copy()->addDays(7))) {
                $peminjaman->pengembalian = $now->copy()->addDays(7);
            } else {
                $peminjaman->pengembalian = $requestedReturn;
            }

            $peminjaman->save();

            // update status item buku
            $item->status = 'dipinjam';
            $item->save();
        });

        return back()->with('success', 'Peminjaman berhasil disetujui dan tanggal pengembalian diatur sesuai batas maksimal 7 hari.');
    }


    public function perpanjang(Request $request, $id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'dipinjam') {
            return back()->with('error', 'Peminjaman ini tidak bisa diperpanjang.');
        }

        $request->validate([
            'hari' => 'required|integer|min:1|max:7',
        ]);

        $peminjaman->pengembalian = \Carbon\Carbon::parse($peminjaman->pengembalian)
            ->addDays((int)$request->hari);
        $peminjaman->status = 'diperpanjang';
        $peminjaman->save();

        return back()->with('success', "Peminjaman diperpanjang {$request->hari} hari.");
    }


    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'kondisi' => 'required|in:baik,rusak,hilang',
        ]);

        DB::transaction(function() use ($id, $request) {
            $p = Peminjaman::lockForUpdate()->findOrFail($id);
            if (!in_array($p->status, ['dipinjam', 'diperpanjang'])) {
                abort(400, 'Hanya buku yang sedang dipinjam bisa dikembalikan.');
            }

            // ubah status peminjaman — TETAP
            $p->status = 'kembali';
            $p->request_status = 'returned';
            $p->kondisi_buku_saat_kembali = $request->kondisi; // Asumsi kondisi sama untuk semua, kalau beda nanti adjust
            $p->save();

            // Handle multiple items dari JSON
            $idItems = json_decode($p->id_items, true) ?? [$p->id_item]; // Fallback ke legacy
            foreach ($idItems as $id_item) {
                $item = buku_items::lockForUpdate()->findOrFail($id_item);
                $item->kondisi = $request->kondisi;

                if($request->kondisi === 'hilang'){
                    $item->status = 'hilang';
                } else {
                    $item->status = 'tersedia';
                }
                $item->save();
            }
        });

        return back()->with('success', 'Buku berhasil dikembalikan dan kondisi diperbarui.');
    }

    public function destroy($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        // Cek status — hanya boleh hapus jika sudah dikembalikan
        if ($peminjaman->status !== 'kembali') {
            return back()->with('error', 'Hanya peminjaman yang sudah dikembalikan bisa dihapus.');
        }

        $peminjaman->delete();

        return back()->with('success', 'Data peminjaman berhasil dihapus.');
    }

    public function searchBuku(Request $request)
    {
        $query = $request->input('query');

        $items = buku_items::with('bukus') // Relasi ke bukus untuk ambil judul
        ->where('status', 'tersedia') // Hanya yang tersedia
        ->when($query, function ($q) use ($query) {
            $q->where('barcode', 'like', "%{$query}%") // Search barcode
            ->orWhereHas('bukus', function ($subq) use ($query) {
                $subq->where('judul', 'like', "%{$query}%"); // Search judul
            });
        })
            ->get(['id_item', 'barcode', 'bukus.judul as judul']); // Ambil data minimal

        return response()->json($items);
    }

    public function getBukusForSelection(Request $request)
    {
        $search = $request->input('search');

        $bukus = \App\Models\bukus::withCount([
            'items as tersedia_count' => function ($query) {
                $query->where('status', 'tersedia'); // HANYA HITUNG YANG TERSedia
            }
        ])
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', "%{$search}%");
            })
            ->having('tersedia_count', '>', 0) // HANYA BUKU YANG ADA YANG TERSedia
            ->orderBy('judul')
            ->paginate(10);

        return view('peminjaman.buku_table', compact('bukus'))->render();
    }

    public function getEksemplarByBuku($id_buku, Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('page', 1);

        $items = \App\Models\buku_items::where('id_buku', $id_buku)
            ->where('status', 'tersedia')
            ->when($query, function ($q, $query) {
                return $q->where('barcode', 'like', "%{$query}%");
            })
            ->select('id_item', 'barcode', 'kondisi')
            ->orderBy('barcode')
            ->paginate(10, ['*'], 'page', $page);

        return response()->json([
            'data' => $items->items(),
            'total' => $items->total(),
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'links' => $items->links('pagination::bootstrap-5')->toHtml()  // Render pagination HTML
        ]);
    }

    public function getMembersForSelection(Request $request)
    {
        $search = $request->input('search');

        $members = \App\Models\User::where('status', 'member')
            ->when($search, function ($q, $search) {
                return $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->withCount(['peminjaman as active_loans' => function ($query) {
                $query->whereIn('status', ['dipinjam', 'diperpanjang'])
                    ->select(DB::raw('SUM(JSON_LENGTH(id_items))'));  // Hitung total item dari JSON
            }])
            ->orderBy('name')
            ->paginate(10);

        // Hitung kuota: 2 - active_loans (kalau null, anggap 0)
        foreach ($members as $member) {
            $member->kuota = max(2 - ($member->active_loans ?? 0), 0);
        }

        return view('peminjaman.member_table', compact('members'))->render();
    }

    public function getActiveLoans($id_member)
    {
        $activeLoans = Peminjaman::where('id_member', $id_member)
            ->whereIn('status', ['dipinjam', 'diperpanjang'])
            ->get();

        $activeItemCount = 0;
        foreach ($activeLoans as $loan) {
            $items = json_decode($loan->id_items, true);
            if (is_array($items)) {
                $activeItemCount += count($items);
            }
        }

        return response()->json(['active' => $activeItemCount]);
    }
}
