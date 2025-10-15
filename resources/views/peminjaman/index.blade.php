@extends('layouts.app')

@section('content')
    <div class="table table-responsive">
        <h2>Daftar Peminjaman</h2>
        <table class="table table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr class="text-center">
                <th>User</th>
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
                    <td>{{ $p->bukus->judul }}</td>
                    <td>{{ $p->item->id_item }}</td>
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
                        {{-- tombol untuk status dipinjam atau diperpanjang --}}
                        @if(in_array($p->status, ['dipinjam', 'diperpanjang']))
                            {{-- tombol perpanjang --}}
                            <button
                                type="button"
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#perpanjangModal"
                                data-id="{{ $p->id_peminjaman }}">
                                <i class="fas fa-clock"></i> Perpanjang
                            </button>

                            {{-- tombol kembalikan --}}
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

            <!-- Modal Perpanjang -->
            <div class="modal fade" id="perpanjangModal" tabindex="-1" aria-labelledby="perpanjangModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form id="perpanjangForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title" id="perpanjangModalLabel">Perpanjang Peminjaman</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <p>Pilih jumlah hari perpanjangan (maksimal 7 hari):</p>
                                <input type="number" name="hari" class="form-control" min="1" max="7" value="7" required>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning">Perpanjang</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            </tbody>
        </table>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var perpanjangModal = document.getElementById('perpanjangModal');
            perpanjangModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var form = document.getElementById('perpanjangForm');
                form.action = '/peminjaman/' + id + '/perpanjang';
            });
        });
    </script>
@endpush
