@extends('layouts.app')

@section('content')
    <div class="table table-responsive">
        <h2>Daftar Peminjaman</h2>

        {{-- Form Search - UPDATED: bisa search judul buku & barcode --}}
        <form action="{{ route('peminjaman.index') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:500px">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari nama, no transaksi, judul buku, atau barcode..."
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">Reset</a>
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

        {{-- TABLE UTAMA - UPDATED: Kolom diubah sesuai request --}}
        <table class="table table-modern table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr class="text-center">
                <th>ID</th>
                <th>No Transaksi</th>
                <th>Nama Peminjam</th> {{-- CHANGED: dari User jadi Nama Peminjam --}}
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
                    <td>{{ $p->no_transaksi }}</td>
                    <td>{{ $p->nama_peminjam }}</td> {{-- CHANGED: tampilkan nama_peminjam --}}
                    <td>
                        {{-- UPDATED: Tombol Lihat Buku warna berbeda (secondary/abu-abu) --}}
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#bukuModal{{ $p->id_peminjaman }}">
                            <i class="fas fa-book"></i> Lihat Buku
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
                        {{-- UPDATED: Tombol Aksi dipisah --}}
                        {{-- Tombol Lihat Detail (warna info/biru muda) --}}
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id_peminjaman }}">
                            <i class="fas fa-eye"></i> Lihat
                        </button>

                        {{-- UPDATED: Tombol Perpanjang dipindah ke sini (warna warning/kuning) --}}
                        @php
                            $canExtend = $p->loan_items
                                ->where('display_status', 'dipinjam')
                                ->filter(fn($item) => $item->loan_extended_at === null)->count() > 0;
                            $canReturn = $p->loan_items->where('display_status', 'dipinjam')->count() > 0;
                        @endphp

                        @if($canExtend)
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#perpanjangModal{{ $p->id_peminjaman }}">
                                <i class="fas fa-calendar-plus"></i> Perpanjang
                            </button>
                        @endif

                        {{-- UPDATED: Tombol Kembalikan dipindah ke sini (warna success/hijau) --}}
                        @if($canReturn)
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kembalikanModal{{ $p->id_peminjaman }}">
                                <i class="fas fa-undo"></i> Kembalikan
                            </button>
                        @endif

                        {{-- Tombol Hapus (warna danger/merah) --}}
                        {{-- }}@if($p->status === 'kembali')
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $p->id_peminjaman }}">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        @endif --}}
                        
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
        MODAL LIHAT BUKU - HANYA NAMPILIN BUKU (TANPA TOMBOL AKSI)
    ===================================================== --}}
    @foreach($peminjaman as $p)
        <div class="modal fade" id="bukuModal{{ $p->id_peminjaman }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title"><i class="fas fa-book me-2"></i>Buku Dipinjam #{{ $p->no_transaksi }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Barcode</th>
                                <th>Status</th>
                                <th>Perpanjang?</th>
                                <th>Batas Kembali</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($p->loan_items as $index => $item)
                                @php
                                    $rowClass = match($item->display_status) {
                                        'dikembalikan' => 'table-success',
                                        'hilang' => 'table-danger',
                                        default => ''
                                    };
                                    $displayDate = $item->loan_due_date ? \Carbon\Carbon::parse($item->loan_due_date) : null;
                                    $isPast = $displayDate && $displayDate->isPast() && $item->display_status === 'dipinjam';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->bukus->judul }}</td>
                                    <td><code>{{ $item->barcode }}</code></td>
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
                                        @if($item->display_status !== 'dipinjam')
                                            <span class="text-muted">-</span>
                                        @elseif($item->loan_extended_at)
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Sudah</span>
                                        @else
                                            <span class="text-warning"><i class="fas fa-clock"></i> Belum</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($displayDate)
                                            <span class="{{ $isPast ? 'text-danger fw-bold' : '' }}">
                                                {{ $displayDate->format('d/m/Y') }}
                                                @if($isPast) <i class="fas fa-exclamation-triangle"></i> @endif
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- =====================================================
        MODAL PERPANJANG - TERPISAH DARI MODAL BUKU
    ===================================================== --}}
    @foreach($peminjaman as $p)
        @php $canExtendItems = $p->loan_items->where('display_status', 'dipinjam')->filter(fn($i) => $i->loan_extended_at === null); @endphp
        @if($canExtendItems->count() > 0)
            <div class="modal fade" id="perpanjangModal{{ $p->id_peminjaman }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Perpanjang Buku #{{ $p->no_transaksi }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('peminjaman.perpanjang', $p->id_peminjaman) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="text-muted mb-3">Pilih buku yang ingin diperpanjang:</p>
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="50"><input type="checkbox" id="checkAllPerpanjang{{ $p->id_peminjaman }}" onclick="toggleAllCheckbox(this, 'perpanjang{{ $p->id_peminjaman }}')"></th>
                                        <th>Judul</th>
                                        <th>Barcode</th>
                                        <th>Batas Kembali Saat Ini</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($canExtendItems as $item)
                                        <tr>
                                            <td><input type="checkbox" name="items[]" value="{{ $item->id_item }}" class="perpanjang{{ $p->id_peminjaman }}"></td>
                                            <td>{{ $item->bukus->judul }}</td>
                                            <td><code>{{ $item->barcode }}</code></td>
                                            <td>{{ \Carbon\Carbon::parse($item->loan_due_date)->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tambah berapa hari? (Max 7 hari)</label>
                                    <select name="hari" class="form-select" required>
                                        <option value="">Pilih jumlah hari</option>
                                        @for($i = 1; $i <= 7; $i++)
                                            <option value="{{ $i }}">{{ $i }} hari</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning"><i class="fas fa-calendar-plus me-1"></i>Perpanjang</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- =====================================================
        MODAL KEMBALIKAN - TERPISAH DARI MODAL BUKU
    ===================================================== --}}
    @foreach($peminjaman as $p)
        @php $canReturnItems = $p->loan_items->where('display_status', 'dipinjam'); @endphp
        @if($canReturnItems->count() > 0)
            <div class="modal fade" id="kembalikanModal{{ $p->id_peminjaman }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Kembalikan Buku #{{ $p->no_transaksi }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('peminjaman.kembalikan', $p->id_peminjaman) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="text-muted mb-3">Pilih buku yang ingin dikembalikan:</p>
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="50"><input type="checkbox" id="checkAllKembalikan{{ $p->id_peminjaman }}" onclick="toggleAllCheckbox(this, 'kembalikan{{ $p->id_peminjaman }}')"></th>
                                        <th>Judul</th>
                                        <th>Barcode</th>
                                        <th>Batas Kembali</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($canReturnItems as $item)
                                        @php
                                            $displayDate = $item->loan_due_date ? \Carbon\Carbon::parse($item->loan_due_date) : null;
                                            $isPast = $displayDate && $displayDate->isPast();
                                        @endphp
                                        <tr class="{{ $isPast ? 'table-danger' : '' }}">
                                            <td><input type="checkbox" name="items[]" value="{{ $item->id_item }}" class="kembalikan{{ $p->id_peminjaman }}"></td>
                                            <td>{{ $item->bukus->judul }}</td>
                                            <td><code>{{ $item->barcode }}</code></td>
                                            <td>
                                                {{ $displayDate ? $displayDate->format('d/m/Y') : '-' }}
                                                @if($isPast) <span class="badge bg-danger">TELAT!</span> @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kondisi buku saat dikembalikan:</label>
                                    <select name="kondisi" class="form-select" required>
                                        <option value="">Pilih kondisi</option>
                                        <option value="baik">✅ Baik</option>
                                        <option value="rusak">⚠️ Rusak</option>
                                        <option value="hilang">❌ Hilang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-undo me-1"></i>Kembalikan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- MODAL DETAIL, HAPUS, DAN LAINNYA TETAP SAMA --}}
    @foreach($peminjaman as $p)
        <div class="modal fade" id="detailModal{{ $p->id_peminjaman }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Peminjaman #{{ $p->id_peminjaman }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

    @foreach($peminjaman as $p)
        <div class="modal fade" id="hapusModal{{ $p->id_peminjaman }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin hapus peminjaman <strong>#{{ $p->no_transaksi }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form action="{{ route('peminjaman.destroy', $p->id_peminjaman) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Ya, Hapus!</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- =====================================================
    MODAL PINJAM BUKU - FIXED BISA SCROLL
    Ganti modal lama dengan ini bro!
===================================================== --}}
    <div class="modal fade" id="pinjamModal" tabindex="-1">
        {{-- Tambah modal-dialog-scrollable biar bisa scroll --}}
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                {{-- Header Modal --}}
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-book-open me-2"></i>Pilih Buku untuk Dipinjam
                    </h5>
                    <span class="badge bg-light text-dark ms-2">Maks 2 Buku</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Body Modal - Bisa Scroll --}}
                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">

                    {{-- ===== FITUR SCAN BARCODE ===== --}}
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <i class="fas fa-barcode me-2"></i>Scan Barcode (Lebih Cepat!)
                        </div>
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-qrcode"></i></span>
                                        <input type="text" id="scanBarcodeInput" class="form-control"
                                               placeholder="Scan atau ketik barcode/ISBN..." autofocus>
                                        <button type="button" id="btnScanBarcode" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Tambah
                                        </button>
                                    </div>
                                    <small class="text-muted">Langsung scan barcode eksemplar atau ISBN buku</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <span class="badge bg-success" id="scanStatus">Siap Scan</span>
                                </div>
                            </div>
                            {{-- Tempat hasil scan --}}
                            <div id="scanResult" class="mt-2"></div>
                        </div>
                    </div>

                    <hr class="my-3">
                    <p class="text-center text-muted mb-3"><small>— atau cari manual —</small></p>

                    {{-- ===== SEARCH BUKU MANUAL ===== --}}
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="search-buku-input" class="form-control" placeholder="Cari judul buku...">
                        <button type="button" id="btn-search-buku" class="btn btn-dark">Cari</button>
                        <button type="button" id="reset-buku-search" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>

                    {{-- Table Buku (bisa scroll sendiri) --}}
                    <div id="buku-table-container" class="mb-3" style="max-height: 200px; overflow-y: auto;"></div>

                    {{-- ===== CARD BUKU TERPILIH ===== --}}
                    <div id="selectedCard" class="card border-success mt-3" style="display: none;">
                        <div class="card-header bg-success text-white py-2">
                            <i class="fas fa-check-circle me-2"></i>Buku Terpilih (<span id="selectedCount">0</span>/2)
                        </div>
                        <div class="card-body py-2">
                            <div id="selectedBuku"></div>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="button" id="lanjutForm" class="btn btn-primary" disabled>
                        <i class="fas fa-arrow-right me-1"></i>Lanjut
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Eksemplar, Member, Detail Pinjam (tetap sama seperti sebelumnya) --}}
    @include('peminjaman.partials.modal-eksemplar')
    @include('peminjaman.partials.modal-member')
    @include('peminjaman.partials.modal-detail-pinjam')

