@extends('layouts.app')

@section('content')
    <div class="table table-responsive">
        <h2>Daftar Peminjaman</h2>
        <table class="table table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr class="text-center">
                <th>Nama Peminjam</th>
                <th>Buku</th>
                <th>Eksemplar</th>
                <th>Tanggal Pinjam</th>
                <th>Batas Pengembalian</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @foreach($peminjaman as $p)
                <tr class="text-center">
                    <td>{{ $p->user->name }}</td>
                    <td>{{ $p->buku->judul }}</td>
                    <td>{{ $p->item->kode_item }}</td>
                    <td>{{ $p->pinjam }}</td>
                    <td>{{ $p->pengembalian }}</td>
                    <td>
                        @if($p->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($p->status === 'dipinjam')
                            <span class="badge bg-primary">Dipinjam</span>
                        @elseif($p->status === 'kembali')
                            <span class="badge bg-success">Dikembalikan</span>
                        @elseif($p->status === 'ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                        @elseif($p->status === 'diperpanjang')
                            <span class="badge bg-info">Diperpanjang</span>
                        @endif
                    </td>

                    <td>
                        {{-- tombol untuk status dipinjam --}}
                        @if($p->status === 'dipinjam')
                            {{-- tombol perpanjang (PUT) --}}
                            <form action="{{ route('peminjaman.update', $p->id_peminjaman) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-clock"></i> Perpanjang
                                </button>
                            </form>

                            {{-- tombol kembalikan (POST) --}}
                            <form action="{{ route('peminjaman.kembalikan', $p->id_peminjaman) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-undo"></i> Kembalikan
                                </button>
                            </form>

                            {{-- tombol untuk status diperpanjang --}}
                        @elseif($p->status === 'diperpanjang')
                            <form action="{{ route('peminjaman.kembalikan', $p->id_peminjaman) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-undo"></i> Kembalikan
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
