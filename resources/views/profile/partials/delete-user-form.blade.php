<section class="space-y-6">
    <header>
        <h2 class="h5 text-danger fw-bold">
            {{ __('Hapus Akun') }}
        </h2>

        <p class="text-muted">
            Setelah akun Anda dihapus, semua data akan hilang secara permanen.
            Pastikan untuk menyimpan data penting sebelum melanjutkan.
        </p>
    </header>

    <!-- Tombol Delete -->
    <button type="button" class="btn btn-danger w-100"
            data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        Hapus Akun
    </button>

    <!-- Modal Konfirmasi -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Setelah akun dihapus, semua data akan hilang secara permanen. Apakah Anda yakin?</p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus Akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
