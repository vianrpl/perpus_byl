@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Daftar Item Buku: {{ $buku->judul }}</h2>

    {{-- 🔹 Tentukan ID rak asal --}}
    @php
        // Jika datang dari show rak, ambil dari query string ?from_rak=...
        // Jika tidak ada, fallback ke id_rak dari item pertama (kalau ada)
        $rakId = request('from_rak') ?? (isset($items) && $items->isNotEmpty() ? $items->first()->id_rak : null);
    @endphp


    {{-- 🔹 Tombol navigasi --}}
    <a href="{{ route('bukus.index') }}" class="btn btn-primary mb-3">Daftar Buku</a>

    @if($rakId)
        {{-- 🔹 Jika ada rakId, maka tombol Rak akan kembali ke show rak asal --}}
        <a href="{{ route('raks.show', $rakId) }}" class="btn btn-primary mb-3">Rak</a>
    @else
        {{-- 🔹 Jika tidak ada rakId, fallback ke index semua rak --}}
        <a href="{{ route('raks.index') }}" class="btn btn-primary mb-3">Rak</a>
    @endif


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
                        @unless(Auth::user()->role === 'konsumen')
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

                        @unless(Auth::user()->role === 'konsumen')
                            <button type="button" class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pinjamModal{{$item->id_item}}">
                                Pinjam
                            </button>
                        @endunless
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

        <!-- Modal -->
        <div class="modal fade" id="pinjamModal{{$item->id_item}}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form id="pinjamForm{{$item->id_item}}">
                    @csrf
                    <input type="hidden" name="id_item" value="{{ $item->id_item }}">
                    <input type="hidden" name="id_buku" value="{{ $buku->id_buku }}">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Form Pinjam</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Tanggal Pengembalian</label>
                                <input type="date" name="pengembalian" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-primary" id="submitPinjam">Kirim Permintaan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('form[id^="pinjamForm"]').forEach(form => {
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        const formData = new FormData(this);
                        const itemId = formData.get('id_item');

                        fetch(`/buku_items/${itemId}/pinjam`, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: formData
                        })
                            .then(r => r.json())
                            .then(res => {
                                if(res.success){
                                    alert(res.message);
                                    location.reload();
                                } else {
                                    alert('Error: ' + (res.message || 'gagal'));
                                }
                            })
                            .catch(err => {
                                alert('Error koneksi');
                            });
                    });
                });
            });
        </script>

    @endforeach
@endsection
