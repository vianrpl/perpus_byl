<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\BukuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    // tampilkan semua data peminjaman (khusus admin/petugas)
    public function index()
    {
        $this->authorizeRole(['admin', 'petugas']);

        $peminjaman = Peminjaman::with(['user', 'buku', 'item'])->get();
        return view('peminjaman.index', compact('peminjaman'));
    }

    // fungsi pinjam (dipanggil saat user klik tombol pinjam)
    public function store(Request $request)
    {
        $request->validate([
            'id_item' => 'required|exists:buku_items,id_item',
            'id_buku' => 'required|exists:bukus,id_buku',
            'pengembalian' => 'required|date|after:today',
            'alamat' => 'nullable|string|max:255'
        ]);

        $user = auth()->user();
        if (!in_array($user->role, ['member','petugas','admin'])) {
            abort(403, 'Anda tidak berhak melakukan peminjaman');
        }

        $p = Peminjaman::create([
            'id_user' => $user->id_user,
            'id_item' => $request->id_item,
            'id_buku' => $request->id_buku,
            'pinjam' => now(),
            'pengembalian' => $request->pengembalian,
            'kondisi' => $request->kondisi ?? 'baik',
            'status' => 'pending',
            'alamat' => $request->alamat
        ]);

        // bisa kirim email/notification ke admin/petugas nanti
        return response()->json(['success' => true, 'message' => 'Permintaan peminjaman terkirim, menunggu persetujuan.']);
    }

    public function return(Request $request, $id)
    {
        $this->authorizeRole(['admin','petugas']);
        $p = Peminjaman::findOrFail($id);

        if (!in_array($p->status, ['dipinjam','diperpanjang'])) {
            return back()->with('error','Peminjaman tidak dalam status aktif.');
        }

        $item = $p->item;
        if ($item) {
            $item->update(['status' => 'tersedia']);
        }

        $p->update(['status' => 'kembali']);

        return back()->with('success','Buku telah dikembalikan.');
    }



    public function notif()
    {
        $this->authorizeRole(['admin','petugas']);
        $pending = Peminjaman::with(['user','buku','item'])
            ->where('status','pending')
            ->orderBy('pinjam','desc')
            ->get();
        return view('peminjaman.notif', compact('pending'));
    }



    public function approve($id)
    {
        $this->authorizeRole(['admin','petugas']);
        $p = Peminjaman::findOrFail($id);

        if ($p->status !== 'pending') {
            return back()->with('error','Status bukan pending.');
        }

        $item = $p->item;
        if (!$item) return back()->with('error','Item tidak ditemukan.');

        $item->update(['status' => 'dipinjam']);
        $p->update(['status' => 'dipinjam', 'pinjam' => now()]);

        return back()->with('success','Peminjaman disetujui.');
    }

    public function reject($id)
    {
        $this->authorizeRole(['admin','petugas']);
        $p = Peminjaman::findOrFail($id);
        if ($p->status !== 'pending') {
            return back()->with('error','Status bukan pending.');
        }
        $p->update(['status' => 'ditolak']);
        return back()->with('success','Permintaan peminjaman ditolak.');
    }

    // helper biar hanya role tertentu bisa akses
    private function authorizeRole(array $roles)
    {
        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorizeActionForStaff();
        $p = Peminjaman::findOrFail($id);

        $request->validate([
            'pengembalian' => 'required|date|after:today'
        ]);

        // cek perpanjang maksimal 7 hari dari pinjam
        $maxExtendDate = \Carbon\Carbon::parse($p->pinjam)->addDays(7);
        if (\Carbon\Carbon::parse($request->pengembalian)->gt($maxExtendDate)) {
            return back()->with('error','Perpanjangan maksimal 7 hari dari tanggal pinjam.');
        }

        $p->pengembalian = $request->pengembalian;
        $p->save();

        return back()->with('success','Tanggal pengembalian diperbarui.');
    }

}
