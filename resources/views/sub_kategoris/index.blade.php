@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
        <h2 class="mb-3">Daftar Sub Kategori</h2>

        <!-- tombol tambah-->
        <div class="mb-3">
            @unless(Auth::user()->role === 'konsumen')
            <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
                    data-bs-toggle="modal" data-bs-target="#modalTambahSub">
                + Tambah
            </button>
            @endunless
        </div>
        </div>

        {{-- Form Search --}}
        <form action="{{ route('sub_kategoris.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Cari Sub (nama sub)"
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('sub_kategoris.index') }}" class="btn btn-dark">Reset</a>
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
                @foreach($sub_kategoris as $sub_kategori)
                    <tr class="text-center">
                        <td>{{ ($sub_kategoris->currentPage() - 1) * $sub_kategoris->perPage() + $loop->iteration }}</td>
                        <td>{{$sub_kategori->nama_sub_kategori}}</td>
                        @unless(Auth::user()->role === 'konsumen')
                        <td>
                            <!-- Tombol Edit-->
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#modalEditSub{{ $sub_kategori->id_sub}}">Edit</button>
                            <!-- tombol hapus -->
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus{{ $sub_kategori->id_sub}}">Hapus</button>

                        </td>
                        @endunless
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $sub_kategoris->links('pagination::bootstrap-5') }}
    </div>

    <!-- modal edit -->
    @foreach($sub_kategoris as $sub_kategori)
        <div class="modal fade" id="modalEditSub{{ $sub_kategori->id_sub}}" tabindex="-1" aria-labelledby="modalEditSubLabel{{ $sub_kategori->id_sub }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditSubLabel{{ $sub_kategori->id_sub }}">Edit Sub</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <form action="{{ route('sub_kategoris.update', $sub_kategori->id_sub) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Sub Kategori</label>
                                <input type="text" name="nama_sub_kategori" value="{{ old('nama_sub_kategori', $sub_kategori->nama_sub_kategori) }}" class="form-control" required>
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
        <!-- end edit -->

        <!-- hapus -->
        @foreach($sub_kategoris as $sub_kategori)
            <div class="modal fade" id="modalHapus{{ $sub_kategori->id_sub }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Yakin ingin menghapus Sub kategori <b>{{ $sub_kategori->nama_sub_kategori     }}</b>?
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('sub_kategoris.destroy', $sub_kategori->id_sub) }}" method="POST">
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
        <div class="modal fade" id="modalTambahSub" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Sub Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('sub_kategoris.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Sub Kategori</label>
                                <input type="text" name="nama_sub_kategori" class="form-control" required>
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
