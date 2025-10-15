@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Permintaan Peminjaman Buku</h2>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr class="text-center">
                <th>Nama Peminjam</th>
                <th>Buku</th>
                <th>Eksemplar</th>
                <th>Tanggal Pengembalian</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $req)
                <tr class="text-center">
                    <td>{{ $req->user->name }}</td>
                    <td>{{ $req->item->bukus->judul ?? '-' }}</td> {{-- ✅ Akses lewat relasi item.bukus --}}
                    <td>{{ $req->item->barcode ?? '-' }}</td> {{-- ✅ Barcode dari buku_items --}}
                    <td>{{ $req->pengembalian }}</td>
                    <td>{{ $req->alamat }}</td>
                    <td>
                        <form action="{{ route('peminjaman.approve', $req->id_peminjaman) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                        </form>
                        <form action="{{ route('peminjaman.reject', $req->id_peminjaman) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Belum ada permintaan peminjaman.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
