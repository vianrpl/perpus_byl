@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
        <h2 class="mb-3">Daftar Kategori</h2>

        <!-- tombol tambah-->
        <div class="mb-3">
            @unless(Auth::user()->role === 'konsumen')
            <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
            data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                + Tambah
            </button>
            @endunless
        </div>
        </div>

        {{-- Form Search --}}
        <form action="{{ route('kategoris.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Cari Kategori (nama kategori)"
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('kategoris.index') }}" class="btn btn-dark">Reset</a>
                @endif
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success">{{session('success')}}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr class="text-center">
                    <th>ID</th>
                    <th>Nama</th>
                    @unless(Auth::user()->role === 'konsumen')
                    <th>Aksi</th>
                    @endunless
                </tr>
                </thead>
                <tbody>
                @foreach($kategoris as $kategori)
                    <tr class="text-center">
                        <td>{{ ($kategoris->currentPage() - 1) * $kategoris->perPage() + $loop->iteration }}</td>
                        <td>{{$kategori->nama_kategori}}</td>
                        @unless(Auth::user()->role === 'konsumen')
                        <td>
                            <!-- Tombol Edit-->
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#modalEditKategori{{ $kategori->id_kategori}}">Edit</button>
                            <!-- tombol hapus -->
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus{{ $kategori->id_kategori}}">Hapus</button>

                        </td>
                        @endunless
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $kategoris->links('pagination::bootstrap-5') }}
    </div>

            <!-- modal edit kategori-->
            @foreach($kategoris as $kategori)
                <div class="modal fade" id="modalEditKategori{{ $kategori->id_kategori}}" tabindex="-1" aria-labelledby="modalEditKategoriLabel{{ $kategori->id_kategori }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content shadow-lg">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title" id="modalEditKategoriLabel{{ $kategori->id_kategori }}">Edit Kategori</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>

                            <form action="{{ route('kategoris.update', $kategori->id_kategori) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">

                                    <div class="mb-3">
                                        <label class="form-label">Kategori</label>
                                        <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" class="form-control" required>
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
                <!-- end edit kategori-->

                <!-- hapus -->
                @foreach($kategoris as $kategori)
                    <div class="modal fade" id="modalHapus{{ $kategori->id_kategori }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    Yakin ingin menghapus kategori <b>{{ $kategori->nama_kategori }}</b>?
                                </div>
                                <div class="modal-footer">
                                    <form action="{{ route('kategoris.destroy', $kategori->id_kategori) }}" method="POST">
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
                <!-- end hapus -->

                <!-- Modal Tambah-->
                <div class="modal fade" id="modalTambahKategori" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Tambah Kategori</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('kategoris.store') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control" required>
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
                <!-- end modal tambah-->
            @endforeach
@endsection
