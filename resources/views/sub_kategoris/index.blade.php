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
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sub_kategoris as $sub_kategori)
                    <tr class="text-center">
                        <td>{{ ($sub_kategoris->currentPage() - 1) * $sub_kategoris->perPage() + $loop->iteration }}</td>
                        <td>{{$sub_kategori->nama_sub_kategori}}</td>
                        <td>
                            <!-- Tombol Detail (BARU) -->
                            <button class="btn btn-sm btn-info"
                                    onclick="loadKategoris({{ $sub_kategori->id_sub }}, '{{ $sub_kategori->nama_sub_kategori }}')">
                                Detail
                            </button>

                            @unless(Auth::user()->role === 'konsumen')
                                <!-- Tombol Edit-->
                                <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal" data-bs-target="#modalEditSub{{ $sub_kategori->id_sub}}">Edit</button>
                                <!-- tombol hapus -->
                                <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus{{ $sub_kategori->id_sub}}">Hapus</button>
                            @endunless
                        </td>
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
        <div class="modal fade" id="modalHapus{{ $sub_kategori->id_sub }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus Sub kategori <b>{{ $sub_kategori->nama_sub_kategori }}</b>?
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
        <!-- end hapus -->
    @endforeach

    <!-- Modal Tambah (UPDATED DENGAN PILIH KATEGORI) -->
    <div class="modal fade" id="modalTambahSub" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Sub Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('sub_kategoris.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Sub Kategori <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_sub_kategori" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori Terkait (Opsional)</label>
                                    <input type="text" id="searchKategoriTambah" class="form-control" placeholder="Cari kategori...">
                                    <small class="text-muted">Sub kategori ini akan digunakan untuk kategori yang dipilih</small>
                                </div>
                            </div>
                        </div>

                        {{-- Tabel Pilih Kategori --}}
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="sticky-top bg-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllKategoriTambah">
                                    </th>
                                    <th>Nama Kategori</th>
                                </tr>
                                </thead>
                                <tbody id="tableKategoriTambah">
                                @foreach($kategoris as $kategori)
                                    <tr class="kategori-row-tambah">
                                        <td>
                                            <input type="checkbox" name="kategori_ids[]" value="{{ $kategori->id_kategori }}" class="kategori-checkbox-tambah">
                                        </td>
                                        <td class="kategori-name">{{ $kategori->nama_kategori }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <small><i class="bi bi-info-circle"></i> <strong>Info:</strong> Anda bisa memilih kategori yang akan menggunakan sub kategori ini. Jika tidak memilih, sub kategori tetap akan dibuat.</small>
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

    {{-- Modal Detail Kategoris (BARU) --}}
    <div class="modal fade" id="modalDetailKategoris" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Kategori yang Menggunakan: <span id="namaSubKategori"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Loading State --}}
                    <div id="loadingKategoris" class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div id="contentKategoris" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kategori</th>
                                    <th>Jumlah Buku</th>
                                </tr>
                                </thead>
                                <tbody id="tableBodyKategoris">
                                {{-- Data akan dimuat via JavaScript --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div id="paginationKategoris" class="d-flex justify-content-center mt-3">
                            {{-- Pagination akan dimuat via JavaScript --}}
                        </div>

                        {{-- Pesan Kosong --}}
                        <div id="emptyKategoris" style="display:none;" class="text-center py-4 text-muted">
                            Sub kategori ini belum digunakan oleh kategori manapun
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // SCRIPT UNTUK DETAIL KATEGORIS
        let currentSubKategoriId = null;
        let currentPageKat = 1;

        function loadKategoris(id_sub, nama_sub) {
            currentSubKategoriId = id_sub;
            currentPageKat = 1;

            document.getElementById('namaSubKategori').textContent = nama_sub;

            const modal = new bootstrap.Modal(document.getElementById('modalDetailKategoris'));
            modal.show();

            document.getElementById('loadingKategoris').style.display = 'block';
            document.getElementById('contentKategoris').style.display = 'none';

            fetchKategorisData(1);
        }

        function fetchKategorisData(page) {
            currentPageKat = page;

            fetch(`/sub_kategoris/${currentSubKategoriId}/kategoris?page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loadingKategoris').style.display = 'none';
                    document.getElementById('contentKategoris').style.display = 'block';

                    const tbody = document.getElementById('tableBodyKategoris');
                    tbody.innerHTML = '';

                    if (data.kategoris && data.kategoris.length > 0) {
                        document.getElementById('emptyKategoris').style.display = 'none';

                        data.kategoris.forEach((kat, index) => {
                            const row = `
                        <tr>
                            <td>${(currentPageKat - 1) * 5 + index + 1}</td>
                            <td>${kat.nama_kategori}</td>
                            <td>${kat.jumlah_buku || 0}</td>
                        </tr>
                    `;
                            tbody.innerHTML += row;
                        });

                        renderPaginationKat(data.pagination);
                    } else {
                        document.getElementById('emptyKategoris').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data');
                    document.getElementById('loadingKategoris').style.display = 'none';
                });
        }

        function renderPaginationKat(pagination) {
            const container = document.getElementById('paginationKategoris');
            container.innerHTML = '';

            if (!pagination || pagination.last_page <= 1) return;

            let html = '<nav><ul class="pagination pagination-sm mb-0">';

            html += `
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="fetchKategorisData(${pagination.current_page - 1}); return false;">
                    Previous
                </a>
            </li>
        `;

            for (let i = 1; i <= pagination.last_page; i++) {
                html += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="fetchKategorisData(${i}); return false;">
                        ${i}
                    </a>
                </li>
            `;
            }

            html += `
            <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="fetchKategorisData(${pagination.current_page + 1}); return false;">
                    Next
                </a>
            </li>
        `;

            html += '</ul></nav>';
            container.innerHTML = html;
        }

        // SCRIPT UNTUK MODAL TAMBAH
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('searchKategoriTambah');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.kategori-row-tambah');

                    rows.forEach(row => {
                        const text = row.querySelector('.kategori-name').textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Select all functionality
            const selectAll = document.getElementById('selectAllKategoriTambah');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.kategori-checkbox-tambah');
                    const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
                        return cb.closest('tr').style.display !== 'none';
                    });

                    visibleCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
        });
    </script>
@endsection
