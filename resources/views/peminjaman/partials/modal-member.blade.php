{{--
    FILE: resources/views/peminjaman/partials/modal-member.blade.php
    Modal untuk pilih member
--}}
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
                {{-- Search Member --}}
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

                {{-- Table Member --}}
                <div id="member-table-container" class="table-modern mb-4"></div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
