@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
        <h2 class="mb-3">Daftar Lokasi Rak</h2>

        <!-- tombol tambah-->
        <div class="mb-3">
            @unless(Auth::user()->role === 'konsumen')
            <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
                    data-bs-toggle="modal" data-bs-target="#modalTambahLokRak">
                + Tambah
            </button>
            @endunless
        </div>
        </div>
        <!-- end tombol tambah-->

        {{-- Form Search --}}
        <form action="{{ route('lokasi_raks.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Cari Lokasi (lantai,ruang)"
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('lokasi_raks.index') }}" class="btn btn-dark">Reset</a>
                @endif
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr class="text-center">
                <th>ID</th>
                <th>Lantai</th>
                <th>Ruang</th>
                <th>Sisi</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @foreach($lokasi_raks as $lokasi_rak)
                <tr class="text-center">
                    <td>{{ ($lokasi_raks->currentPage() - 1) * $lokasi_raks->perPage() + $loop->iteration }}</td>
                    <td>{{ $lokasi_rak->lantai }}</td>
                    <td>{{ $lokasi_rak->ruang }}</td>
                    <td>{{ $lokasi_rak->sisi ?? '-' }}</td>
                    <td>

                        <!-- Tombol LIHAT -->
                        <button type="button" class="btn btn-info btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalShowLokRak{{ $lokasi_rak->id_lokasi }}">
                            Lihat
                        </button>
                        @unless(Auth::user()->role === 'konsumen')
                        <!-- tombol edit-->
                        <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal" data-bs-target="#modalEditLokRak{{ $lokasi_rak->id_lokasi }}">Edit</button>

                        <!-- Tombol Hapus -->
                        <button type="button" class="btn btn-sm btn-danger"
                                data-bs-toggle="modal" data-bs-target="#modalHapus{{ $lokasi_rak->id_lokasi }}">Hapus</button>
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

        <div class="d-flex justify-content-center">
            {{ $lokasi_raks->links('pagination::bootstrap-5') }}
        </div>

        <!-- modal show lokasi rak-->
        @foreach($lokasi_raks as $lokasi_rak)
        <div class="modal fade" id="modalShowLokRak{{ $lokasi_rak->id_lokasi }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Lokasi Rak </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>ID Rak:</strong> {{ $lokasi_rak->id_lokasi }}</p>
                        <p><strong>Lantai:</strong> {{ $lokasi_rak->lantai ?? '-' }}</p>
                        <p><strong>Ruang:</strong> {{ $lokasi_rak->ruang ?? '-' }}</p>
                        <p><strong>Sisi:</strong> {{ $lokasi_rak->sisi ?? '-' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        <!-- end modal lihat-->

        <!-- modal edit rak -->
        @foreach($lokasi_raks as $lokasi_rak)
            <div class="modal fade" id="modalEditLokRak{{ $lokasi_rak->id_lokasi }}" tabindex="-1" aria-labelledby="modalEditLokRakLabel{{ $lokasi_rak->id_lokasi }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content shadow-lg">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title" id="modalEditLokRakLabel{{ $lokasi_rak->id_lokasi }}">Edit Lokasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <form action="{{ route('lokasi_raks.update', $lokasi_rak->id_lokasi) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label">Lantai</label>
                                    <input type="text" name="lantai" value="{{ old('lantai', $lokasi_rak->lantai) }}" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ruang</label>
                                    <input type="text" name="ruang" value="{{ old('ruang', $lokasi_rak->ruang) }}" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Sisi</label>
                                    <input type="text" name="sisi" value="{{ old('sisi', $lokasi_rak->sisi) }}" class="form-control">
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

        <!-- Modal Tambah rak -->
        <div class="modal fade" id="modalTambahLokRak" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Lokasi Rak</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('lokasi_raks.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Lantai</label>
                                <input type="text" name="lantai" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ruang</label>
                                <input type="text" name="ruang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sisi</label>
                                <input type="text" name="sisi" class="form-control">
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

        <!-- hapus rak -->
        @foreach($lokasi_raks as $lokasi_rak)
            <div class="modal fade" id="modalHapus{{ $lokasi_rak->id_lokasi }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Yakin ingin menghapus Lokasi Rak <b>{{ $lokasi_rak->id_lokasi }}</b>?
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('lokasi_raks.destroy', $lokasi_rak->id_lokasi) }}" method="POST">
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
@endsection
