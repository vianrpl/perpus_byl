<div class="modal fade" id="tambahMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('admin.member.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Formulir Pendaftaran Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   placeholder="contoh@email.com">
                            <small class="text-muted">Password default: <code>member123</code></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required
                                   placeholder="Masukkan nama lengkap">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor HP <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp" class="form-control" required
                                   placeholder="08xxxxxxxxxx" maxlength="15">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profesi</label>
                            <input type="text" name="profesi" class="form-control"
                                   placeholder="Pelajar/Mahasiswa/Umum">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required
                                  placeholder="Masukkan alamat lengkap"></textarea>
                    </div>

                    <hr>
                    <h6 class="mb-3"><i class="fas fa-file-image"></i> Dokumen</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Foto 3x4 <span class="text-danger">*</span></label>
                            <input type="file" name="foto_3x4" class="form-control" required accept="image/*">
                            <small class="text-muted">Max 2MB</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Foto KTP</label>
                            <input type="file" name="ktp" class="form-control" accept="image/*">
                            <small class="text-muted">Opsional, Max 2MB</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kartu Pelajar</label>
                            <input type="file" name="student_card" class="form-control" accept="image/*">
                            <small class="text-muted">Opsional, Max 2MB</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Daftar Member
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
