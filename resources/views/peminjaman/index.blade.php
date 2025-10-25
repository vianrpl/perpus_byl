@extends('layouts.app')

@section('content')
    <div class="table table-responsive">
        <h2>Daftar Peminjaman</h2>
        {{-- Form Search --}}
        <form action="{{ route('peminjaman.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Cari Data Peminjam "
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-dark">Reset</a>
                @endif
            </div>
        </form>
        <table class="table table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr class="text-center">
                <th>ID</th>
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
                    <td>{{ ($peminjaman->currentPage() - 1) * $peminjaman->perPage() + $loop->iteration }}</td>
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
                            <div class="text-center">
                                <span class="badge bg-success">Dikembalikan</span><br>
                                @if(!empty($p->kondisi_buku_saat_kembali))
                                    @php
                                        $ikon = [
                                            'baik' => '<i class="fas fa-book text-success"></i>',
                                            'rusak' => '<i class="fas fa-exclamation-triangle text-warning"></i>',
                                            'hilang' => '<i class="fas fa-times-circle text-danger"></i>',
                                        ][$p->kondisi_buku_saat_kembali] ?? '';
                                    @endphp
                                    <small class="text-muted fst-italic" style="font-size: 0.8em;">
                                        {!! $ikon !!} {{ ucfirst($p->kondisi_buku_saat_kembali) }}
                                    </small>
                                @endif
                            </div>


                        @elseif($p->status === 'ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                        @elseif($p->status === 'diperpanjang')
                            <span class="badge bg-info">Diperpanjang</span>
                        @endif
                    </td>

                    <td>
                        {{-- tombol untuk status dipinjam atau diperpanjang --}}
                        @if($p->status === 'dipinjam')
                            {{-- tombol perpanjang --}}
                            <button
                                type="button"
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#perpanjangModal"
                                data-id="{{ $p->id_peminjaman }}">
                                <i class="fas fa-clock"></i> Perpanjang
                            </button>
                        @endif

                        {{-- tombol lihat/detail --}}
                        <button
                            type="button"
                            class="btn btn-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#detailModal{{ $p->id_peminjaman }}">
                            <i class="fas fa-eye"></i> Lihat
                        </button>

                        {{-- tombol kembalikan --}}
                        @if(in_array($p->status, ['dipinjam', 'diperpanjang']))
                            <button
                                type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kembalikanModal" data-id="{{ $p->id_peminjaman }}" data-buku="{{ $p->item->id_item }}">
                                <i class="fas fa-undo"></i> Kembalikan
                            </button>
                        @endif

                    </td>
                </tr>

            @endforeach
            @if($peminjaman->isEmpty())
                <tr><td colspan="8" class="text-center">Belum ada peminjaman.</td></tr>
            @endif

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

    {{-- Modal Detail Peminjaman --}}
    @foreach($peminjaman as $p)
        <div class="modal fade" id="detailModal{{ $p->id_peminjaman }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $p->id_peminjaman }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="detailModalLabel{{ $p->id_peminjaman }}">Detail Peminjaman #{{ $p->id_peminjaman }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                            <p><b>ID Buku :</b> {{ $p->id_buku }}</p>
                            <p><b>Judul Buku :</b> {{ $p->bukus->judul ?? '-' }}</p>
                            <p><b>User :</b> {{ $p->user->name }}</p>
                            <p><b>Nama Peminjam :</b> {{ $p->nama_peminjam }}</p>
                            <p><b>Alamat :</b> {{ $p->alamat }}</p>
                            <p><b>Tanggal Pinjam :</b> {{ $p->pinjam }}</p>
                            <p><b>Batas Pengembalian :</b> {{ $p->pengembalian }}</p>
                            <p><b>Status :</b> {{ ucfirst($p->status) ?? '-' }}</p>
                            <p><b>Kondisi Buku Saat Pinjam :</b> {{ $p->kondisi ?? '-' }}</p>
                            <p><b>Kondisi Buku Saat Kembali :</b> {{ $p->kondisi_buku_saat_kembali ?? '-' }}</p>
                            <p><b>Request Status :</b> {{ ucfirst($p->request_status) ?? '-' }}</p>
                            <p><b>Waktu Disetujui :</b> {{ $p->approved_at ?? '-' }}</p>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Kembalikan -->
    <div class="modal fade" id="kembalikanModal" tabindex="-1" aria-labelledby="kembalikanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="kembalikanForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="kembalikanModalLabel">Kembalikan Buku</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label fw-semibold mb-2"> kondisi buku saat dikembalikan:</label>
                        <select name="kondisi" class="form-select border-0 shadow-sm rounded-3 py-2 px-3" style="background-color:#f8f9fa;" required>
                            <option value="baik">📗 Baik</option>
                            <option value="rusak">📘 Rusak</option>
                            <option value="hilang">📕 Hilang</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Kembalikan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        {{ $peminjaman->appends(request()->query())->links('pagination::bootstrap-5') }}
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Untuk modal perpanjang (sudah ada)
            var perpanjangModal = document.getElementById('perpanjangModal');
            perpanjangModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var form = document.getElementById('perpanjangForm');
                form.action = '/peminjaman/' + id + '/perpanjang';
            });

            // Untuk modal kembalikan
            var kembalikanModal = document.getElementById('kembalikanModal');
            kembalikanModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var form = document.getElementById('kembalikanForm');
                form.action = '/peminjaman/' + id + '/kembalikan';
            });
        });
    </script>

@endpush
