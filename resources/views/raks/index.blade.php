@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-1">
    <h2>Daftar Rak</h2>

    <div class="mb-3">
        @unless(Auth::user()->role === 'konsumen')
    <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
            data-bs-toggle="modal" data-bs-target="#modalTambahRak">
        + Tambah
    </button>
        @endunless
    </div>
    </div>

    {{-- Form Search --}}
    <form action="{{ route('raks.index') }}" method="GET" class="mb-3">
        <div class="input-group" style="max-width:400px">
            <input type="text" name="search" class="form-control" placeholder="Cari Rak (nama,kategori)"
                   value="{{ request('search') }}">
            <button class="btn btn-dark" type="submit">Cari</button>
            @if(request('search'))
                <a href="{{ route('raks.index') }}" class="btn btn-dark">Reset</a>
            @endif
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
    <table class="table table-bordered table-striped ">
        <thead class="table-dark">
        <tr class="text-center">
            <th>ID Rak</th>
            <th>Barcode</th>
            <th>Nama</th>
            <th>Kolom</th>
            <th>Baris</th>
            <th>Kapasitas</th>
            <th>Lokasi</th>
            <th>Kategori</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($raks as $rak)
            <tr class="text-center">
                <td>{{ ($raks->currentPage() - 1) * $raks->perPage() + $loop->iteration }}</td>
                <td>{{ $rak->barcode }}</td>
                <td>{{ $rak->nama }}</td>
                <td>{{ $rak->kolom }}</td>
                <td>{{ $rak->baris }}</td>
                <td>{{ $rak->kapasitas ?? 0}}/{{$rak->penataan_bukus_sum_jumlah ?? 0}}</td>
                <td>{{ $rak->lokasi_raks->ruang }}</td>
                <td>{{ $rak->kategoris->nama_kategori }}</td>
                <td class="text-center">
                    <!-- Tombol LIHAT (alihkan ke show) -->
                    <a href="{{ route('raks.show', $rak->id_rak) }}" class="btn btn-info btn-sm">
                        Lihat
                    </a>
                    @unless(Auth::user()->role === 'konsumen')
                    <!-- tombol edit-->
                    <button class="btn btn-sm btn-warning"
                            data-bs-toggle="modal" data-bs-target="#modalEditRak{{ $rak->id_rak }}">Edit</button>
                    <!-- Tombol Hapus -->
                    <button type="button" class="btn btn-sm btn-danger"
                            data-bs-toggle="modal" data-bs-target="#modalHapus{{ $rak->id_rak }}">Hapus</button>
                    @endunless
                </td>
            </tr>
            <!-- modal lihat -->
            <div class="modal fade" id="modalShowRak{{ $rak->id_rak }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">Detail Rak </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>ID Rak:</strong> {{ $rak->id_rak }}</p>
                            <p><strong>Lantai:</strong> {{ $rak->lokasi_raks->lantai ?? '-' }}</p>
                            <p><strong>Ruang:</strong> {{ $rak->lokasi_raks->ruang ?? '-' }}</p>
                            <p><strong>Sisi:</strong> {{ $rak->lokasi_raks->sisi ?? '-' }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end modal lihat-->
        @endforeach
        </tbody>
    </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $raks->links('pagination::bootstrap-5') }}
    </div>
    <!-- modal edit rak -->
    @foreach($raks as $rak)
        <div class="modal fade" id="modalEditRak{{ $rak->id_rak }}" tabindex="-1" aria-labelledby="modalEditRakLabel{{ $rak->id_rak }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditRakLabel{{ $rak->id_rak }}">Edit Rak</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <form action="{{ route('raks.update', $rak->id_rak) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">

                            <div class="mb-3">
                                <label>Barcode</label>
                                <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $rak->barcode) }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" value="{{ old('nama', $rak->nama) }}">
                            </div>
                            <div class="mb-3">
                                <label>Kolom</label>
                                <input type="text" name="kolom" class="form-control" value="{{ old('kolom', $rak->kolom) }}">
                            </div>
                            <div class="mb-3">
                                <label>Baris</label>
                                <input type="text" name="baris" class="form-control" value="{{ old('baris', $rak->baris) }}">
                            </div>
                            <div class="mb-3">
                                <label>Kapasitas</label>
                                <input type="text" name="kapasitas" class="form-control" value="{{ old('kapasitas', $rak->kapasitas) }}">
                            </div>
                            <div class="mb-3">
                                <label>Lokasi</label>
                                <input type="text" name="id_lokasi" class="form-control" value="{{ old('id_lokasi', $rak->id_lokasi) }}">
                            </div>
                            <div class="mb-3">
                                <label>Kategori</label>
                                <input type="text" name="id_kategori" class="form-control" value="{{ old('id_kategori', $rak->id_kategori) }}">
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <!-- end edit rak-->

    <!-- hapus rak -->
    @foreach($raks as $rak)
        <div class="modal fade" id="modalHapus{{ $rak->id_rak }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus Rak <b>{{ $rak->id_rak }}</b>?
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('raks.destroy', $rak->id_rak) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <!-- end hapus rak -->

    <!-- Modal Tambah rak -->
    <div class="modal fade" id="modalTambahRak" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('raks.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Barcode</label>
                            <input type="text" name="barcode" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Kolom</label>
                            <input type="text" name="kolom" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Baris</label>
                            <input type="text" name="baris" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Kapasitas</label>
                            <input type="text" name="kapasitas" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Lokasi</label>
                            <input type="text" name="id_lokasi" class="form-control" placeholder="--id lokasi--">
                        </div>
                        <div class="mb-3">
                            <label>Kategori</label>
                            <input type="text" name="id_kategori" class="form-control" placeholder="--id kategori--">
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
    <!-- End Modal tambah rak -->
@endsection
