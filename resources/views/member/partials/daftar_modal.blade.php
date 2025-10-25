<div class="modal fade" id="daftarMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('member.register') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Formulir Pendaftaran Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Profesi</label>
                        <input name="profesi" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Foto KTP</label>
                        <input type="file" name="ktp" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Kartu Pelajar (opsional)</label>
                        <input type="file" name="student_card" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Kirim Permintaan</button>
                </div>
            </div>
        </form>
    </div>
</div>
