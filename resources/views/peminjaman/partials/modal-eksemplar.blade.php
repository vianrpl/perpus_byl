{{--
    FILE: resources/views/peminjaman/partials/modal-eksemplar.blade.php
    Modal untuk pilih eksemplar buku
--}}
<div class="modal fade modal-pinjam" id="eksemplarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-barcode me-2"></i>
                    <span id="eksemplarModalLabel">Pilih Eksemplar</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Search Barcode --}}
                <div class="input-group search-modern mb-3">
                    <span class="input-group-text bg-transparent border-0">
                        <i class="fas fa-barcode text-muted"></i>
                    </span>
                    <input type="text" id="searchEksemplar" class="form-control border-0 bg-transparent" placeholder="Cari barcode...">
                </div>

                {{-- Table Eksemplar --}}
                <div id="eksemplar-table-container" class="mt-3"></div>
                <div id="eksemplarPagination" class="d-flex justify-content-center mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