@endsection

@push('scripts')
    <script>
        // =====================================================
        // FUNCTION TOGGLE CHECKBOX (untuk perpanjang/kembalikan)
        // =====================================================
        function toggleAllCheckbox(source, className) {
            const checkboxes = document.querySelectorAll('.' + className);
            checkboxes.forEach(cb => cb.checked = source.checked);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // ===== DEKLARASI VARIABEL =====
            const pinjamModal = document.getElementById('pinjamModal');
            const eksemplarModal = document.getElementById('eksemplarModal');
            const memberModal = document.getElementById('memberModal');
            const memberTableContainer = document.getElementById('member-table-container');
            const searchMemberInput = document.getElementById('search-member-input');

            let selectedItems = [];      // Array buat nyimpen buku yang dipilih
            let currentIdBuku = '';      // ID buku yang lagi dipilih eksemplarnya
            let maxSelected = 2;         // Maksimal buku yang bisa dipinjam

            // =====================================================
            // FITUR SCAN BARCODE - BARU!
            // =====================================================
            const scanInput = document.getElementById('scanBarcodeInput');
            const btnScan = document.getElementById('btnScanBarcode');
            const scanResult = document.getElementById('scanResult');
            const scanStatus = document.getElementById('scanStatus');

            // Function untuk proses scan barcode
            async function processBarcodeScan(barcode) {
                // Validasi: barcode gak boleh kosong
                if (!barcode.trim()) return;

                // Update status jadi "Mencari..."
                scanStatus.textContent = 'Mencari...';
                scanStatus.className = 'badge bg-warning';
                scanResult.innerHTML = '';

                try {
                    // Kirim request ke server
                    const response = await fetch('{{ route("peminjaman.scanBarcode") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ barcode: barcode.trim() })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Cek apakah buku ini udah dipilih sebelumnya
                        if (selectedItems.some(s => s.id_item === data.data.id_item)) {
                            scanResult.innerHTML = '<div class="alert alert-info py-1 mb-0"><small><i class="fas fa-info-circle"></i> Buku ini udah dipilih bro!</small></div>';
                            scanStatus.textContent = 'Udah Dipilih';
                            scanStatus.className = 'badge bg-info';
                            scanInput.value = '';
                            scanInput.focus();
                            return;
                        }

                        // Cek apakah udah max 2 buku
                        if (selectedItems.length >= maxSelected) {
                            scanResult.innerHTML = '<div class="alert alert-warning py-1 mb-0"><small><i class="fas fa-exclamation-triangle"></i> Maksimal ' + maxSelected + ' buku bro!</small></div>';
                            scanStatus.textContent = 'Kuota Penuh';
                            scanStatus.className = 'badge bg-danger';
                            scanInput.value = '';
                            scanInput.focus();
                            return;
                        }

                        // SUKSES! Tambahkan ke list
                        selectedItems.push({
                            id_item: data.data.id_item,
                            barcode: data.data.barcode,
                            kondisi: data.data.kondisi,
                            judul: data.data.judul
                        });

                        updateSelectedList();
                        scanResult.innerHTML = '<div class="alert alert-success py-1 mb-0"><small><i class="fas fa-check-circle"></i> <strong>' + data.data.judul + '</strong> berhasil ditambahkan!</small></div>';
                        scanStatus.textContent = 'Berhasil!';
                        scanStatus.className = 'badge bg-success';
                        scanInput.value = '';
                        scanInput.focus();

                        // Reset status setelah 2 detik
                        setTimeout(() => {
                            scanStatus.textContent = 'Siap Scan';
                            scanStatus.className = 'badge bg-success';
                            scanResult.innerHTML = '';
                        }, 2000);

                    } else {
                        // GAGAL - Tampilkan pesan error
                        scanResult.innerHTML = '<div class="alert alert-danger py-1 mb-0"><small><i class="fas fa-times-circle"></i> ' + data.message + '</small></div>';
                        scanStatus.textContent = 'Gak Ketemu';
                        scanStatus.className = 'badge bg-danger';
                        scanInput.select(); // Select text biar gampang hapus
                    }
                } catch (error) {
                    console.error('Error:', error);
                    scanResult.innerHTML = '<div class="alert alert-danger py-1 mb-0"><small><i class="fas fa-times-circle"></i> Terjadi kesalahan!</small></div>';
                    scanStatus.textContent = 'Error';
                    scanStatus.className = 'badge bg-danger';
                }
            }

            // Event: Klik tombol scan
            if (btnScan) {
                btnScan.addEventListener('click', () => processBarcodeScan(scanInput.value));
            }

            // Event: Tekan Enter di input scan
            if (scanInput) {
                scanInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        processBarcodeScan(this.value);
                    }
                });
            }

            // =====================================================
            // MODAL PINJAM BUKU
            // =====================================================
            if (pinjamModal) {
                pinjamModal.addEventListener('show.bs.modal', () => {
                    selectedItems = [];  // Reset pilihan
                    updateSelectedList();
                    loadBukuTable();     // Load table buku
                    // Focus ke input scan setelah modal muncul
                    setTimeout(() => {
                        if (scanInput) scanInput.focus();
                    }, 500);
                });
            }

            // Tombol Lanjut
            document.getElementById('lanjutForm')?.addEventListener('click', function() {
                if (selectedItems.length === 0) {
                    alert('Pilih minimal 1 buku dulu bro!');
                    return;
                }

                // Isi hidden input dengan id_item yang dipilih
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

                // Tampilkan list buku di modal detail
                const bukuList = document.getElementById('bukuDetailList');
                if (bukuList) {
                    bukuList.innerHTML = selectedItems.map(item =>
                        `<li><strong>${item.barcode}</strong> - ${item.judul || ''} <small class="text-muted">(${item.kondisi})</small></li>`
                    ).join('');
                }

                // Tutup modal pinjam, buka modal detail
                const bsPinjam = bootstrap.Modal.getInstance(pinjamModal);
                bsPinjam.hide();

                pinjamModal.addEventListener('hidden.bs.modal', function handler() {
                    pinjamModal.removeEventListener('hidden.bs.modal', handler);
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

                    const detailModal = new bootstrap.Modal(document.getElementById('newPinjamDetailModal'));
                    detailModal.show();
                });
            });

            // =====================================================
            // LOAD TABLE BUKU
            // =====================================================
            function loadBukuTable(search = '', page = 1) {
                const container = document.getElementById('buku-table-container');
                container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat...</div>';

                fetch(`/peminjaman/bukus?search=${encodeURIComponent(search)}&page=${page}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.text())
                    .then(html => {
                        container.innerHTML = html;
                        attachPilihEksemplarButtons();
                        attachBukuPagination();
                    });
            }

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

            function attachBukuPagination() {
                document.querySelectorAll('#buku-table-container .pagination a').forEach(link => {
                    link.onclick = e => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        loadBukuTable(url.searchParams.get('search') || '', url.searchParams.get('page') || 1);
                    };
                });
            }

            // Search Buku
            document.getElementById('btn-search-buku')?.addEventListener('click', () => {
                loadBukuTable(document.getElementById('search-buku-input').value.trim());
            });

            document.getElementById('search-buku-input')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadBukuTable(this.value.trim());
                }
            });

            document.getElementById('reset-buku-search')?.addEventListener('click', () => {
                document.getElementById('search-buku-input').value = '';
                loadBukuTable();
            });

            // =====================================================
            // LOAD EKSEMPLAR
            // =====================================================
            function loadEksemplar(id_buku, search = '', page = 1) {
                currentIdBuku = id_buku;
                const container = document.getElementById('eksemplar-table-container');
                container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

                fetch(`/get-eksemplar-by-buku/${id_buku}?query=${encodeURIComponent(search)}&page=${page}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = `<table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th>Barcode</th><th>Kondisi</th><th>Aksi</th></tr></thead><tbody>`;
                        if (data.data.length === 0) {
                            html += `<tr><td colspan="3" class="text-center text-muted">Gak ada eksemplar</td></tr>`;
                        } else {
                            data.data.forEach(item => {
                                const isSelected = selectedItems.some(s => s.id_item === item.id_item);
                                html += `<tr class="${isSelected ? 'table-secondary' : ''}">
                            <td><code>${item.barcode}</code></td>
                            <td><span class="badge bg-success">${item.kondisi}</span></td>
                            <td><button class="btn btn-sm btn-primary pilih-item" data-id="${item.id_item}" data-barcode="${item.barcode}" data-kondisi="${item.kondisi}" ${isSelected ? 'disabled' : ''}>Pilih</button></td>
                        </tr>`;
                            });
                        }
                        html += `</tbody></table>`;
                        if (data.links) html += `<div class="mt-2">${data.links}</div>`;
                        container.innerHTML = html;
                        attachEksemplarButtons();
                    });
            }

            function attachEksemplarButtons() {
                document.querySelectorAll('.pilih-item').forEach(btn => {
                    btn.onclick = function () {
                        const id = parseInt(this.dataset.id);
                        if (selectedItems.some(s => s.id_item === id)) return;
                        if (selectedItems.length >= maxSelected) {
                            alert('Maksimal ' + maxSelected + ' buku bro!');
                            return;
                        }
                        selectedItems.push({
                            id_item: id,
                            barcode: this.dataset.barcode,
                            kondisi: this.dataset.kondisi,
                            judul: ''
                        });
                        updateSelectedList();
                        bootstrap.Modal.getInstance(eksemplarModal)?.hide();
                    };
                });
            }

            document.getElementById('searchEksemplar')?.addEventListener('input', function () {
                if (currentIdBuku) loadEksemplar(currentIdBuku, this.value.trim());
            });

            // =====================================================
            // UPDATE SELECTED LIST (Buku yang dipilih)
            // =====================================================
            function updateSelectedList() {
                const list = document.getElementById('selectedBuku');
                const count = document.getElementById('selectedCount');
                const card = document.getElementById('selectedCard');
                const lanjutBtn = document.getElementById('lanjutForm');

                if (list) {
                    list.innerHTML = selectedItems.map((item, i) => `
                <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 bg-white">
                    <div>
                        <strong>${item.barcode}</strong>
                        ${item.judul ? '<br><small class="text-muted">' + item.judul + '</small>' : ''}
                        <br><small class="text-muted">Kondisi: ${item.kondisi}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${i})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
                }
                if (count) count.textContent = selectedItems.length;
                if (card) card.style.display = selectedItems.length ? 'block' : 'none';
                if (lanjutBtn) lanjutBtn.disabled = selectedItems.length === 0;
            }

            // Function hapus item dari list
            window.removeItem = function (index) {
                selectedItems.splice(index, 1);
                updateSelectedList();
            };

            // =====================================================
            // MEMBER
            // =====================================================
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
                                alert('Member ini udah pinjam 2 buku bro!');
                                return;
                            }

                            if (selectedItems.length > maxSelected) {
                                selectedItems = selectedItems.slice(0, maxSelected);
                                updateSelectedList();
                                alert('Kuota member cuma ' + maxSelected + ' buku lagi!');
                            }

                            document.getElementById('selectedMemberId').value = id;
                            document.getElementById('selectedMemberDisplay').value = nama;
                            document.getElementById('namaPeminjam').value = nama;
                            document.getElementById('alamatPeminjam').value = '';
                            document.getElementById('alamatPeminjam').removeAttribute('readonly');

                            bootstrap.Modal.getInstance(memberModal)?.hide();
                            new bootstrap.Modal(document.getElementById('newPinjamDetailModal')).show();
                        } catch (err) {
                            alert('Gagal cek kuota bro.');
                        }
                    };
                });
            }

            if (memberModal) {
                memberModal.addEventListener('show.bs.modal', () => loadMemberTable());
            }

            function loadMemberTable(search = '', page = 1) {
                memberTableContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
                fetch(`/peminjaman/members?search=${encodeURIComponent(search)}&page=${page}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.text())
                    .then(html => {
                        memberTableContainer.innerHTML = html;
                        attachMemberButtons();
                        document.querySelectorAll('#member-table-container .pagination a').forEach(link => {
                            link.onclick = e => {
                                e.preventDefault();
                                const url = new URL(link.href);
                                loadMemberTable(url.searchParams.get('search') || '', url.searchParams.get('page') || 1);
                            };
                        });
                    });
            }

            document.getElementById('search-member-form')?.addEventListener('submit', e => {
                e.preventDefault();
                loadMemberTable(searchMemberInput.value.trim());
            });

            document.getElementById('reset-member-search')?.addEventListener('click', () => {
                searchMemberInput.value = '';
                loadMemberTable();
            });

            // =====================================================
            // SUBMIT FORM PINJAM
            // =====================================================
            document.getElementById('pinjamForm')?.addEventListener('submit', async function (e) {
                e.preventDefault();
                const memberId = document.getElementById('selectedMemberId')?.value;
                if (!memberId) {
                    alert('Pilih member dulu bro!');
                    new bootstrap.Modal(document.getElementById('memberModal')).show();
                    return;
                }
                try {
                    const res = await fetch(`/peminjaman/active/${memberId}`);
                    const data = await res.json();
                    if (selectedItems.length > (2 - (data.active || 0))) {
                        alert('Kuota member gak cukup bro!');
                        return;
                    }
                    this.submit();
                } catch (err) {
                    alert('Gagal cek kuota.');
                }
            });

            // =====================================================
// PAGINATION EKSEMPLAR DI MODAL
// =====================================================
            document.addEventListener('click', function(e) {
                // Deteksi apakah yang diklik adalah link pagination dalam eksemplar
                const paginationLink = e.target.closest('#eksemplar-table-container .pagination a, #eksemplarPagination .pagination a');

                if (paginationLink) {
                    e.preventDefault();

                    const url = new URL(paginationLink.href);
                    const page = url.searchParams.get('page') || 1;
                    const query = url.searchParams.get('query') || '';

                    // Pastikan ada ID buku yang sedang dipilih
                    if (currentIdBuku) {
                        loadEksemplar(currentIdBuku, query, page);
                    }
                }
            });

        });
    </script>
@endpush
