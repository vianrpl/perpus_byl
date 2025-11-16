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
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal" data-id="{{ $p->id_peminjaman }}">
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

    {{-- MODAL BUKU DIPINJAM (DI LUAR TABEL, TAPI DI DALAM LOOP) --}}
    @foreach($peminjaman as $p)
        <div class="modal fade" id="bukuModal{{ $p->id_peminjaman }}" tabindex="-1" aria-labelledby="bukuModalLabel{{ $p->id_peminjaman }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="bukuModalLabel{{ $p->id_peminjaman }}">Buku Dipinjam #{{ $p->id_peminjaman }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bulkForm{{ $p->id_peminjaman }}">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" onclick="selectAll(this, '{{ $p->id_peminjaman }}')"></th>
                                    <th>Judul Buku</th>
                                    <th>Barcode</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $idItems = json_decode($p->id_items, true) ?? [$p->id_item];
                                    $items = \App\Models\buku_items::with('bukus')->whereIn('id_item', $idItems)->get();
                                @endphp
                                @if($items->isEmpty())
                                    <tr><td colspan="3" class="text-center text-muted">Tidak ada data buku</td></tr>
                                @else
                                    @foreach($items as $item)
                                        <tr>
                                            <td><input type="checkbox" name="items[]" value="{{ $item->id_item }}"></td>
                                            <td>{{ $item->bukus->judul ?? 'Judul Tidak Diketahui' }}</td>
                                            <td>{{ $item->barcode ?? $item->id_item }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                    <div class="modal-footer">
                        @if(in_array($p->status, ['dipinjam', 'diperpanjang']))
                            <button class="btn btn-warning btn-sm" onclick="bulkAction('perpanjang', '{{ $p->id_peminjaman }}')">
                                Perpanjang
                            </button>
                            <button class="btn btn-success btn-sm" onclick="bulkAction('kembalikan', '{{ $p->id_peminjaman }}')">
                                Kembalikan
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
                            <a href="{{ route('admin.member.profile', $p->user->id_user) }}" class="btn btn-info text-white">
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
    <div class="modal fade" id="hapusModal" tabindex="-1" aria-labelledby="hapusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="hapusForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="hapusModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <p>Apakah kamu yakin ingin menghapus data peminjaman ini?</p>
                    <p class="text-danger small fst-italic">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
        </div>
        </form>
    </div>
    </div>

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
                    <div id="eksemplar-table-container" class="mt-3">
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
                                <label class="form-label fw-bold">Pilih Member</label>
                                <div class="input-group">
                                    <input type="text" id="selectedMemberDisplay" class="form-control" placeholder="Klik untuk pilih member..." readonly>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                                <input type="hidden" name="id_member" id="selectedMemberId">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Peminjam</label>
                                <input type="text" name="nama_peminjam" id="namaPeminjam" class="form-control" required readonly>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Alamat</label>
                                <input type="text" name="alamat" id="alamatPeminjam" class="form-control" required placeholder="Masukkan alamat peminjam" readonly>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">Tanggal Pengembalian</label>
                                <input type="date" name="pengembalian" class="form-control" required
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
        <div class="modal-dialog modal-sm">
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

@endsection
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const pinjamModal = document.getElementById('pinjamModal');
                    const eksemplarModal = document.getElementById('eksemplarModal');
                    let selectedItems = [];
                    let currentIdBuku = '';
                    let maxSelected = 2;

                    if (pinjamModal) {
                        pinjamModal.addEventListener('show.bs.modal', () => {
                            selectedItems = [];
                            updateSelectedList();
                            loadBukuTable();
                            setTimeout(() => {
                                attachLanjutEvent();
                            }, 100);
                        });
                    }

                    function attachLanjutEvent() {
                        const lanjutBtn = document.getElementById('lanjutForm');
                        if (lanjutBtn) {
                            lanjutBtn.removeEventListener('click', lanjutHandler);
                            lanjutBtn.addEventListener('click', lanjutHandler);
                            console.log('Event Lanjut ke-attach!');
                        } else {
                            console.error('Tombol #lanjutForm TIDAK DITEMUKAN!');
                        }
                    }

                    function lanjutHandler() {
                        console.log('LANJUT DIKLIK! selectedItems:', selectedItems.length);

                        if (selectedItems.length === 0) {
                            alert('Pilih minimal 1 buku!');
                            return;
                        }
                        if (selectedItems.length > maxSelected) {
                            new bootstrap.Modal(document.getElementById('maxBukuModal')).show();
                            return;
                        }

                        // Isi hidden inputs
                        const container = document.getElementById('idItemContainer');
                        if (!container) return console.error('idItemContainer tidak ditemukan!');
                        container.innerHTML = '';
                        selectedItems.forEach(item => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'id_item[]';
                            input.value = item.id;
                            container.appendChild(input);
                        });

                        // Isi daftar buku di detail modal
                        const bukuList = document.getElementById('bukuDetailList');
                        if (bukuList) {
                            bukuList.innerHTML = selectedItems.map(item =>
                                `<li><strong>${item.barcode}</strong> <small class="text-muted">(${item.kondisi})</small></li>`
                            ).join('');
                        }

                        // Paksa tutup pinjamModal dan buka detail dengan fix ARIA
                        try {
                            const pinjamModalEl = document.getElementById('pinjamModal');
                            if (!pinjamModalEl) throw new Error('pinjamModalEl tidak ditemukan!');

                            const bsPinjam = bootstrap.Modal.getInstance(pinjamModalEl) || new bootstrap.Modal(pinjamModalEl, { focus: false });
                            bsPinjam.hide();

                            // Tunggu hidden event sebelum buka detail
                            const onHidden = () => {
                                pinjamModalEl.removeEventListener('hidden.bs.modal', onHidden);
                                console.log('BUKA DETAIL MODAL SEKARANG!');
                                const detailModalEl = document.getElementById('newPinjamDetailModal');
                                if (!detailModalEl) throw new Error('newPinjamDetailModal tidak ditemukan!');

                                // Hapus backdrop lama untuk hindari overlap
                                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

                                // Buka dengan focus false untuk fix ARIA
                                const modal = new bootstrap.Modal(detailModalEl, { backdrop: 'static', focus: false });
                                modal.show();

                                // Manual pindah focus setelah show
                                detailModalEl.addEventListener('shown.bs.modal', () => {
                                    const firstInput = detailModalEl.querySelector('#selectedMemberDisplay');
                                    if (firstInput) firstInput.focus();
                                    console.log('Focus dipindah ke detail modal!');
                                }, { once: true });
                            };

                            if (!pinjamModalEl.classList.contains('show')) {
                                onHidden();
                            } else {
                                pinjamModalEl.addEventListener('hidden.bs.modal', onHidden);
                            }
                        } catch (e) {
                            console.error('Error saat tutup/buka modal:', e);
                            // Fallback: Buka detail langsung
                            const detailModalEl = document.getElementById('newPinjamDetailModal');
                            if (detailModalEl) {
                                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                                new bootstrap.Modal(detailModalEl, { backdrop: 'static', focus: false }).show();
                                setTimeout(() => {
                                    detailModalEl.querySelector('#selectedMemberDisplay')?.focus();
                                }, 100);
                            }
                        }
                    }

                    // === FUNGSI LAIN TETAP SAMA ===
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

                    function loadEksemplar(id_buku, search = '', page = 1) {
                        currentIdBuku = id_buku;
                        const url = `/get-eksemplar-by-buku/${id_buku}?query=${encodeURIComponent(search)}&page=${page}`;

                        fetch(url)
                            .then(res => res.json())
                            .then(data => {
                                let html = `
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Barcode</th>
                                            <th>Kondisi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                                if (data.data.length === 0) {
                                    html += `<tr><td colspan="3" class="text-center text-muted">Tidak ada eksemplar tersedia</td></tr>`;
                                } else {
                                    data.data.forEach(item => {
                                        const isSelected = selectedItems.some(s => s.id === item.id_item);
                                        html += `
                                    <tr class="${isSelected ? 'table-secondary' : ''}">
                                        <td>${item.barcode}</td>
                                        <td><span class="badge bg-success">${item.kondisi}</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary pilih-item"
                                                    data-id="${item.id_item}"
                                                    data-barcode="${item.barcode}"
                                                    data-kondisi="${item.kondisi}"
                                                    ${isSelected ? 'disabled' : ''}>
                                                Pilih
                                            </button>
                                        </td>
                                    </tr>
                                `;
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
                                if (selectedItems.some(s => s.id === id)) return;
                                if (selectedItems.length >= maxSelected) {
                                    new bootstrap.Modal(document.getElementById('maxBukuModal')).show();
                                    return;
                                }
                                selectedItems.push({
                                    id: id,
                                    barcode: this.dataset.barcode,
                                    kondisi: this.dataset.kondisi
                                });
                                updateSelectedList();
                                loadEksemplar(currentIdBuku, document.getElementById('searchEksemplar')?.value || '');
                                // Tutup modal eksemplar
                                const closeBtn = eksemplarModal.querySelector('.btn-close');
                                if (closeBtn) closeBtn.click();
                            };
                        });
                    }

                    function updateSelectedList() {
                        const list = document.getElementById('selectedBuku');
                        const count = document.getElementById('selectedCount');
                        const card = document.getElementById('selectedCard');
                        const lanjutBtn = document.getElementById('lanjutForm');
                        if (list) {
                            list.innerHTML = selectedItems.map((item, i) => {
                                return `
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 bg-light">
                                <div>
                                    <strong>${item.barcode}</strong>
                                    <small class="text-muted d-block">Kondisi: ${item.kondisi}</small>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="removeItem(${i})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                            }).join('');
                        }
                        if (count) count.textContent = selectedItems.length;
                        if (card) card.style.display = selectedItems.length ? 'block' : 'none';
                        if (lanjutBtn) lanjutBtn.disabled = selectedItems.length === 0;
                    }

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
                        if (currentIdBuku) {
                            loadEksemplar(currentIdBuku, this.value.trim());
                        }
                    });

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
