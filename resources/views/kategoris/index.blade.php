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
                            <!-- Tombol Detail (BARU) -->
                            <button class="btn btn-sm btn-info"
                                    onclick="loadSubKategoris({{ $kategori->id_kategori }}, '{{ $kategori->nama_kategori }}')">
                                Detail
                            </button>

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

    {{-- Modal Detail Sub Kategori (TARUH SEBELUM @endsection) --}}
    <div class="modal fade" id="modalDetailSubKategoris" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Sub Kategori dari: <span id="namaKategori"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Loading State --}}
                    <div id="loadingSubKategoris" class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div id="contentSubKategoris" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Sub Kategori</th>
                                    <th>Jumlah Buku</th>
                                </tr>
                                </thead>
                                <tbody id="tableBodySubKategoris">
                                {{-- Data akan dimuat via JavaScript --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div id="paginationSubKategoris" class="d-flex justify-content-center mt-3">
                            {{-- Pagination akan dimuat via JavaScript --}}
                        </div>

                        {{-- Pesan Kosong --}}
                        <div id="emptySubKategoris" style="display:none;" class="text-center py-4 text-muted">
                            Kategori ini belum memiliki sub kategori
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript untuk Load Sub Kategoris --}}
    <script>
        let currentKategoriId = null;
        let currentPage = 1;

        function loadSubKategoris(id_kategori, nama_kategori, page = 1) {
            currentKategoriId = id_kategori;
            currentPage = page;

            // Set nama kategori di modal
            document.getElementById('namaKategori').textContent = nama_kategori;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalDetailSubKategoris'));
            modal.show();

            // Show loading, hide content
            document.getElementById('loadingSubKategoris').style.display = 'block';
            document.getElementById('contentSubKategoris').style.display = 'none';

            // Fetch data
            fetch(`/kategoris/${id_kategori}/sub-kategoris?page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    // Hide loading
                    document.getElementById('loadingSubKategoris').style.display = 'none';
                    document.getElementById('contentSubKategoris').style.display = 'block';

                    const tbody = document.getElementById('tableBodySubKategoris');
                    tbody.innerHTML = '';

                    if (data.sub_kategoris && data.sub_kategoris.length > 0) {
                        // Ada data
                        document.getElementById('emptySubKategoris').style.display = 'none';

                        data.sub_kategoris.forEach((sub, index) => {
                            const row = `
                    <tr>
                        <td>${(currentPage - 1) * 5 + index + 1}</td>
                        <td>${sub.nama_sub_kategori}</td>
                        <td>${sub.jumlah_buku || 0}</td>
                    </tr>
                `;
                            tbody.innerHTML += row;
                        });

                        // Render pagination
                        renderPagination(data.pagination);
                    } else {
                        // Tidak ada data
                        document.getElementById('emptySubKategoris').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data');
                    document.getElementById('loadingSubKategoris').style.display = 'none';
                });
        }

        function renderPagination(pagination) {
            const container = document.getElementById('paginationSubKategoris');
            container.innerHTML = '';

            if (!pagination || pagination.last_page <= 1) return;

            let html = '<nav><ul class="pagination pagination-sm mb-0">';

            // Previous button
            html += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadSubKategoris(${currentKategoriId}, document.getElementById('namaKategori').textContent, ${pagination.current_page - 1}); return false;">
                Previous
            </a>
        </li>
    `;

            // Page numbers
            for (let i = 1; i <= pagination.last_page; i++) {
                html += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadSubKategoris(${currentKategoriId}, document.getElementById('namaKategori').textContent, ${i}); return false;">
                    ${i}
                </a>
            </li>
        `;
            }

            // Next button
            html += `
        <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadSubKategoris(${currentKategoriId}, document.getElementById('namaKategori').textContent, ${pagination.current_page + 1}); return false;">
                Next
            </a>
        </li>
    `;

            html += '</ul></nav>';
            container.innerHTML = html;
        }
    </script>
@endsection
