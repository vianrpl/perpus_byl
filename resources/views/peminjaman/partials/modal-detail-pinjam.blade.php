{{--
    FILE: resources/views/peminjaman/partials/modal-detail-pinjam.blade.php
    Modal form detail peminjaman (setelah pilih buku)
--}}
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
                    {{-- Hidden input untuk ID Items --}}
                    <div id="idItemContainer"></div>

                    {{-- Info Buku Terpilih --}}
                    <div class="alert alert-info rounded-3 mb-4">
                        <h6><i class="fas fa-book me-2"></i>Buku Terpilih:</h6>
                        <ul id="bukuDetailList" class="mb-0"></ul>
                    </div>

                    {{-- Hidden ID User (Petugas) --}}
                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">

                    {{-- Pilih Member --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="selectedMemberDisplay">Pilih Member</label>
                        <div class="input-group">
                            <input type="text" id="selectedMemberDisplay" class="form-control" placeholder="Klik untuk pilih member..." readonly>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                        <input type="hidden" name="id_member" id="selectedMemberId" value="">
                    </div>

                    {{-- Nama Peminjam --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="namaPeminjam">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" id="namaPeminjam" class="form-control" required readonly>
                    </div>

                    {{-- Alamat --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="alamatPeminjam">Alamat</label>
                        <input type="text" name="alamat" id="alamatPeminjam" class="form-control" required placeholder="Masukkan alamat peminjam" readonly>
                    </div>

                    {{-- Tanggal Pengembalian --}}
                    <div class="mb-0">
                        <label class="form-label fw-bold" for="pengembalian">Tanggal Pengembalian</label>
                        <input type="date" name="pengembalian" id="pengembalian" class="form-control" required
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               max="{{ now()->addDays(7)->format('Y-m-d') }}">
                        <small class="text-muted">Maksimal 7 hari dari sekarang</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Pinjam Buku
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Peringatan Max Buku --}}
<div class="modal fade" id="maxBukuModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Peringatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Maksimal 2 buku yang bisa dipinjam!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
