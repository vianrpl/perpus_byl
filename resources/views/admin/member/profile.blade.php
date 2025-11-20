@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-id-card"></i> Data Diri Member</h3>
            <div>
                {{-- Tombol Lihat Kartu --}}
                @if($user->is_member && $profile->no_member)
                    <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#kartuMemberModal">
                        <i class="fas fa-id-card-alt"></i> Lihat Kartu
                    </button>
                @endif
                <a href="{{ route('admin.member.kelola') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Kartu Member Preview --}}
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="mb-0">Preview Kartu Member</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($profile->foto_3x4)
                            <img src="{{ asset('storage/'.$profile->foto_3x4) }}"
                                 style="width: 90px; height: 120px; object-fit: cover; border-radius: 8px; border: 3px solid #667eea;">
                        @else
                            <div class="bg-light p-5 mb-3 rounded">
                                <i class="fas fa-user fa-5x text-muted"></i>
                            </div>
                        @endif

                        <h5 class="mb-2 mt-3">{{ $profile->nama_lengkap }}</h5>

                        @if($profile->no_member)
                            <div class="alert alert-success mb-3">
                                <strong>No. Member:</strong><br>
                                <h4 class="mb-0 font-monospace">{{ $profile->no_member }}</h4>
                            </div>

                            {{-- Barcode --}}
                            <div class="bg-white p-3 border rounded">
                                <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $profile->no_member }}&code=Code128&translate-esc=on"
                                     alt="Barcode" class="img-fluid" style="max-height: 60px;">
                            </div>
                        @endif

                        <div class="mt-3">
                            <span class="badge bg-success">Member Aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Informasi --}}
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Member</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Nama Lengkap</th>
                                <td>{{ $profile->nama_lengkap }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Nomor HP</th>
                                <td>{{ $profile->no_hp }}</td>
                            </tr>
                            <tr>
                                <th>Profesi</th>
                                <td>{{ $profile->profesi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $profile->alamat }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Daftar</th>
                                <td>{{ $profile->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Disetujui Oleh</th>
                                <td>{{ $profile->approver->name ?? 'Admin' }}
                                    @if($profile->approved_at)
                                        <br><small class="text-muted">
                                            {{ \Carbon\Carbon::parse($profile->approved_at)->format('d F Y H:i') }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-success">Approved</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Dokumen --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($profile->ktp_path)
                                <div class="col-md-6 mb-3">
                                    <h6>Foto KTP</h6>
                                    <button class="btn btn-primary btn-sm w-100"
                                            data-bs-toggle="modal" data-bs-target="#ktpModal">
                                        <i class="fas fa-eye"></i> Lihat KTP
                                    </button>
                                </div>
                            @endif

                            @if($profile->student_card_path)
                                <div class="col-md-6 mb-3">
                                    <h6>Kartu Pelajar</h6>
                                    <button class="btn btn-primary btn-sm w-100"
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

    {{-- Modal KTP --}}
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
                             class="img-fluid" style="max-height: 80vh;">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Kartu Pelajar --}}
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
                             class="img-fluid" style="max-height: 80vh;">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Kartu Member Digital --}}
    @include('admin.member.partials.kartu_member_modal')

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Load data ke kartu saat modal dibuka
                var kartuModal = document.getElementById('kartuMemberModal');
                if (kartuModal) {
                    kartuModal.addEventListener('show.bs.modal', function () {
                        loadMemberData({
                            nama: '{{ $profile->nama_lengkap }}',
                            email: '{{ $user->email }}',
                            no_hp: '{{ $profile->no_hp }}',
                            nomor_member: '{{ $profile->no_member }}',
                            foto: '{{ $profile->foto_3x4 ? asset("storage/".$profile->foto_3x4) : "https://ui-avatars.com/api/?name=".$user->name."&size=100" }}'
                        });
                    });
                }
            });
        </script>
    @endpush

@endsection
