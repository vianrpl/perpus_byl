@extends('layouts.app')

@section('content')
    <div class="table table-responsive">
        <h2>Daftar Peminjaman</h2>

        {{-- Form Search --}}
        <form action="{{ route('peminjaman.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Cari Data Peminjam "
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-dark">Reset</a>
                @endif

                <button type="button" id="openPinjamBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pinjamModal">
                    <i class="fas fa-plus"></i> Pinjam Buku
                </button>
            </div>
        </form>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-modern table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr class="text-center">
                <th>ID</th>
                <th>User</th>
                <th>No_Transaksi</th>
                <th>Buku</th>
                <th>Batas Pengembalian</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($peminjaman as $p)
                <tr class="text-center">
                    <td>{{ ($peminjaman->currentPage() - 1) * $peminjaman->perPage() + $loop->iteration }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ $p->no_transaksi }}</td>
                    <td>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#bukuModal{{ $p->id_peminjaman }}">
                            Lihat Buku
                        </button>
                    </td>
                    <td class="{{ \Carbon\Carbon::parse($p->pengembalian)->isPast() ? 'text-danger' : 'text-success' }}">
                        {{ $p->pengembalian }}
                    </td>
                    <td>
                        @if($p->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($p->status === 'dipinjam')
                            <span class="badge bg-primary">Dipinjam</span>
                        @elseif($p->status === 'kembali')
                            <div class="text-center">
                                <span class="badge bg-success">Dikembalikan</span><br>
                                @if(!empty($p->kondisi_buku_saat_kembali))
                                    @php
                                        $ikon = [
                                            'baik' => '<i class="fas fa-book text-success"></i>',
                                            'rusak' => '<i class="fas fa-exclamation-triangle text-warning"></i>',
                                            'hilang' => '<i class="fas fa-times-circle text-danger"></i>',
                                        ][$p->kondisi_buku_saat_kembali] ?? '';
                                    @endphp
                                    <small class="text-muted fst-italic" style="font-size: 0.8em;">
                                        {!! $ikon !!} {{ ucfirst($p->kondisi_buku_saat_kembali) }}
                                    </small>
                                @endif
                            </div>
                        @elseif($p->status === 'ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                        @elseif($p->status === 'diperpanjang')
                            <span class="badge bg-info">Diperpanjang</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id_peminjaman }}">
                            <i class="fas fa-eye"></i> Lihat
                        </button>

                        @if($p->status === 'kembali')
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $p->id_peminjaman }}">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Belum ada peminjaman.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $peminjaman->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- =====================================================
    GANTI BAGIAN MODAL BUKU di resources/views/peminjaman/index.blade.php
    Cari @foreach($peminjaman as $p) yang pertama untuk modal bukuModal
    GANTI SELURUH MODAL BUKU DENGAN KODE INI
