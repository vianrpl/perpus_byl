@extends('layouts.app')

@section('content')
    <!-- Header dengan judul dan tombol tambah -->
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h2>Daftar Penataan Buku</h2>

        <!-- Tombol tambah hanya untuk non-konsumen -->
        <div class="mb-3">
            @unless(Auth::user()->role === 'konsumen')
                <button class="btn btn-primary shadow-sm px-3 py-2 fw-bold hover-scale"
                        data-bs-toggle="modal" data-bs-target="#modalTambahPenataan">
                    + Tambah
                </button>
            @endunless
        </div>
    </div>

    <!-- Form pencarian -->
    <form action="{{ route('penataan_bukus.index') }}" method="GET" class="mb-3">
        <div class="input-group" style="max-width:400px">
            <!-- Input untuk mencari berdasarkan nama buku atau rak -->
            <input type="text" name="search" class="form-control"
                   placeholder="Cari Penataan (nama buku, nama rak)"
                   value="{{ request('search') }}">
            <button class="btn btn-dark" type="submit">Cari</button>
            <!-- Tombol reset jika ada query pencarian -->
            @if(request('search'))
                <a href="{{ route('penataan_bukus.index') }}" class="btn btn-dark">Reset</a>
            @endif
            {{-- 🧮 Tombol Filter --}}
            <button type="button" class="btn btn-outline-secondary d-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel me-2"></i> Filter
            </button>
        </div>
    </form>

    <!-- Pesan sukses -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- Pesan error validasi -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabel daftar penataan -->
    <div class="table-responsive">
        <table class="table table-modern table-bordered table-striped">
            <thead class="table-dark">
            <tr class="text-center">
                <th>No</th>
                <th>Nama Buku</th>
                <th>Nama Rak</th>
                <th>Kolom</th>
                <th>Baris</th>
                <th>Jumlah (Max/Tata)</th>
                <th>Petugas</th>
                <th>Sumber</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <!-- Looping data penataan -->
            @forelse($penataanBukus as $penataan)
                <tr class="text-center">
                    <!-- Nomor urut berdasarkan pagination -->
                    <td>{{ ($penataanBukus->currentPage() - 1) * $penataanBukus->perPage() + $loop->iteration }}</td>
                    <!-- Nama buku dari relasi -->
                    <td>{{ $penataan->bukus->judul ?? 'Buku tidak ditemukan' }}</td>
                    <!-- Nama rak dari relasi -->
                    <td>{{ $penataan->raks->nama ?? 'Rak tidak ditemukan' }}</td>
                    <td>{{ $penataan->kolom }}</td>
                    <td>{{ $penataan->baris }}</td>
                    <td>
                        @if($penataan->bukus)
                            {{ $penataan->bukus->jumlah }}/{{ $penataan->bukus->jumlah_tata }}
                        @else
                            0/0
                        @endif
                    </td>
                    <td>{{ $penataan->user->name }}</td>  <!-- Langsung dari field name (string) -->
                    <!-- Pakai accessor dari model bukus -->
                    <td>{{ $penataan->sumber }}</td>
                    <td>
                        <!-- Tombol lihat -->
                        <button type="button" class="btn btn-info btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalShowPenataan{{ $penataan->id_penataan }}">
                            Lihat
                        </button>
                        <!-- Tombol edit dan hapus untuk non-konsumen -->
                        @unless(Auth::user()->role === 'konsumen')
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditPenataan{{ $penataan->id_penataan }}">Edit</button>
                            <!-- <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalHapus{{ $penataan->id_penataan }}">Hapus</button> -->
                        @endunless
                    </td>
                </tr>

                <!-- Modal Lihat -->
                <div class="modal fade" id="modalShowPenataan{{ $penataan->id_penataan }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Detail Penataan Buku</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>ID Penataan:</strong> {{ $penataan->id_penataan }}</p>
                                <p><strong>Nama Buku:</strong> {{ $penataan->bukus->judul ?? '-' }}</p>
                                <p><strong>Nama Rak:</strong> {{ $penataan->raks->nama ?? '-' }}</p>
                                <p><strong>Kolom:</strong> {{ $penataan->kolom }}</p>
                                <p><strong>Baris:</strong> {{ $penataan->baris }}</p>
                                <p><strong>Jumlah:</strong> {{ $penataan->jumlah }}</p>
                                <p><strong>Petugas:</strong> {{ $penataan->user->name }}</p>  <!-- Langsung dari field name -->
                                <p><strong>Sumber:</strong>{{ $penataan->sumber }}</p>
                                <p><strong>Tanggal Dibuat:</strong> {{ $penataan->insert_date->format('d-m-Y H:i') }}</p>
                                <p><strong>Tanggal Diperbarui:</strong> {{ $penataan->modified_date->format('d-m-Y H:i') }}</p>
                            </div>
                            <div class="modal-footer">
                                {{-- tombol baru --}}
                                @if($penataan->raks)
                                    <a href="{{ url('/raks/' . $penataan->raks->id_rak . '?kategori=' . $penataan->bukus->id_kategori) }}" class="btn btn-primary">
                                        Lihat Rak
                                    </a>
                                @endif


                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- End Modal Lihat -->
            @empty
                <tr>
                    <td colspan="10" class="text-center">Belum ada data penataan buku.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $penataanBukus->links('pagination::bootstrap-5') }}
    </div>

    <!-- Modal Edit Penataan -->
    @foreach($penataanBukus as $penataan)
        <div class="modal fade" id="modalEditPenataan{{ $penataan->id_penataan }}" tabindex="-1 "
             aria-labelledby="modalEditPenataanLabel{{ $penataan->id_penataan }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditPenataanLabel{{ $penataan->id_penataan }}">Edit Penataan Buku</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <form action="{{ route('penataan_bukus.update', $penataan->id_penataan) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <!-- PERUBAHAN: Ganti dropdown buku jadi tombol pilih + input readonly -->
                            <div class="mb-3">
                                <label for="selected_judul_buku_edit_{{ $penataan->id_penataan }}">Buku</label>
                                <div class="input-group">
                                    <input type="text" id="selected_judul_buku_edit_{{ $penataan->id_penataan }}" class="form-control" readonly
                                           placeholder="-- Pilih Buku --" value="{{ old('judul', $penataan->bukus->judul ?? '') }}">
                                    <input type="hidden" name="id_buku" id="selected_id_buku_edit_{{ $penataan->id_penataan }}"
                                           value="{{ old('id_buku', $penataan->id_buku) }}">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPilihBuku"
                                            data-form-type="edit" data-penataan-id="{{ $penataan->id_penataan }}">
                                        Pilih Buku
                                    </button>
                                </div>
                                @error('id_buku')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Dropdown rak (tetap) -->
                            <div class="mb-3">
                                <label for="id_rak_{{ $penataan->id_penataan }}">Rak</label>
                                <select name="id_rak" id="id_rak_{{ $penataan->id_penataan }}" class="form-control" required>
                                    <option value="">-- Pilih Rak --</option>
                                    @foreach($raks as $rak)
                                        <option value="{{ $rak->id_rak }}"
                                            {{ old('id_rak', $penataan->id_rak) == $rak->id_rak ? 'selected' : '' }}>
                                            {{ $rak->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_rak')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Petugas: Readonly auto dari login + hidden input -->
                            <div class="mb-3">
                                <label for="name_{{ $penataan->id_penataan }}">Petugas</label>
                                <input type="text" class="form-control" id="name_{{ $penataan->id_penataan }}" value="{{ Auth::user()->name }}" readonly>
                                <input type="hidden" name="name" value="{{ Auth::user()->name }}">  <!-- Kirim auto ke controller -->
                            </div>
                            <!-- Input kolom -->
                            <div class="mb-3">
                                <label for="kolom_{{ $penataan->id_penataan }}">Kolom</label>
                                <input type="number" name="kolom" id="kolom_{{ $penataan->id_penataan }}"
                                       class="form-control" value="{{ old('kolom', $penataan->kolom) }}" min="1" required>
                                @error('kolom')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Input baris -->
                            <div class="mb-3">
                                <label for="baris_{{ $penataan->id_penataan }}">Baris</label>
                                <input type="number" name="baris" id="baris_{{ $penataan->id_penataan }}"
                                       class="form-control" value="{{ old('baris', $penataan->baris) }}" min="1" required>
                                @error('baris')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Input jumlah -->
                            <div class="mb-3">
                                <label for="jumlah_{{ $penataan->id_penataan }}">Jumlah</label>
                                <input type="number" name="jumlah" id="jumlah_{{ $penataan->id_penataan }}"
                                       class="form-control" value="{{ old('jumlah', $penataan->jumlah) }}" min="1" required>
                                @error('jumlah')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="sumber_{{ $penataan->id_penataan }}">Sumber</label>
                                <input type="text" name="sumber" id="sumber_{{ $penataan->id_penataan }}"
                                       class="form-control" value="{{ old('sumber', $penataan->sumber) }}" required>
                                @error('sumber')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
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
    <!-- End Modal Edit Penataan -->

    <!-- Modal Hapus Penataan -->
    @foreach($penataanBukus as $penataan)
        <div class="modal fade" id="modalHapus{{ $penataan->id_penataan }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus Penataan <b>{{ $penataan->id_penataan }}</b>?
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('penataan_bukus.destroy', $penataan->id_penataan) }}" method="POST">
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
    <!-- End Modal Hapus Penataan -->

    <!-- Modal Tambah Penataan -->
    <div class="modal fade" id="modalTambahPenataan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Penataan Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('penataan_bukus.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- PERUBAHAN: Ganti dropdown buku jadi tombol pilih + input readonly -->
                        <div class="mb-3">
                            <label for="selected_judul_buku_tambah">Buku</label>
                            <div class="input-group">
                                <input type="text" id="selected_judul_buku_tambah" class="form-control" readonly
                                       placeholder="-- Pilih Buku --">
                                <input type="hidden" name="id_buku" id="selected_id_buku_tambah">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPilihBuku"
                                        data-form-type="tambah">
                                    Pilih Buku
                                </button>
                            </div>
                            @error('id_buku')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_rak_tambah">Rak</label>
                            <select name="id_rak" id="id_rak_tambah" class="form-control" required>
                                <option value="">-- Pilih Rak --</option>
                            </select>
                            @error('id_rak')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Petugas: Readonly auto dari login + hidden input -->
                        <div class="mb-3">
                            <label for="name_tambah">Petugas</label>
                            <input type="text" class="form-control" id="name_tambah" value="{{ Auth::user()->name }}" readonly>
                            <input type="hidden" name="name" value="{{ Auth::user()->name }}">  <!-- Kirim auto ke controller -->
                        </div>
                        <!-- Input kolom -->
                        <div class="mb-3">
                            <label for="kolom_tambah">Kolom</label>
                            <input type="number" name="kolom" id="kolom_tambah" class="form-control" min="1" required>
                            @error('kolom')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Input baris -->
                        <div class="mb-3">
                            <label for="baris_tambah">Baris</label>
                            <input type="number" name="baris" id="baris_tambah" class="form-control" min="1" required>
                            @error('baris')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Input jumlah -->
                        <div class="mb-3">
                            <label for="jumlah_tambah">Jumlah</label>
                            <input type="number" name="jumlah" id="jumlah_tambah" class="form-control" min="1" required>
                            @error('jumlah')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="sumber_tambah">Sumber</label>
                            <input type="text" name="sumber" id="sumber_tambah" class="form-control" required>
                            @error('sumber')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
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
    <!-- End Modal Tambah Penataan -->

    <!-- BARU: Modal Pilih Buku (dengan search, table, pagination via AJAX) -->
    <div class="modal fade" id="modalPilihBuku" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Pilih Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Form search dengan tombol Reset -->
                    <form id="search-buku-form" class="mb-3">
                        <div class="input-group" style="max-width:400px">
                            <input type="text" id="search-buku-input" class="form-control" placeholder="Cari judul buku...">
                            <button type="submit" class="btn btn-dark">Cari</button>
                            <button type="button" id="reset-buku-search" class="btn btn-dark">Reset</button>
                        </div>
                    </form>

                    <!-- Container untuk table (loaded via AJAX) -->
                    <div id="buku-table-container"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal Pilih Buku -->

    <!-- 🧮 Modal Filter -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="filterModalLabel">Filter Data Penataan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('penataan_bukus.index') }}" method="GET">
                    <div class="modal-body">
                        {{-- Petugas --}}
                        <!-- ===== GANTI BLOK PETUGAS DENGAN YANG INI ===== -->
                        <div class="mb-3">
                            <label for="filter_petugas" class="form-label">Petugas</label>
                            <!-- Perbaikan: ambil user yang berrole admin atau petugas -->
                            <select name="filter_petugas" id="filter_petugas" class="form-select">
                                <option value="">-- Semua Petugas --</option>
                                @foreach(\App\Models\User::whereIn('role', ['admin','petugas'])->get() as $user)
                                    <option value="{{ $user->id }}" {{ request('filter_petugas') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <!-- ===== TAMBAHKAN BLOK STATUS ===== -->
                        <div class="mb-3">
                            <label for="filter_status" class="form-label">Status Penataan</label>
                            <select name="filter_status" id="filter_status" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <!-- penuh = jumlah_tata >= jumlah (sudah tertata penuh) -->
                                <option value="penuh" {{ request('filter_status') == 'penuh' ? 'selected' : '' }}>Sudah Penuh (tertata)</option>
                                <!-- belum = jumlah_tata < jumlah (belum tertata penuh) -->
                                <option value="belum" {{ request('filter_status') == 'belum' ? 'selected' : '' }}>Belum Penuh (belum tertata)</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('penataan_bukus.index') }}" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Script JS (modifikasi existing + baru untuk modal pilih buku) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Existing JS untuk load rak berdasarkan buku
            function loadRak(idBuku, rakSelectId) {
                const rakSelect = document.getElementById(rakSelectId);
                rakSelect.innerHTML = '<option value="">-- Pilih Rak --</option>';

                if (idBuku) {
                    fetch(`/get-rak-by-buku/${idBuku}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                rakSelect.innerHTML = '<option value="">Tidak ada rak tersedia</option>';
                            } else {
                                data.forEach(rak => {
                                    rakSelect.innerHTML += `<option value="${rak.id_rak}">${rak.nama}</option>`;
                                });
                            }
                        });
                }
            }

            let currentFormType = '';
            let currentPenataanId = '';

            const modalPilihBuku = document.getElementById('modalPilihBuku');
            const modalTambahPenataan = new bootstrap.Modal(document.getElementById('modalTambahPenataan'), {
                backdrop: 'static',
                keyboard: false
            });
            const modalEditPenataanTemplate = document.querySelector('#modalEditPenataan'); // Ambil template modal edit

            // Event saat modal Pilih Buku dibuka
            modalPilihBuku.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                currentFormType = button.getAttribute('data-form-type');
                currentPenataanId = button.getAttribute('data-penataan-id') || '';
                loadBukuTable(); // Load table default
            });

            // Event saat modal Pilih Buku ditutup
            modalPilihBuku.addEventListener('hidden.bs.modal', function() {
                if (currentFormType === 'tambah' && !modalTambahPenataan._isShown) {
                    modalTambahPenataan.show(); // Buka kembali modal Tambah Penataan
                } else if (currentFormType === 'edit' && currentPenataanId) {
                    const modalEdit = document.querySelector(`#modalEditPenataan${currentPenataanId}`);
                    if (modalEdit && !bootstrap.Modal.getInstance(modalEdit)?._isShown) {
                        new bootstrap.Modal(modalEdit).show(); // Buka kembali modal Edit Penataan spesifik
                    }
                }
            });

            function loadBukuTable(search = '', page = 1) {
                const url = `/get-bukus-for-selection?search=${encodeURIComponent(search)}&page=${page}`;
                fetch(url)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('buku-table-container').innerHTML = html;
                        attachPilihButtonListeners();
                        attachPaginationListeners();
                    });
            }

            function attachPilihButtonListeners() {
                const pilihButtons = document.querySelectorAll('.pilih-buku');
                pilihButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const idBuku = this.dataset.id;
                        const judulBuku = this.dataset.judul;

                        if (currentFormType === 'tambah') {
                            document.getElementById('selected_id_buku_tambah').value = idBuku;
                            document.getElementById('selected_judul_buku_tambah').value = judulBuku;
                            loadRak(idBuku, 'id_rak_tambah');
                        } else if (currentFormType === 'edit' && currentPenataanId) {
                            document.getElementById(`selected_id_buku_edit_${currentPenataanId}`).value = idBuku;
                            document.getElementById(`selected_judul_buku_edit_${currentPenataanId}`).value = judulBuku;
                            loadRak(idBuku, `id_rak_${currentPenataanId}`);
                        }

                        const modal = bootstrap.Modal.getInstance(modalPilihBuku);
                        modal.hide();
                    });
                });
            }

            function attachPaginationListeners() {
                const paginationLinks = document.querySelectorAll('#buku-table-container .pagination a');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        const page = url.searchParams.get('page') || 1;
                        const search = document.getElementById('search-buku-input').value;
                        loadBukuTable(search, page);
                    });
                });
            }

            const searchForm = document.getElementById('search-buku-form');
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const search = document.getElementById('search-buku-input').value;
                loadBukuTable(search);
            });

            // Event untuk tombol Reset
            document.getElementById('reset-buku-search').addEventListener('click', function() {
                document.getElementById('search-buku-input').value = ''; // Kosongkan input
                loadBukuTable(); // Muat ulang tabel tanpa filter
            });
        });
    </script>
@endsection
