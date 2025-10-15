<?php

namespace App\Http\Controllers;

use App\Models\buku_items;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    // 🔹 Menampilkan semua peminjaman (untuk admin/petugas)
    public function index()
    {
        $peminjaman = Peminjaman::with(['user', 'item.bukus'])
            ->orderBy('id_peminjaman', 'desc')
            ->get();

        return view('peminjaman.index', compact('peminjaman'));
    }

    // 🔹 User mengirim permintaan pinjam
    public function storeRequest(Request $req)
    {
        $req->validate([
            'id_item' => 'required|integer|exists:buku_items,id_item',
            'alamat' => 'required|string|max:255',
            'pengembalian' => 'required|date|after:now',
        ]);

        $item = buku_items::findOrFail($req->id_item);
        if ($item->status !== 'tersedia') {
            return back()->withErrors(['id_item' => 'Item tidak tersedia.']);
        }

        // buat permintaan (belum aktif)
        Peminjaman::create([
            'id_buku' => $item->id_buku,
            'id_user' => Auth::id(),
            'id_item' => $item->id_item,
            'alamat' => $req->alamat,
            'pengembalian' => $req->pengembalian,
            'kondisi' => $item->kondisi,
            'status' => null,
            'request_status' => 'pending',
            'pinjam' => null,
        ]);

        return back()->with('success', 'Permintaan peminjaman berhasil dikirim. Tunggu persetujuan admin/petugas.');
    }


    // 🔹 Admin/petugas melihat permintaan pinjam
    public function requestsIndex()
    {
        $requests = Peminjaman::with(['user', 'item.bukus'])
            ->where('request_status', 'pending')
            ->orderBy('id_peminjaman', 'desc')
            ->get();

        return view('peminjaman.requests', compact('requests'));
    }

    // 🔹 Menyetujui permintaan
    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $now = \Carbon\Carbon::now();
            $p = Peminjaman::lockForUpdate()->findOrFail($id);
            if ($p->request_status !== 'pending') abort(400, 'Sudah diproses');

            $item = buku_items::lockForUpdate()->findOrFail($p->id_item);
            if ($item->status !== 'tersedia') abort(400, 'Item tidak tersedia');

            $p->request_status = 'approved';
            $p->approved_by = Auth::id();
            $p->approved_at = Carbon::now();
            $p->pinjam = Carbon::now();
            $p->status = 'dipinjam';

            // Ambil tanggal pengembalian dari request user (sudah tersimpan sebelumnya)
            if ($p->pengembalian) {
                $requestedReturn = Carbon::parse($p->pengembalian);

                // Kalau lebih dari 7 hari dari tanggal pinjam, batasi
                if ($requestedReturn->gt($now->copy()->addDays(7))) {
                    $p->pengembalian = $now->copy()->addDays(7);
                } else {
                    $p->pengembalian = $requestedReturn;
                }
            } else {
                // fallback kalau user belum isi, otomatis +7 hari
                $p->pengembalian = $now->copy()->addDays(7);
            }
            $p->save();

            $item->status = 'dipinjam';
            $item->save();
        });

        return back()->with('success', 'Permintaan disetujui.');
    }

    // 🔹 Menolak permintaan
    public function reject($id, Request $req)
    {
        $p = Peminjaman::findOrFail($id);
        if ($p->request_status !== 'pending') {
            return back()->withErrors('Tidak dapat menolak request yang sudah diproses.');
        }

        $p->delete(); // langsung hapus record
        return back()->with('success', 'Permintaan ditolak.');
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


    public function kembalikan($id)
    {
        DB::transaction(function() use ($id) {
            $p = Peminjaman::lockForUpdate()->findOrFail($id);
            if (!in_array($p->status, ['dipinjam', 'diperpanjang'])) {
                abort(400, 'Hanya buku yang sedang dipinjam bisa dikembalikan.');
            }

            // ubah status jadi kembali
            $p->status = 'kembali';
            $p->request_status = 'returned';
            $p->save();

            // ubah status item buku jadi tersedia lagi
            $item = buku_items::lockForUpdate()->findOrFail($p->id_item);
            $item->status = 'tersedia';
            $item->save();
        });

        return back()->with('success', 'Buku berhasil dikembalikan.');
    }

}
