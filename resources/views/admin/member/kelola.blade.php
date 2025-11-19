@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Kelola Member Perpustakaan</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahMemberModal">
                <i class="fas fa-user-plus"></i> Daftar Member Baru
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif



                <div class="table-responsive">
                    <table class="table table-modern table-bordered table-striped fade-in">
                        <thead class="table-dark">
                        <tr class="text-center">
                            <th>No</th>
                            <th>No. Member</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Profesi</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($members as $index => $member)
                            <tr class="text-center">
                                <td class="text-center">{{ ($members->currentPage() - 1) * $members->perPage() + $loop->iteration }}</td>
                                <td class="text-center"><strong>{{ $member->no_member }}</strong></td>
                                <td>{{ $member->nama_lengkap }}</td>
                                <td>{{ $member->user->email }}</td>
                                <td>{{ $member->no_hp }}</td>
                                <td>{{ $member->profesi ?? '-' }}</td>
                                <td class="text-center">{{ $member->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.member.profile', $member->user_id) }}"
                                       class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Belum ada member terdaftar
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $members->links('pagination::bootstrap-5') }}
                </div>
    </div>

    <!-- Modal Tambah Member -->
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
                                       placeholder="08xxxxxxxxxx" maxlength="12">
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

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Foto 3x4 <span class="text-danger">*</span></label>
                                <input type="file" name="foto_3x4" class="form-control" required accept="image/*">
                                <small class="text-muted">Max 2MB</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Foto KTP</label>
                                <input type="file" name="ktp" class="form-control" accept="image/*">
                                <small class="text-muted">Max 2MB</small>
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
                            <i class="fas fa-save"></i> Daftar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
