@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Data Diri Member</h4>
                <a href="{{ url()->previous() }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card-body">
                {{-- Pesan sukses / error --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Informasi User --}}
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <div class="border rounded-circle overflow-hidden mx-auto" style="width:150px;height:150px;">
                            @if($user->photo)
                                <img src="{{ asset('storage/'.$user->photo) }}" alt="Foto Profil" class="img-fluid w-100 h-100 object-fit-cover">
                            @else
                                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Default User" class="img-fluid w-100 h-100 object-fit-cover">
                            @endif
                        </div>
                        <h5 class="mt-3 fw-bold">{{ $user->name }}</h5>
                        <span class="badge bg-info text-dark text-capitalize">{{ $user->role }}</span>
                    </div>

                    <div class="col-md-9">
                        <h5 class="fw-bold mb-3 text-primary">Informasi Akun</h5>
                        <table class="table table-bordered align-middle">
                            <tr>
                                <th style="width:200px;">Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Status Akun</th>
                                <td>
                                    @if($user->is_verified_member)
                                        <span class="badge bg-success">Sudah Terverifikasi</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Terverifikasi</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status Keanggotaan</th>
                                <td>
                                    @if($user->is_member)
                                        <span class="badge bg-success">Member Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Belum Member</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Daftar</th>
                                <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Data Profil Member --}}
                <h5 class="fw-bold mb-3 text-primary">Data Pendaftaran Member</h5>
                <table class="table table-bordered table-striped">
                    <tr>
                        <th style="width:200px;">Nama Lengkap</th>
                        <td>{{ $profile->nama_lengkap ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $profile->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. HP</th>
                        <td>{{ $profile->no_hp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Permintaan</th>
                        <td>
                            @if($profile->request_status === 'approved')
                                <span class="badge bg-success">Disetujui</span>
                            @elseif($profile->request_status === 'pending')
                                <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                            @elseif($profile->request_status === 'rejected')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-secondary">Tidak diketahui</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Disetujui Oleh</th>
                        <td>{{ $profile->approver->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Disetujui</th>
                        <td>{{ $profile->approved_at ? \Carbon\Carbon::parse($profile->approved_at)->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kartu Identitas</th>
                        <td>
                            @if($profile->ktp_path)
                                <button class="btn btn-info btn-sm text-white"
                                        data-bs-toggle="modal"
                                        data-bs-target="#previewModal"
                                        data-img="{{ asset('storage/'.$profile->ktp_path) }}">
                                    <i class="bi bi-card-heading"></i> Lihat KTP
                                </button>
                            @elseif($profile->kartu_pelajar_path)
                                <button class="btn btn-secondary btn-sm text-white"
                                        data-bs-toggle="modal"
                                        data-bs-target="#previewModal"
                                        data-img="{{ asset('storage/'.$profile->kartu_pelajar_path) }}">
                                    <i class="bi bi-person-vcard"></i> Lihat Kartu Pelajar
                                </button>
                            @else
                                <span class="text-muted">Tidak ada identitas diunggah</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Preview Identitas -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="previewModalLabel">Preview Identitas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" alt="Identitas" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var previewModal = document.getElementById('previewModal');
                previewModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var imageSrc = button.getAttribute('data-img');
                    var modalImage = document.getElementById('previewImage');
                    modalImage.src = imageSrc;
                });
            });
        </script>
    @endpush

@endsection
