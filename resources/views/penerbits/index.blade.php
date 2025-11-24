@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-1">
        <h2 class="mb-1">Daftar Penerbit</h2>
        <!-- tombol tambah-->
        <div class="mb-3">
            @unless(Auth::user()->role === 'konsumen')
            <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
                    data-bs-toggle="modal" data-bs-target="#modalTambahPenerbit">
                + Tambah
            </button>
            @endunless
        </div>
        </div>
        {{-- Form Search --}}
        <form action="{{ route('penerbits.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Cari Penerbit (id,nama)"
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('penerbits.index') }}" class="btn btn-dark">Reset</a>
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
                    <th style="width: 5%">ID</th>
                    <th style="width: 40%">Nama</th>
                    <th style="width: 30%">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach($penerbits as $penerbit)
                    <tr class="text-center">
                        <td>{{ ($penerbits->currentPage() - 1) * $penerbits->perPage() + $loop->iteration }}</td>
                        <td>{{ $penerbit->nama_penerbit }}</td>
                        <td>
                            <!-- Tombol Buku (BARU) -->
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="loadBukusPenerbit({{ $penerbit->id_penerbit }}, '{{ $penerbit->nama_penerbit }}')">
                                Buku
                            </button>

                            <!-- Tombol lihat -->
                            <button type="button" class="btn btn-info btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalShowPenerbit{{ $penerbit->id_penerbit }}">
                                Lihat
                            </button>
                            @unless(Auth::user()->role === 'konsumen')
                            <!-- Tombol Edit-->
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#modalEditPenerbit{{ $penerbit->id_penerbit }}">Edit</button>
                            <!-- tombol hapus -->
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus{{ $penerbit->id_penerbit }}">Hapus</button>
                            @endunless
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $penerbits->links('pagination::bootstrap-5') }}
    </div>

    <!-- modal edit penerbit-->
    @foreach($penerbits as $penerbit)
        <div class="modal fade" id="modalEditPenerbit{{ $penerbit->id_penerbit }}" tabindex="-1" aria-labelledby="modalEditPenerbitLabel{{ $penerbit->id_penerbit }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditPenerbitLabel{{ $penerbit->id_penerbit }}">Edit Penerbit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <form action="{{ route('penerbits.update', $penerbit->id_penerbit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama_penerbit" value="{{ old('nama_penerbit', $penerbit->nama_penerbit) }}" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <input type="text" name="alamat" value="{{ old('alamat', $penerbit->alamat) }}" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="no_telepon" value="{{ old('no_telepon', $penerbit->no_telepon) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" name="email" value="{{ old('email', $penerbit->email) }}" class="form-control">
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
        <!-- end edit penerbit-->

        <!-- hapus  -->
        @foreach($penerbits as $penerbit)
            <div class="modal fade" id="modalHapus{{ $penerbit->id_penerbit }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Yakin ingin menghapus penerbit <b>{{ $penerbit->nama_penerbit }}</b>?
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('penerbits.destroy', $penerbit->id_penerbit) }}" method="POST">
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
        <!-- end hapus  -->

        <!-- Modal Tambah penerbit -->
        <div class="modal fade" id="modalTambahPenerbit" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Penerbit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('penerbits.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama_penerbit" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <input type="text" name="alamat" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="no_telepon" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" name="email" class="form-control">
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
<!-- end modal tambah penerbit -->

        <!-- modal show penerbit-->
        @foreach($penerbits as $penerbit)
            <div class="modal fade" id="modalShowPenerbit{{ $penerbit->id_penerbit}}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">Detail Penerbit </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>ID:</strong> {{ $penerbit->id_penerbit }}</p>
                            <p><strong>Nama:</strong> {{ $penerbit->nama_penerbit ?? '-' }}</p>
                            <p><strong>Alamat:</strong> {{ $penerbit->alamat ?? '-' }}</p>
                            <p><strong>Telepon:</strong> {{ $penerbit->no_telepon ?? '-' }}</p>
                            <p><strong>Email:</strong> {{ $penerbit->email ?? '-' }}</p>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <!-- end modal lihat-->

        {{-- Modal Daftar Buku Penerbit --}}
        <div class="modal fade" id="modalBukusPenerbit" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Daftar Buku dari Penerbit: <span id="namaPenerbitBuku"></span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Loading State --}}
                        <div id="loadingBukusPenerbit" class="text-center py-4">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data buku...</p>
                        </div>

                        {{-- Content --}}
                        <div id="contentBukusPenerbit" style="display:none;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Pengarang</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th>Tahun</th>
                                        <th>Stok</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tableBodyBukusPenerbit">
                                    {{-- Data akan dimuat via JavaScript --}}
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div id="paginationBukusPenerbit" class="d-flex justify-content-center mt-3">
                                {{-- Pagination akan dimuat via JavaScript --}}
                            </div>

                            {{-- Pesan Kosong --}}
                            <div id="emptyBukusPenerbit" style="display:none;" class="alert alert-info text-center">
                                <i class="bi bi-inbox"></i> Penerbit ini belum memiliki buku
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- JavaScript untuk Load Bukus Penerbit --}}
        <script>
            let currentPenerbitId = null;
            let currentPageBuku = 1;

            function loadBukusPenerbit(id_penerbit, nama_penerbit) {
                console.log('Load buku untuk penerbit:', id_penerbit, nama_penerbit); // Debug

                currentPenerbitId = id_penerbit;
                currentPageBuku = 1;

                // Set nama penerbit di modal
                document.getElementById('namaPenerbitBuku').textContent = nama_penerbit;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('modalBukusPenerbit'));
                modal.show();

                // Show loading, hide content
                document.getElementById('loadingBukusPenerbit').style.display = 'block';
                document.getElementById('contentBukusPenerbit').style.display = 'none';

                // Fetch data
                fetchBukusPenerbitData(1);
            }

            function fetchBukusPenerbitData(page) {
                currentPageBuku = page;

                const url = `/penerbits/${currentPenerbitId}/bukus?page=${page}`;
                console.log('Fetching URL:', url); // Debug

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        console.log('Response status:', response.status); // Debug
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Data received:', data); // Debug

                        // Hide loading
                        document.getElementById('loadingBukusPenerbit').style.display = 'none';
                        document.getElementById('contentBukusPenerbit').style.display = 'block';

                        const tbody = document.getElementById('tableBodyBukusPenerbit');
                        tbody.innerHTML = '';

                        if (data.bukus && data.bukus.length > 0) {
                            document.getElementById('emptyBukusPenerbit').style.display = 'none';

                            data.bukus.forEach((buku, index) => {
                                const row = `
                    <tr>
                        <td>${(currentPageBuku - 1) * 5 + index + 1}</td>
                        <td>${buku.judul || '-'}</td>
                        <td>${buku.pengarang || '-'}</td>
                        <td>${buku.kategoris ? buku.kategoris.nama_kategori : '-'}</td>
                        <td>${buku.sub_kategoris ? buku.sub_kategoris.nama_sub_kategori : '-'}</td>
                        <td>${buku.tahun_terbit || '-'}</td>
                        <td><span class="badge bg-primary">${buku.jumlah || 0}</span></td>
                    </tr>
                `;
                                tbody.innerHTML += row;
                            });

                            // Render pagination jika ada
                            if (data.pagination) {
                                renderPaginationBukuPenerbit(data.pagination);
                            }
                        } else {
                            document.getElementById('emptyBukusPenerbit').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('loadingBukusPenerbit').style.display = 'none';
                        document.getElementById('contentBukusPenerbit').style.display = 'block';
                        document.getElementById('emptyBukusPenerbit').style.display = 'block';
                        document.getElementById('emptyBukusPenerbit').innerHTML = `
            <i class="bi bi-exclamation-triangle"></i>
            Terjadi kesalahan saat mengambil data: ${error.message}
        `;
                    });
            }

            function renderPaginationBukuPenerbit(pagination) {
                const container = document.getElementById('paginationBukusPenerbit');
                container.innerHTML = '';

                if (!pagination || pagination.last_page <= 1) return;

                let html = '<nav><ul class="pagination pagination-sm mb-0">';

                // Previous button
                html += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="fetchBukusPenerbitData(${pagination.current_page - 1}); return false;">
                Previous
            </a>
        </li>
    `;

                // Page numbers
                for (let i = 1; i <= pagination.last_page; i++) {
                    html += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="fetchBukusPenerbitData(${i}); return false;">
                    ${i}
                </a>
            </li>
        `;
                }

                // Next button
                html += `
        <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="fetchBukusPenerbitData(${pagination.current_page + 1}); return false;">
                Next
            </a>
        </li>
    `;

                html += '</ul></nav>';
                container.innerHTML = html;
            }
        </script>
    @endforeach
@endsection

