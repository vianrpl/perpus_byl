@extends('layouts.app')

@section('content')
    <h2>Permintaan Peminjaman Pending</h2>

    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>Nama</th>
            <th>Buku</th>
            <th>Item</th>
            <th>Tanggal Pinjam</th>
            <th>Batas Pengembalian</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pending as $p)
            <tr>
                <td>{{ $p->user->name }}</td>
                <td>{{ $p->item->bukus->judul ?? '-' }}</td>
                <td>{{ $p->item->barcode }}</td>
                <td>{{ $p->pinjam ?? '-' }}</td>
                <td>{{ $p->pengembalian }}</td>
                <td>
                    <form action="{{ route('peminjaman.approve', $p->id_peminjaman) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin menyetujui peminjaman ini?')">Setujui</button>
                    </form>

                    <form action="{{ route('peminjaman.reject', $p->id_peminjaman) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Tolak permintaan ini?')">Tolak</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
