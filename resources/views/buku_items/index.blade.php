@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Daftar Item Buku: {{ $buku->judul }}</h2>
    {{-- Form Search --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
        {{-- 🔍 Form Search --}}
        <form action="{{ route('bukus.items.index', $buku->id_buku) }}"  method="GET" class="d-flex" style="max-width:400px;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari Barcode"
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('bukus.items.index', $buku->id_buku) }}"  class="btn btn-dark">Reset</a>
                @endif
            </div>
        </form>
    {{-- 🔹 Tentukan ID rak asal --}}
    @php
        // Jika datang dari show rak, ambil dari query string ?from_rak=...
        // Jika tidak ada, fallback ke id_rak dari item pertama (kalau ada)
        $rakId = request('from_rak') ?? (isset($items) && $items->isNotEmpty() ? $items->first()->id_rak : null);
    @endphp


    {{-- 🔹 Tombol navigasi --}}
        <div class="d-flex align-items-center" style="gap:8px;">
    <a href="{{ route('bukus.index') }}" class="btn btn-primary mb-1">Daftar Buku</a>

    @if($rakId)
        {{-- 🔹 Jika ada rakId, maka tombol Rak akan kembali ke show rak asal --}}
        <a href="{{ route('raks.show', $rakId) }}" class="btn btn-primary mb-1">Rak</a>
    @else
        {{-- 🔹 Jika tidak ada rakId, fallback ke index semua rak --}}
        <a href="{{ route('raks.index') }}" class="btn btn-primary mb-1">Rak</a>
    @endif
    </div>
    </div>

    {{-- Tabel daftar item --}}
    <div class="table table-responsive">
        <table class="table table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Sumber</th>
                <th>Rak</th>
                <th>Barcode</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                    <td>{{ $item->kondisi }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->sumber }}</td>
                    <td>{{ $item->rak->nama ?? $item->id_rak }}</td>
                    <td>{{ $item->barcode }}</td>
                    <td>
                        <!-- Tombol LIHAT -->
                        <button type="button" class="btn btn-info btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalShowItem{{ $item->id_item }}">
                            Lihat
                        </button>

                        <!-- Tombol EDIT -->
                        @unless($item->kondisi === 'hilang' || $item->status === 'hilang' || Auth::user()->role === 'konsumen')
                            <button type="button" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditItem{{ $item->id_item }}">
                                Edit
                            </button>
                        @endunless

                        <!-- Tombol HAPUS -->
                        @unless(Auth::user()->role === 'konsumen')
                            <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalHapusItem{{ $item->id_item }}">
                                Hapus
                            </button>
                        @endunless

                        @if($item->status === 'tersedia')
                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pinjamModal"
                                    data-id="{{ $item->id_item}}"
                                    data-judul="{{ $item->bukus->judul}}">
                                Pinjam
                            </button>
                        @else
                            @if($item->status === 'hilang' || $item->kondisi === 'hilang')
                                <span class="badge bg-danger">Hilang</span>
                            @else
                                <span class="badge bg-secondary">Tidak tersedia</span>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada item untuk buku ini</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔹 Pagination tetap membawa query from_rak agar tidak hilang saat pindah halaman --}}
    <div class="d-flex justify-content-center">
        {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    <!-- ============================= -->
    <!-- Modal Tambah Item -->
    <!-- ============================= -->
    <div class="modal fade" id="modalTambahBuku" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Item Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('bukus.items.store', $buku->id_buku) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kondisi</label>
                            <select name="kondisi" class="form-control">
                                <option value="baik">Baik</option>
                                <option value="rusak">Rusak</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="tersedia">Tersedia</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sumber</label>
                            <input type="text" name="sumber" value="{{ old('sumber') }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak</label>
                            <select name="id_rak" class="form-control">
                                @foreach($raks as $rak)
                                    <option value="{{ $rak->id_rak }}">{{ $rak->nama ?? $rak->nama_rak }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================= -->
    <!-- Modal Lihat/Edit/Hapus per Item -->
    <!-- ============================= -->
    @foreach ($items as $item)
        <!-- Modal Lihat -->
        <div class="modal fade" id="modalShowItem{{ $item->id_item }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Item </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><b>Barcode:</b> {{ $item->barcode ?? '-' }}</p>
                        <p><b>Kondisi:</b> {{ $item->kondisi }}</p>
                        <p><b>Status:</b> {{ $item->status }}</p>
                        <p><b>Sumber:</b> {{ $item->sumber ?? '-' }}</p>
                        <p><b>Rak:</b> {{ $item->rak->nama ?? $item->id_rak }}</p>
                        <hr>
                        <p><b>Insert Date:</b> {{ $item->insert_date ? $item->insert_date->format('d M Y H:i') : '-' }}</p>
                        <p><b>Modified Date:</b> {{ $item->modified_date ? $item->modified_date->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditItem{{ $item->id_item }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Item </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('bukus.items.update', [$buku->id_buku, $item->id_item]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Kondisi</label>
                                <select name="kondisi" class="form-control">
                                    <option value="baik" {{ $item->kondisi=='baik'?'selected':'' }}>Baik</option>
                                    <option value="rusak" {{ $item->kondisi=='rusak'?'selected':'' }}>Rusak</option>
                                    <option value="hilang" {{ $item->kondisi=='hilang'?'selected':'' }}>Hilang</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="tersedia" {{ $item->status=='tersedia'?'selected':'' }}>Tersedia</option>
                                    <option value="dipinjam" {{ $item->status=='dipinjam'?'selected':'' }}>Dipinjam</option>
                                    <option value="hilang" {{ $item->status=='hilang'?'selected':'' }}>Hilang</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sumber</label>
                                <input type="text" name="sumber" value="{{ $item->sumber }}" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rak</label>
                                <select name="id_rak" class="form-control">
                                    @foreach($raks as $rak)
                                        <option value="{{ $rak->id_rak }}" {{ $item->id_rak==$rak->id_rak?'selected':'' }}>
                                            {{ $rak->nama ?? $rak->nama_rak }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal fade" id="modalHapusItem{{ $item->id_item }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus item buku <b>{{ $buku->judul }}</b>?
                    </div>
                    <form action="{{ route('bukus.items.destroy', [$buku->id_buku, $item->id_item]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <!-- ============================= -->
    <!-- Modal Pinjam (1x saja di luar loop) -->
    <!-- ============================= -->
    <div class="modal fade" id="pinjamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('peminjaman.request') }}" id="formPinjam">
                @csrf
                <input type="hidden" name="id_item" id="modal_id_item" />
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Permintaan Pinjam - <span id="modal_judul"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>User</label>
                            <input type="text" class="form-control" name="nama" value="{{ auth()->user()->name }}" readonly>
                        </div>
                        <div class="mb-2">
                            <label>Alamat</label>
                            <input type="text" class="form-control" name="alamat" required>
                        </div>
                        <div class="mb-2">
                            <label>Nama Peminjam</label>
                            <input type="text" class="form-control" name="nama_peminjam" required>
                        </div>
                        <div class="mb-2">
                            <label>Tanggal Pengembalian (maks 7 hari)</label>
                            <input type="date" class="form-control" id="pengembalian" name="pengembalian" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================= -->
    <!-- Script Modal Pinjam -->
    <!-- ============================= -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('button[data-bs-target="#pinjamModal"]');
            const idField = document.getElementById('modal_id_item');
            const judulField = document.getElementById('modal_judul');
            const dateInput = document.getElementById('pengembalian');

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const judul = this.dataset.judul || 'Judul tidak ditemukan';

                    idField.value = id;
                    judulField.textContent = judul;

                    // atur tanggal min dan max (maks 7 hari)
                    const today = new Date();
                    const min = today.toISOString().split('T')[0];

                    const maxDate = new Date(today);
                    maxDate.setDate(maxDate.getDate() + 7);
                    const max = maxDate.toISOString().split('T')[0];

                    dateInput.min = min;
                    dateInput.max = max;
                    dateInput.value = min;
                });
            });
        });
    </script>

@endsection