===================================================== --}}

    @foreach($peminjaman as $p)
        <div class="modal fade" id="bukuModal{{ $p->id_peminjaman }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Buku Dipinjam #{{ $p->no_transaksi }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bulkForm{{ $p->id_peminjaman }}">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th width="50">Pilih</th>
                                    <th>Judul</th>
                                    <th>Barcode</th>
                                    <th>Status</th>
                                    <th>Perpanjang?</th>
                                    <th>Batas Kembali</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($p->loan_items as $item)
                                    @php
                                        // Tentukan class row berdasarkan status DISPLAY
                                        $rowClass = '';
                                        if ($item->display_status === 'dikembalikan') {
                                            $rowClass = 'table-success';
                                        } elseif ($item->display_status === 'hilang') {
                                            $rowClass = 'table-danger';
                                        }

                                        // Cek apakah item bisa dipilih
                                        $canSelect = $item->display_status === 'dipinjam';

                                        // Gunakan tanggal dari SNAPSHOT (loan_due_date)
                                        $displayDate = $item->loan_due_date
                                            ? \Carbon\Carbon::parse($item->loan_due_date)
                                            : null;

                                        // Cek telat hanya untuk yang masih dipinjam
                                        $isPast = $displayDate && $displayDate->isPast() && $item->display_status === 'dipinjam';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td>
                                            @if($canSelect)
                                                <input type="checkbox" name="items[]" value="{{ $item->id_item }}">
                                            @endif
                                        </td>
                                        <td>{{ $item->bukus->judul }}</td>
                                        <td>{{ $item->barcode }}</td>
                                        <td>
                                            @if($item->display_status === 'dikembalikan')
                                                <span class="badge bg-success">✓ Dikembalikan</span>
                                            @elseif($item->display_status === 'hilang')
                                                <span class="badge bg-danger">✗ Hilang</span>
                                            @else
                                                <span class="badge bg-primary">Dipinjam</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Gunakan loan_extended_at dari SNAPSHOT --}}
                                            @if($item->display_status !== 'dipinjam')
                                                <span class="text-muted">-</span>
                                            @elseif($item->loan_extended_at)
                                                <span class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                                Sudah ({{ \Carbon\Carbon::parse($item->loan_extended_at)->format('d/m/Y') }})
                                            </span>
                                            @else
                                                <span class="text-warning">
                                                <i class="fas fa-clock"></i> Belum
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($displayDate)
                                                <span class="{{ $isPast ? 'text-danger fw-bold' : 'text-muted' }}">
                                                {{ $displayDate->format('d/m/Y') }}
                                                    @if($isPast)
                                                        <i class="fas fa-exclamation-triangle"></i> Telat!
                                                    @endif
                                            </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                    <div class="modal-footer">
                        @php
                            $canExtend = $p->loan_items
                                ->where('display_status', 'dipinjam')
                                ->filter(function($item) {
                                    return $item->loan_extended_at === null;
                                })->count() > 0;

                            $canReturn = $p->loan_items
                                ->where('display_status', 'dipinjam')
                                ->count() > 0;
                        @endphp

                        @if($canExtend)
                            <button class="btn btn-warning" onclick="bulkAction('perpanjang', '{{ $p->id_peminjaman }}')">
                                <i class="fas fa-calendar-plus"></i> Perpanjang Terpilih
                            </button>
                        @endif

                        @if($canReturn)
                            <button class="btn btn-success" onclick="bulkAction('kembalikan', '{{ $p->id_peminjaman }}')">
                                <i class="fas fa-check-circle"></i> Kembalikan Terpilih
                            </button>
                        @endif

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- MODAL DETAIL PEMINJAMAN --}}
    @foreach($peminjaman as $p)
        <div class="modal fade" id="detailModal{{ $p->id_peminjaman }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $p->id_peminjaman }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="detailModalLabel{{ $p->id_peminjaman }}">Detail Peminjaman #{{ $p->id_peminjaman }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><b>ID Buku :</b> {{ $p->id_buku }}</p>
                        <p><b>Judul Buku :</b> {{ $p->bukus->judul ?? '-' }}</p>
                        <p><b>User :</b> {{ $p->user->name }}</p>
                        <p><b>Nama Peminjam :</b> {{ $p->nama_peminjam }}</p>
                        <p><b>Alamat :</b> {{ $p->alamat }}</p>
                        <p><b>Tanggal Pinjam :</b> {{ $p->pinjam }}</p>
                        <p><b>Batas Pengembalian :</b> {{ $p->pengembalian }}</p>
                        <p><b>Status :</b> {{ ucfirst($p->status) ?? '-' }}</p>
                        <p><b>Kondisi Buku Saat Pinjam :</b> {{ $p->kondisi ?? '-' }}</p>
                        <p><b>Kondisi Buku Saat Kembali :</b> {{ $p->kondisi_buku_saat_kembali ?? '-' }}</p>
                        <p><b>Request Status :</b> {{ ucfirst($p->request_status) ?? '-' }}</p>
                        <p><b>Waktu Disetujui :</b> {{ $p->approved_at ?? '-' }}</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        @if(in_array(Auth::user()->role, ['admin', 'petugas']))
                            <a href="{{ route('admin.member.profile', $p->id_member) }}" class="btn btn-info text-white">
                                <i class="fas fa-id-card"></i> Data Diri
                            </a>
                        @endif
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- MODAL HAPUS --}}
    {{-- MODAL KONFIRMASI HAPUS --}}
    @foreach($peminjaman as $p)
        <div class="modal fade" id="hapusModal{{ $p->id_peminjaman }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus Peminjaman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus peminjaman <strong>#{{ $p->no_transaksi }}</strong> atas nama <strong>{{ $p->nama_peminjam }}</strong>?</p>
                        <p class="text-danger">Data akan hilang permanen!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>

                        {{-- FORM DELETE YANG BENAR --}}
                        <form action="{{ route('peminjaman.destroy', $p->id_peminjaman) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Ya, Hapus!</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- MODAL PINJAM BUKU (MODERN) --}}
    <div class="modal fade modal-pinjam fade-in-up" id="pinjamModal" tabindex="-1" aria-labelledby="pinjamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-book-open me-2 icon-buku"></i>
                        Pilih Buku untuk Dipinjam
                    </h5>
                    <span class="badge bg-light text-dark ms-2">Maks 2 Buku</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="search-buku-form" class="mb-4">
                        <div class="input-group search-modern">
                            <span class="input-group-text bg-transparent border-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="search-buku-input" class="form-control border-0 bg-transparent" placeholder="Cari judul buku...">
                            <button type="submit" class="btn btn-dark rounded-end-pill px-4">Cari</button>
                            <button type="button" id="reset-buku-search" class="btn btn-outline-secondary rounded-pill ms-2 px-3">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </form>

                    <div id="buku-table-container" class="table-modern mb-4"></div>

                    <div id="selectedCard" class="selected-card fade-in-up" style="display: none;">
                        <h6 class="mb-3">
                            <i class="fas fa-check-circle me-2 icon-check"></i>
                            Buku Terpilih (<span id="selectedCount">0</span>/2)
                        </h6>
                        <ul id="selectedBuku" class="list-unstyled"></ul>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Pilih 1 atau 2 buku. Klik "Eksemplar" untuk pilih item.
                    </small>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="button" id="lanjutForm" class="btn-lanjut rounded-pill px-4" disabled>
                        <i class="fas fa-arrow-right me-2"></i> Lanjut
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SUB-MODAL EKSAMPLAR --}}
    <div class="modal fade modal-pinjam" id="eksemplarModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-barcode me-2 icon-eksemplar"></i>
                        <span id="eksemplarModalLabel">Pilih Eksemplar</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="input-group search-modern mb-3">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fas fa-barcode text-muted"></i>
                        </span>
                        <input type="text" id="searchEksemplar" class="form-control border-0 bg-transparent" placeholder="Cari barcode...">
                    </div>
                    <div id="eksemplar-table-container" class="mt-3"></div>
                    <div id="eksemplarPagination" class="d-flex justify-content-center mt-3"></div>
                </div>
            </div>
        </div>
    </div>



        <div class="modal fade modal-pinjam" id="newPinjamDetailModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form id="pinjamForm" method="POST" action="{{ route('peminjaman.storeRequest') }}" class="form-modern">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>
                                Detail Peminjaman
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div id="idItemContainer"></div>
                            <div class="alert alert-info rounded-3 mb-4">
                                <h6><i class="fas fa-book me-2"></i>Buku Terpilih:</h6>
                                <ul id="bukuDetailList" class="mb-0"></ul>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">User</label>
                                <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold" for="selectedMemberDisplay">Pilih Member</label>  <!-- TAMBAH for -->
                                <div class="input-group">
                                    <input type="text" id="selectedMemberDisplay" class="form-control" placeholder="Klik untuk pilih member..." readonly>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                                <input type="hidden" name="id_member" id="selectedMemberId" value="">
                                <input type="hidden" name="id_user" value="{{ auth()->id() }}"> <!-- Petugas -->
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold" for="namaPeminjam">Nama Peminjam</label>  <!-- TAMBAH for -->
                                <input type="text" name="nama_peminjam" id="namaPeminjam" class="form-control" required readonly>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold" for="alamatPeminjam">Alamat</label>  <!-- TAMBAH for -->
                                <input type="text" name="alamat" id="alamatPeminjam" class="form-control" required placeholder="Masukkan alamat peminjam" readonly>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold" for="pengembalian">Tanggal Pengembalian</label>  <!-- TAMBAH for -->
                                <input type="date" name="pengembalian" id="pengembalian" class="form-control" required
                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                       max="{{ now()->addDays(7)->format('Y-m-d') }}">
                                <small class="text-muted">Maksimal 7 hari dari sekarang</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Pinjam Buku</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <!-- MODAL PILIH MEMBER (SEPERTI PILIH BUKU) -->
    <div class="modal fade modal-pinjam" id="memberModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i> Pilih Member
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Search Member -->
                    <form id="search-member-form" class="mb-4">
                        <div class="input-group search-modern">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                            <input type="text" id="search-member-input" class="form-control border-0 bg-transparent" placeholder="Cari nama / email member...">
                            <button type="submit" class="btn btn-dark rounded-end-pill px-4">Cari</button>
                            <button type="button" id="reset-member-search" class="btn btn-outline-secondary rounded-pill ms-2 px-3">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Table Member -->
                    <div id="member-table-container" class="table-modern mb-4"></div>

                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal Peringatan Max Buku -->
    <div class="modal fade" id="maxBukuModal" tabindex="-1" aria-labelledby="maxBukuLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="maxBukuLabel">Peringatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Anda sudah mencapai batas maksimal 2 buku. Hapus salah satu jika ingin ganti.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KONFirmasi PERPANJANG --}}
    <div class="modal fade" id="konfirmPerpanjangModal" tabindex="-1" aria-labelledby="konfirmPerpanjangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="konfirmPerpanjangLabel">Konfirmasi Perpanjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="perpanjangForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Pilih jumlah hari perpanjangan (maks 7 hari):</p>
                        <select name="hari" class="form-control" required>
                            <option value="">Pilih hari</option>
                            @for($i=1; $i<=7; $i++)
                                <option value="{{ $i }}">{{ $i }} hari</option>
                            @endfor
                        </select>
                        <div id="selectedItemsPerpanjang" class="mt-3 small text-muted"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Perpanjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL KONFirmasi KEMBALIKAN --}}
    <div class="modal fade" id="konfirmKembalikanModal" tabindex="-1" aria-labelledby="konfirmKembalikanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="konfirmKembalikanLabel">Konfirmasi Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="kembalikanForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Pilih kondisi buku saat dikembalikan:</p>
                        <select name="kondisi" class="form-control" required>
                            <option value="">Pilih kondisi</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                            <option value="hilang">Hilang</option>
                        </select>
                        <div id="selectedItemsKembalikan" class="mt-3 small text-muted"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Kembalikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pinjamModal = document.getElementById('pinjamModal');
            const eksemplarModal = document.getElementById('eksemplarModal');
            const memberModal = document.getElementById('memberModal');
            const memberTableContainer = document.getElementById('member-table-container');
            const searchMemberForm = document.getElementById('search-member-form');
            const searchMemberInput = document.getElementById('search-member-input');
            const resetMemberSearch = document.getElementById('reset-member-search');

            let selectedItems = [];
            let currentIdBuku = '';
            let maxSelected = 2;

            // ========== PINJAM MODAL ==========
            if (pinjamModal) {
                pinjamModal.addEventListener('show.bs.modal', () => {
                    selectedItems = [];
                    updateSelectedList();
                    loadBukuTable();
                    setTimeout(attachLanjutEvent, 100);
                });
            }

            function attachLanjutEvent() {
                const lanjutBtn = document.getElementById('lanjutForm');
                if (lanjutBtn) {
                    lanjutBtn.onclick = null;
                    lanjutBtn.addEventListener('click', lanjutHandler);
                }
            }

            function lanjutHandler() {
                if (selectedItems.length === 0) {
                    alert('Pilih minimal 1 buku!');
                    return;
                }

                const container = document.getElementById('idItemContainer');
                if (container) {
                    container.innerHTML = '';
                    selectedItems.forEach(item => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'id_item[]';
                        input.value = item.id_item;
                        container.appendChild(input);
                    });
                }

                const bukuList = document.getElementById('bukuDetailList');
                if (bukuList) {
                    bukuList.innerHTML = selectedItems.map(item =>
                        `<li><strong>${item.barcode}</strong> <small class="text-muted">(${item.kondisi})</small></li>`
                    ).join('');
                }

                const pinjamEl = document.getElementById('pinjamModal');
                const detailEl = document.getElementById('newPinjamDetailModal');

                if (!pinjamEl || !detailEl) {
                    console.error('Modal tidak ditemukan:', { pinjamEl, detailEl });
                    return;
                }

                const bsPinjam = bootstrap.Modal.getInstance(pinjamEl) || new bootstrap.Modal(pinjamEl);
                bsPinjam.hide();

                pinjamEl.addEventListener('hidden.bs.modal', function openDetail() {
                    pinjamEl.removeEventListener('hidden.bs.modal', openDetail);
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

                    const modal = new bootstrap.Modal(detailEl, {
                        backdrop: 'static',
                        keyboard: false,
                        focus: false
                    });
                    modal.show();

                    detailEl.addEventListener('shown.bs.modal', () => {
                        const input = detailEl.querySelector('#selectedMemberDisplay');
                        if (input) input.focus();
                    }, { once: true });
                });
            }

            // ========== BUKU ==========
            function attachPilihEksemplarButtons() {
                document.querySelectorAll('.pilih-eksemplar').forEach(btn => {
                    btn.onclick = function () {
                        currentIdBuku = this.dataset.id;
                        const judul = this.dataset.judul;
                        document.getElementById('eksemplarModalLabel').textContent = `Pilih Eksemplar: ${judul}`;
                        loadEksemplar(currentIdBuku);
                        new bootstrap.Modal(eksemplarModal).show();
                    };
                });
            }

            function loadBukuTable(search = '', page = 1) {
                fetch(`/peminjaman/bukus?search=${encodeURIComponent(search)}&page=${page}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('buku-table-container').innerHTML = html;
                        attachPilihEksemplarButtons();
                        attachBukuPagination();
                    });
            }

            function attachBukuPagination() {
                document.querySelectorAll('#buku-table-container .pagination a').forEach(link => {
                    link.onclick = e => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const search = url.searchParams.get('search') || '';
                        const page = url.searchParams.get('page') || 1;
                        loadBukuTable(search, page);
                    };
                });
            }

            // ========== EKSEMPLAR ==========
            function loadEksemplar(id_buku, search = '', page = 1) {
                currentIdBuku = id_buku;
                fetch(`/get-eksemplar-by-buku/${id_buku}?query=${encodeURIComponent(search)}&page=${page}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = `<div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Barcode</th><th>Kondisi</th><th>Aksi</th></tr></thead><tbody>`;
                        if (data.data.length === 0) {
                            html += `<tr><td colspan="3" class="text-center text-muted">Tidak ada eksemplar</td></tr>`;
                        } else {
                            data.data.forEach(item => {
                                const isSelected = selectedItems.some(s => s.id_item === item.id_item);
                                html += `<tr class="${isSelected ? 'table-secondary' : ''}">
                        <td>${item.barcode}</td>
                        <td><span class="badge bg-success">${item.kondisi}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary pilih-item"
                                    data-id="${item.id_item}"
                                    data-barcode="${item.barcode}"
                                    data-kondisi="${item.kondisi}"
                                    ${isSelected ? 'disabled' : ''}>Pilih</button>
                        </td>
                    </tr>`;
                            });
                        }
                        html += `</tbody></table></div>`;
                        html += data.links || '';
                        document.getElementById('eksemplar-table-container').innerHTML = html;
                        attachEksemplarButtons();
                        attachEksemplarPagination(search);
                    });
            }

            function attachEksemplarPagination(currentSearch) {
                document.querySelectorAll('#eksemplar-table-container .pagination a').forEach(link => {
                    link.onclick = e => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        loadEksemplar(currentIdBuku, currentSearch, page);
                    };
                });
            }

            function attachEksemplarButtons() {
                document.querySelectorAll('.pilih-item').forEach(btn => {
                    btn.onclick = function () {
                        const id = parseInt(this.dataset.id);
                        if (selectedItems.some(s => s.id_item === id)) return;
                        if (selectedItems.length >= maxSelected) {
                            new bootstrap.Modal(document.getElementById('maxBukuModal')).show();
                            return;
                        }
                        selectedItems.push({
                            id_item: id,
                            barcode: this.dataset.barcode,
                            kondisi: this.dataset.kondisi
                        });
                        updateSelectedList();
                        loadEksemplar(currentIdBuku, document.getElementById('searchEksemplar')?.value || '');
                        eksemplarModal.querySelector('.btn-close')?.click();
                    };
                });
            }

            function updateSelectedList() {
                const list = document.getElementById('selectedBuku');
                const count = document.getElementById('selectedCount');
                const card = document.getElementById('selectedCard');
                const lanjutBtn = document.getElementById('lanjutForm');

                if (list) {
                    list.innerHTML = selectedItems.map((item, i) => `
                <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 bg-light">
                    <div><strong>${item.barcode}</strong><small class="text-muted d-block">Kondisi: ${item.kondisi}</small></div>
                    <button class="btn btn-sm btn-danger" onclick="removeItem(${i})"><i class="fas fa-times"></i></button>
                </div>
            `).join('');
                }
                if (count) count.textContent = selectedItems.length;
                if (card) card.style.display = selectedItems.length ? 'block' : 'none';
                if (lanjutBtn) lanjutBtn.disabled = selectedItems.length === 0;
            }

            // ========== BULK ACTION: Perpanjang & Kembalikan ==========
            function selectAll(checkbox, peminjamanId) {
                const form = document.getElementById(`bulkForm${peminjamanId}`);
                const checkboxes = form.querySelectorAll('input[name="items[]"]');
                checkboxes.forEach(cb => cb.checked = checkbox.checked);
            }

            window.bulkAction = function(action, peminjamanId) {
                const form = document.getElementById(`bulkForm${peminjamanId}`);
                const checked = Array.from(form.querySelectorAll('input[name="items[]"]:checked'))
                    .map(cb => ({
                        id: cb.value,
                        barcode: cb.closest('tr').querySelector('td:nth-child(3)')?.textContent.trim() || 'Unknown'
                    }));

                if (checked.length === 0) {
                    alert('Pilih minimal 1 buku!');
                    return;
                }

                let modalId, formId, route;
                if (action === 'perpanjang') {
                    modalId = 'konfirmPerpanjangModal';
                    formId = 'perpanjangForm';  // BENAR
                    route = `/peminjaman/${peminjamanId}/perpanjang`;
                } else if (action === 'kembalikan') {
                    modalId = 'konfirmKembalikanModal';
                    formId = 'kembalikanForm';  // BENAR
                    route = `/peminjaman/${peminjamanId}/kembalikan`;
                }

                // Isi daftar item yang dipilih
                const listEl = document.getElementById(`selectedItems${action.charAt(0).toUpperCase() + action.slice(1)}`);
                if (listEl) {
                    listEl.innerHTML = '<strong>Buku dipilih:</strong><ul class="mb-0 ps-3">' +
                        checked.map(item => `<li>${item.barcode}</li>`).join('') + '</ul>';
                }

                // Setup form
                const targetForm = document.getElementById(formId);
                if (!targetForm) {
                    console.error('Form tidak ditemukan:', formId);
                    return;
                }
                targetForm.action = route;

                // Hapus input lama
                targetForm.querySelectorAll('input[name="items[]"]').forEach(el => el.remove());

                // Tambah input hidden untuk item
                checked.forEach(item => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'items[]';
                    input.value = item.id;
                    targetForm.appendChild(input);
                });

                // Buka modal
                new bootstrap.Modal(document.getElementById(modalId)).show();
            };

            window.removeItem = function (index) {
                selectedItems.splice(index, 1);
                updateSelectedList();
            };

            document.getElementById('search-buku-form')?.addEventListener('submit', e => {
                e.preventDefault();
                const query = document.getElementById('search-buku-input').value.trim();
                loadBukuTable(query);
            });

            document.getElementById('reset-buku-search')?.addEventListener('click', () => {
                document.getElementById('search-buku-input').value = '';
                loadBukuTable();
            });

            document.getElementById('searchEksemplar')?.addEventListener('input', function () {
                if (currentIdBuku) loadEksemplar(currentIdBuku, this.value.trim());
            });

            // ========== MEMBER (SEMUA DI DALAM DOMContentLoaded) ==========
            function attachMemberButtons() {
                document.querySelectorAll('.pilih-member').forEach(btn => {
                    btn.onclick = async function () {
                        const id = this.dataset.id;
                        const nama = this.dataset.nama;
                        try {
                            const res = await fetch(`/peminjaman/active/${id}`);
                            const data = await res.json();
                            const active = data.active || 0;
                            maxSelected = 2 - active;

                            if (maxSelected <= 0) {
                                new bootstrap.Modal(document.getElementById('maxBukuModal')).show();
                                return;
                            }

                            if (selectedItems.length > maxSelected) {
                                selectedItems = selectedItems.slice(0, maxSelected);
                                updateSelectedList();
                                new bootstrap.Modal(document.getElementById('maxBukuModal')).show();
                            }

                            document.getElementById('selectedMemberId').value = id;
                            document.getElementById('selectedMemberDisplay').value = nama;
                            document.getElementById('namaPeminjam').value = nama;
                            document.getElementById('alamatPeminjam').value = '';
                            document.getElementById('alamatPeminjam').removeAttribute('readonly');

                            bootstrap.Modal.getInstance(document.getElementById('memberModal')).hide();
                            new bootstrap.Modal(document.getElementById('newPinjamDetailModal')).show();
                        } catch (err) {
                            alert('Gagal cek kuota.');
                        }
                    };
                });
            }

            if (memberModal) {
                memberModal.addEventListener('show.bs.modal', () => {
                    loadMemberTable();
                });
            }

            function loadMemberTable(search = '', page = 1) {
                const url = `/peminjaman/members?search=${encodeURIComponent(search)}&page=${page}`;

                memberTableContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.text())
                    .then(html => {
                        memberTableContainer.innerHTML = html;
                        attachMemberButtons();  // SEKARANG AMAN!
                        attachMemberPagination();
                    })
                    .catch(err => {
                        memberTableContainer.innerHTML = '<p class="text-danger text-center">Gagal memuat member.</p>';
                        console.error(err);
                    });
            }

            function attachMemberPagination() {
                document.querySelectorAll('#member-table-container .pagination a').forEach(link => {
                    link.onclick = e => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const search = url.searchParams.get('search') || '';
                        const page = url.searchParams.get('page') || 1;
                        loadMemberTable(search, page);
                    };
                });
            }

            if (searchMemberForm) {
                searchMemberForm.addEventListener('submit', e => {
                    e.preventDefault();
                    loadMemberTable(searchMemberInput.value.trim());
                });
            }

            if (resetMemberSearch) {
                resetMemberSearch.addEventListener('click', () => {
                    searchMemberInput.value = '';
                    loadMemberTable();
                });
            }

            // ========== SUBMIT FORM ==========
            document.getElementById('pinjamForm')?.addEventListener('submit', async function (e) {
                e.preventDefault();
                const memberId = document.getElementById('selectedMemberId')?.value;
                if (!memberId) {
                    alert('Pilih member dulu!');
                    new bootstrap.Modal(document.getElementById('memberModal')).show();
                    return;
                }
                try {
                    const res = await fetch(`/peminjaman/active/${memberId}`);
                    const data = await res.json();
                    if (selectedItems.length > (2 - (data.active || 0))) {
                        new bootstrap.Modal(document.getElementById('maxBukuModal')).show();
                        return;
                    }
                    this.submit();
                } catch (err) {
                    alert('Gagal cek kuota.');
                }
            });
        });
    </script>
@endpush

