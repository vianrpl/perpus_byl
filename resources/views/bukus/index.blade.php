@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Daftar Buku</h1>
        @unless(Auth::user()->role === 'konsumen')
        <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
                data-bs-toggle="modal" data-bs-target="#modalTambahBuku">
            + Tambah Buku
        </button>
        @endunless
    </div>
<!--search-->
    <form method="GET" action="{{ route('bukus.index') }}" class="mb-3">
        <div class="input-group" style="max-width:400px">
            <input type="text" name="search" class="form-control" placeholder="Cari judul/penerbit/pengarang/tahun/kategori/sub" value="{{ request('search') }}">
            <button class="btn btn-dark">Cari</button>
            @if(request('search'))
                <a href="{{route('bukus.index')}}" class="btn btn-dark">Reset</a>
            @endif
        </div>
    </form>
    <!-- end search-->

    @if(session('success'))
        <div class="alert alert-success">{{session('success')}}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr class="text-center">
                <th>ID</th>
                <th>Judul</th>
                <th>Penerbit</th>
                <th>Pengarang</th>
                <th>Tahun</th>
                <th>Kategori</th>
                <th>ISBN</th>
                <th>Sub Kategori</th>
                <th>Barcode</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($buku as $b)
                <tr>
                    <td class="text-center">{{ ($buku->currentPage() - 1) * $buku->perPage() + $loop->iteration }}</td>
                    <td>{{ $b->judul }}</td>
                    <td>{{ $b->penerbits->nama_penerbit }}</td>
                    <td>{{ $b->pengarang }}</td>
                    <td class="text-center">{{ $b->tahun_terbit }}</td>
                    <td>{{ $b->kategoris->nama_kategori }}</td>
                    <td class="text-center">{{ $b->isbn }}</td>
                    <td>{{ $b->sub_kategoris ? $b->sub_kategoris->nama_sub_kategori : '-' }}</td>
                    <td>{{ $b->barcode }}</td>
                    <td class="text-center
    @if($b->jumlah_tata == 0)
        text-danger
    @elseif($b->jumlah_tata < $b->jumlah)
        text-warning
    @else
        text-success
    @endif">
                        {{ $b->jumlah }} / {{ $b->jumlah_tata ?? 0 }}
                    </td>




                    <td class="text-center">
                        <a href="{{ route('bukus.items.index', $b->id_buku) }}" class="btn btn-sm btn-info">Eksemplar</a>

                        <!-- tombol edit-->
                        @unless(Auth::user()->role === 'konsumen')
                        <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal" data-bs-target="#modalEditBuku{{ $b->id_buku }}">Edit</button>
                        @endunless

                        <!-- Tombol Hapus -->
                        @unless(Auth::user()->role === 'konsumen')
                        <button type="button" class="btn btn-sm btn-danger"
                                data-bs-toggle="modal" data-bs-target="#modalHapus{{ $b->id_buku }}">Hapus</button>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">Belum ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $buku->links('pagination::bootstrap-5') }}
    </div>

    <!-- hapus buku -->
    @foreach($buku as $b)
    <div class="modal fade" id="modalHapus{{ $b->id_buku }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Yakin ingin menghapus buku <b>{{ $b->judul }}</b>?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('bukus.destroy', $b->id_buku) }}" method="POST">
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
    <!-- end hapus buku -->

    <!-- Modal Tambah Buku -->
    <div class="modal fade" id="modalTambahBuku" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('bukus.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Judul</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Penerbit</label>
                            <select name="id_penerbit" class="form-control">
                                <option value="">-- Pilih Penerbit --</option>
                                @foreach($penerbits as $p)
                                    <option value="{{ $p->id_penerbit }}">{{ $p->nama_penerbit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Pengarang</label>
                            <input type="text" name="pengarang" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Tahun Terbit</label>
                            <input type="text" name="tahun_terbit" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Kategori</label>
                            <select name="id_kategori" class="form-control">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Sub Kategori</label>
                            <select name="id_sub" class="form-control">
                                <option value="">-- Pilih Sub Kategori --</option>
                                @foreach($sub_kategoris as $s)
                                    <option value="{{ $s->id_sub }}">{{ $s->nama_sub_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah" class="form-control">
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
    <!-- End Modal Tambah -->


    <!-- modal edit buku -->
    @foreach($buku as $buku)
        <!-- Modal Edit Buku -->
        <div class="modal fade" id="modalEditBuku{{ $buku->id_buku }}" tabindex="-1" aria-labelledby="modalEditBukuLabel{{ $buku->id_buku }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditBukuLabel{{ $buku->id_buku }}">Edit Buku</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <form action="{{ route('bukus.update', $buku->id_buku) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">

                            <div class="mb-2">
                                <label class="form-label">Judul Buku</label>
                                <input type="text" name="judul" class="form-control" value="{{ $buku->judul }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Pengarang</label>
                                <input type="text" name="pengarang" class="form-control" value="{{ $buku->pengarang }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Penerbit</label>
                                <select name="id_penerbit" id="id_penerbit_{{ $buku->id_penerbit }}" class="form-control" required>
                                    <option value="">-- Pilih penerbit --</option>
                                    @foreach($penerbits as $p)
                                        <option value="{{ $p->id_penerbit }}"
                                            {{ old('id_penerbit', $buku->id_penerbit) == $buku->id_penerbit ? 'selected' : '' }}>
                                            {{ $p->nama_penerbit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Kategori</label>
                                <select name="id_kategori" id="id_kategori_{{ $buku->id_kategori }}" class="form-control" required>
                                    <option value="">-- Pilih kategori --</option>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id_kategori }}"
                                            {{ old('id_kategori', $buku->id_kategori) == $buku->id_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Sub Kategori</label>
                                <select name="id_sub" id="id_sub_{{ $buku->id_sub }}"class="form-control" required>
                                    <option value="">-- Pilih Sub Kategori --</option>
                                    @foreach($sub_kategoris as $sk)
                                        <option value="{{ $sk->id_sub }}"
                                            {{ old('id_sub',$buku->id_sub )== $buku->id_sub ? 'selected' : '' }}>
                                            {{ $sk->nama_sub_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Tahun Terbit</label>
                                <input type="text" name="tahun_terbit" class="form-control" value="{{ $buku->tahun_terbit }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">ISBN</label>
                                <input type="text" name="isbn" class="form-control" value="{{ $buku->isbn }}">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Barcode</label>
                                <input type="text" name="barcode" class="form-control" value="{{ $buku->barcode }}">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Jumlah</label>
                                <input type="number" name="jumlah" class="form-control" value="{{ $buku->jumlah }}">
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
    <!-- end edit buku-->

@endsection
