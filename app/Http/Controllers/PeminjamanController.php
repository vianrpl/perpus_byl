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
    // =====================================================
    // INDEX - Menampilkan semua peminjaman
    // =====================================================
    public function index(Request $request)
    {
        $search = $request->input('search');

        $peminjaman = Peminjaman::with(['user', 'bukus'])
            ->orderBy('id_peminjaman', 'asc')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('no_transaksi', 'like', "%{$search}%")
                    ->orWhere('nama_peminjam', 'like', "%{$search}%")
                    ->orWhereHas('bukus', function ($q) use ($search) {
                        $q->where('judul', 'like', '%' . $search . '%');
                    });
            })
            ->paginate(10)
            ->withQueryString();

        // Set loan_items dengan tanggal yang benar per-record
        foreach ($peminjaman as $p) {
            $p->loan_items = $this->getItemsForLoan($p);
        }

        return view('peminjaman.index', compact('peminjaman', 'search'));
    }

    // =====================================================
    // GET ITEMS FOR LOAN - Ambil items dengan tanggal SNAPSHOT
    // Menggunakan kolom due_dates yang sudah ada di DB
    // =====================================================
    public function getItemsForLoan($peminjaman)
    {
        $idItems = json_decode($peminjaman->id_items, true) ?? [$peminjaman->id_item];
        $items = buku_items::with('bukus')->whereIn('id_item', $idItems)->get();

        // Ambil snapshot tanggal dari kolom due_dates (yang sudah ada di DB)
        $dueDates = json_decode($peminjaman->due_dates, true) ?? [];

        foreach ($items as $item) {
            $itemId = (string) $item->id_item;

            // ===== TENTUKAN STATUS DISPLAY =====
            if ($peminjaman->status === 'kembali') {
                // Record sudah selesai = semua item dikembalikan
                $item->display_status = 'dikembalikan';
            } elseif (isset($dueDates[$itemId]['returned_at']) && $dueDates[$itemId]['returned_at'] !== null) {
                // Item sudah dikembalikan (partial return)
                $item->display_status = isset($dueDates[$itemId]['status']) && $dueDates[$itemId]['status'] === 'hilang'
                    ? 'hilang'
                    : 'dikembalikan';
            } else {
                // Cek status real-time untuk backward compatibility (data lama tanpa snapshot)
                if (in_array($item->status, ['tersedia', 'hilang']) && empty($dueDates)) {
                    $item->display_status = $item->status === 'hilang' ? 'hilang' : 'dikembalikan';
                } else {
                    $item->display_status = 'dipinjam';
                }
            }

            // ===== TENTUKAN TANGGAL BATAS KEMBALI (DARI SNAPSHOT) =====
            if (isset($dueDates[$itemId]['due_date'])) {
                // Gunakan tanggal dari snapshot
                $item->loan_due_date = $dueDates[$itemId]['due_date'];
            } else {
                // Fallback ke pengembalian record (untuk data lama)
                $item->loan_due_date = $peminjaman->pengembalian;
            }

            // ===== TENTUKAN STATUS PERPANJANGAN (DARI SNAPSHOT) =====
            if (isset($dueDates[$itemId]['extended_at'])) {
                $item->loan_extended_at = $dueDates[$itemId]['extended_at'];
            } else {
                $item->loan_extended_at = null;
            }
        }

        return $items;
    }

    // =====================================================
    // STORE REQUEST - Simpan peminjaman baru dengan SNAPSHOT
    // =====================================================
    public function storeRequest(Request $req)
    {
        $req->validate([
            'id_item' => 'required|array|min:1|max:2',
            'id_item.*' => 'required|integer|exists:buku_items,id_item|distinct',
            'id_user' => 'required|exists:users,id_user',
            'id_member' => 'required|exists:users,id_user',
            'alamat' => 'required|string|max:255',
            'nama_peminjam' => 'required|string|max:255',
            'pengembalian' => 'required|date|after:now',
        ]);

        $idMember = $req->id_member;
        $member = \App\Models\User::findOrFail($idMember);

        if ($member->role !== 'konsumen' || $member->status !== 'member') {
            return back()->with('error', 'Hanya member biasa yang bisa meminjam.');
        }

        if (count($req->id_item) !== count(array_unique($req->id_item))) {
            return back()->with('error', 'Eksemplar yang sama tidak boleh dipilih dua kali.');
        }

        $now = Carbon::now();
        $today = $now->format('Ymd');

        DB::transaction(function () use ($req, $now, $today, $idMember, $member) {
            Peminjaman::where('id_member', $idMember)->lockForUpdate()->get();

            // CEK KUOTA
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

            if ($activeItemCount + count($req->id_item) > 2) {
                throw new \Exception("Member {$member->name} sudah meminjam $activeItemCount buku. Maksimal 2 buku.");
            }

            // VALIDASI ITEM
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

            // HITUNG TANGGAL PENGEMBALIAN
            $userDate = Carbon::parse($req->pengembalian)->startOfDay();
            $maxDate = $now->copy()->addDays(7)->startOfDay();
            $dueDate = $userDate->lte($maxDate) ? $userDate : $maxDate;

            // ===== BUAT SNAPSHOT TANGGAL PER-ITEM =====
            $dueDatesSnapshot = [];
            foreach ($items as $item) {
                $dueDatesSnapshot[(string)$item->id_item] = [
                    'due_date' => $dueDate->format('Y-m-d'),
                    'extended_at' => null,
                    'returned_at' => null,
                    'status' => 'dipinjam',
                    'kondisi_awal' => $item->kondisi
                ];
            }

            // SIMPAN PEMINJAMAN
            $peminjaman = Peminjaman::create([
                'id_buku' => $firstItem->id_buku,
                'id_item' => $firstItem->id_item,
                'id_items' => json_encode($req->id_item),
                'due_dates' => json_encode($dueDatesSnapshot), // <== PAKAI KOLOM due_dates YANG SUDAH ADA
                'id_user' => Auth::id(),
                'id_member' => $idMember,
                'alamat' => $req->alamat,
                'nama_peminjam' => $req->nama_peminjam,
                'pengembalian' => $dueDate->format('Y-m-d'),
                'kondisi' => $firstItem->kondisi,
                'status' => 'dipinjam',
                'request_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => $now,
                'pinjam' => $now,
                'no_transaksi' => null,
            ]);

            // GENERATE NO TRANSAKSI
            $noTransaksi = $today . '01' . str_pad($peminjaman->id_peminjaman, 4, '0', STR_PAD_LEFT);
            $peminjaman->no_transaksi = $noTransaksi;
            $peminjaman->save();

            // UPDATE STATUS ITEM
            foreach ($items as $item) {
                $item->status = 'dipinjam';
                $item->due_date = $dueDate->format('Y-m-d');
                $item->extended_at = null;
                $item->save();
            }
        });

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil untuk member!');
    }

    // =====================================================
    // PERPANJANG - Update tanggal di SNAPSHOT (due_dates)
    // =====================================================
    public function perpanjang(Request $request, $id)
    {
        $request->validate([
            'hari' => 'required|integer|min:1|max:7',
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:buku_items,id_item',
        ]);

        $peminjaman = Peminjaman::findOrFail($id);
        $selectedItems = $request->items;

        // Ambil snapshot yang ada dari kolom due_dates
        $dueDates = json_decode($peminjaman->due_dates, true) ?? [];

        // Cek apakah sudah pernah diperpanjang (dari snapshot)
        foreach ($selectedItems as $id_item) {
            $itemId = (string) $id_item;
            if (isset($dueDates[$itemId]['extended_at']) && $dueDates[$itemId]['extended_at'] !== null) {
                $item = buku_items::find($id_item);
                return back()->with('error', "Buku {$item->barcode} sudah pernah diperpanjang untuk peminjaman ini.");
            }
        }

        // PERPANJANG & UPDATE SNAPSHOT
        foreach ($selectedItems as $id_item) {
            $itemId = (string) $id_item;
            $item = buku_items::findOrFail($id_item);

            // Ambil due_date dari snapshot, fallback ke pengembalian record
            $currentDue = isset($dueDates[$itemId]['due_date'])
                ? Carbon::parse($dueDates[$itemId]['due_date'])
                : Carbon::parse($peminjaman->pengembalian);

            $newDue = $currentDue->copy()->addDays((int)$request->hari);

            // Update snapshot
            if (!isset($dueDates[$itemId])) {
                $dueDates[$itemId] = [];
            }
            $dueDates[$itemId]['due_date'] = $newDue->format('Y-m-d');
            $dueDates[$itemId]['extended_at'] = now()->format('Y-m-d');

            // Update item real (untuk tracking terkini)
            $item->due_date = $newDue->format('Y-m-d');
            $item->extended_at = now()->format('Y-m-d');
            $item->save();
        }

        // Simpan snapshot yang diupdate
        $peminjaman->due_dates = json_encode($dueDates);
        $peminjaman->status = 'diperpanjang';
        $peminjaman->save();

        return back()->with('success', "Berhasil memperpanjang " . count($selectedItems) . " buku!");
    }

    // =====================================================
    // KEMBALIKAN - Update status di SNAPSHOT (due_dates)
    // =====================================================
    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'kondisi' => 'required|in:baik,rusak,hilang',
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:buku_items,id_item',
        ]);

        $peminjaman = Peminjaman::findOrFail($id);
        $allItems = json_decode($peminjaman->id_items, true) ?? [$peminjaman->id_item];
        $selectedItems = $request->items;

        // Ambil snapshot dari kolom due_dates
        $dueDates = json_decode($peminjaman->due_dates, true) ?? [];

        // Update snapshot untuk item yang dikembalikan
        foreach ($selectedItems as $id_item) {
            $itemId = (string) $id_item;

            if (!isset($dueDates[$itemId])) {
                $dueDates[$itemId] = [
                    'due_date' => $peminjaman->pengembalian,
                ];
            }

            $dueDates[$itemId]['returned_at'] = now()->format('Y-m-d');
            $dueDates[$itemId]['status'] = $request->kondisi === 'hilang' ? 'hilang' : 'dikembalikan';
            $dueDates[$itemId]['kondisi_kembali'] = $request->kondisi;
        }

        // Simpan snapshot
        $peminjaman->due_dates = json_encode($dueDates);
        $peminjaman->save();

        // Update status item real
        buku_items::whereIn('id_item', $selectedItems)->update([
            'status' => $request->kondisi === 'hilang' ? 'hilang' : 'tersedia',
            'kondisi' => $request->kondisi,
            'due_date' => null,
            'extended_at' => null
        ]);

        // Cek apakah semua item sudah kembali
        $allReturned = true;
        foreach ($allItems as $itemId) {
            $id = (string) $itemId;
            if (!isset($dueDates[$id]['returned_at']) || $dueDates[$id]['returned_at'] === null) {
                $allReturned = false;
                break;
            }
        }

        if ($allReturned) {
            $peminjaman->status = 'kembali';
            $peminjaman->kondisi_buku_saat_kembali = $request->kondisi;
            $peminjaman->save();
        }

        return back()->with('success', 'Buku berhasil dikembalikan!');
    }

    // =====================================================
    // METHOD LAINNYA (TETAP SAMA)
    // =====================================================

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

            $peminjaman->request_status = 'approved';
            $peminjaman->approved_by = Auth::id();
            $peminjaman->approved_at = $now;
            $peminjaman->pinjam = $now;
            $peminjaman->status = 'dipinjam';

            $requestedReturn = Carbon::parse($peminjaman->pengembalian);
            if ($requestedReturn->gt($now->copy()->addDays(7))) {
                $peminjaman->pengembalian = $now->copy()->addDays(7);
            }

            $peminjaman->save();

            $item->status = 'dipinjam';
            $item->save();
        });

        return back()->with('success', 'Peminjaman berhasil disetujui.');
    }

    public function destroy($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'kembali') {
            return back()->with('error', 'Hanya peminjaman yang sudah dikembalikan bisa dihapus.');
        }

        $peminjaman->delete();

        return back()->with('success', 'Data peminjaman berhasil dihapus.');
    }

    public function searchBuku(Request $request)
    {
        $query = $request->input('query');

        $items = buku_items::with('bukus')
            ->where('status', 'tersedia')
            ->when($query, function ($q) use ($query) {
                $q->where('barcode', 'like', "%{$query}%")
                    ->orWhereHas('bukus', function ($subq) use ($query) {
                        $subq->where('judul', 'like', "%{$query}%");
                    });
            })
            ->get(['id_item', 'barcode']);

        return response()->json($items);
    }

    public function getBukusForSelection(Request $request)
    {
        $search = $request->input('search');

        $bukus = bukus::withCount([
            'items as tersedia_count' => function ($query) {
                $query->where('status', 'tersedia');
            }
        ])
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', "%{$search}%");
            })
            ->having('tersedia_count', '>', 0)
            ->orderBy('judul')
            ->paginate(10);

        return view('peminjaman.buku_table', compact('bukus'))->render();
    }

    public function getEksemplarByBuku($id_buku, Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('page', 1);

        $items = buku_items::where('id_buku', $id_buku)
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
            'links' => $items->links('pagination::bootstrap-5')->toHtml()
        ]);
    }

    public function getMembersForSelection(Request $request)
    {
        $search = $request->input('search');

        $members = \App\Models\User::where('users.status', 'member')
            ->whereNotIn('role', ['admin', 'petugas'])
            ->when($search, function ($q, $search) {
                return $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->leftJoin('peminjaman', function ($join) {
                $join->on('peminjaman.id_member', '=', 'users.id_user')
                    ->whereIn('peminjaman.status', ['dipinjam', 'diperpanjang']);
            })
            ->select(
                'users.id_user',
                'users.name',
                'users.email',
                'users.status',
                'users.role'
            )
            ->selectRaw('COALESCE(SUM(JSON_LENGTH(peminjaman.id_items)), 0) as active_loans')
            ->groupBy(
                'users.id_user',
                'users.name',
                'users.email',
                'users.status',
                'users.role'
            )
            ->orderBy('users.name')
            ->paginate(10);

        foreach ($members as $member) {
            $member->kuota = max(2 - $member->active_loans, 0);
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
