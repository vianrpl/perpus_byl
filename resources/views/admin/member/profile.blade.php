@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-id-card"></i> Data Diri Member</h3>
            <a href="{{ route('admin.member.kelola') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="row">
            <!-- Kartu Member -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="mb-0">Kartu Member</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($profile->foto_3x4)
                            <img src="{{ asset('storage/'.$profile->foto_3x4) }}"
                                 style="width: 90px; height: 120px; object-fit: cover; border-radius: 6px;">

                        @else
                            <div class="bg-light p-5 mb-3">
                                <i class="fas fa-user fa-5x text-muted"></i>
                            </div>
                        @endif

                        <h5 class="mb-2">{{ $profile->nama_lengkap ?? $user->name }}</h5>

                        @if($profile->no_member)
                            <div class="alert alert-success mb-3">
                                <strong>No. Member:</strong><br>
                                <h4 class="mb-0">{{ $profile->no_member }}</h4>
                            </div>

                            <!-- Barcode Member -->
                            <div class="bg-white p-3 border rounded">
                                <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $profile->no_member }}&code=Code128"
                                     alt="Barcode" class="img-fluid">
                            </div>
                        @endif

                        <div class="mt-3">
                            @if($user->is_member)
                                <span class="badge bg-success">Member Aktif</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Informasi -->
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Member</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tr>
                                <th width="200">Nama Lengkap</th>
                                <td>{{ $profile->nama_lengkap ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Nomor HP</th>
                                <td>{{ $profile->no_hp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Profesi</th>
                                <td>{{ $profile->profesi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $profile->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Daftar</th>
                                <td>{{ $profile->created_at ? $profile->created_at->format('d F Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Disetujui Oleh</th>
                                <td>{{ $profile->approver->name ?? 'Admin' }}
                                    @if($profile->approved_at)
                                        <br><small class="text-muted">{{ $profile->approved_at ? \Carbon\Carbon::parse($profile->approved_at)->format('d F Y H:i') : '' }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($profile->request_status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($profile->request_status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Dokumen -->
                <!-- Dokumen -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            @if($profile->ktp_path)
                                <div class="col-md-6 mb-3">
                                    <h6>Foto KTP</h6>
                                    <button class="btn btn-primary w-100"
                                            data-bs-toggle="modal" data-bs-target="#ktpModal">
                                        <i class="fas fa-eye"></i> Lihat KTP
                                    </button>
                                </div>
                            @endif

                            @if($profile->student_card_path)
                                <div class="col-md-6 mb-3">
                                    <h6>Kartu Pelajar</h6>
                                    <button class="btn btn-primary w-100"
                                            data-bs-toggle="modal" data-bs-target="#studentCardModal">
                                        <i class="fas fa-eye"></i> Lihat Kartu Pelajar
                                    </button>
                                </div>
                            @endif

                            @if(!$profile->ktp_path && !$profile->student_card_path)
                                <div class="col-12 text-center text-muted">
                                    <p>Tidak ada dokumen tersedia</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal KTP -->
    @if($profile->ktp_path)
        <div class="modal fade" id="ktpModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Foto KTP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('storage/' . $profile->ktp_path) }}"
                             class="img-fluid"
                             style="max-height: 85vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- Modal Kartu Pelajar -->
    @if($profile->student_card_path)
        <div class="modal fade" id="studentCardModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kartu Pelajar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('storage/' . $profile->student_card_path) }}"
                             class="img-fluid"
                             style="max-height: 85vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
