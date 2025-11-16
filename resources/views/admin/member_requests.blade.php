@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Permintaan Pendaftaran Member</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>KTP</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($profiles as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ $p->user->email }}</td>
                    <td>{{ $p->user->no_hp }}</td>
                    <td>{{ $p->nama_lengkap }}</td>
                    <td>{{ Str::limit($p->alamat, 50) }}</td>
                    <td class="text-center">
                        @if($p->ktp_path || $p->kartu_pelajar_path)
                            <!-- Tombol buka modal -->
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#identitasModal{{ $p->id }}">
                                <i class="bi bi-card-image"></i> Lihat
                            </button>

                            <!-- Modal Preview -->
                            <div class="modal fade" id="identitasModal{{ $p->id }}" tabindex="-1" aria-labelledby="identitasModalLabel{{ $p->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="identitasModalLabel{{ $p->id }}">
                                                Identitas {{ $p->user->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">

                                            @if($p->ktp_path)
                                                <p class="fw-bold mb-2">KTP</p>
                                                <img src="{{ asset('storage/'.$p->ktp_path) }}"
                                                     class="ktp-image img-fluid rounded shadow mb-4"
                                                     alt="KTP {{ $p->user->name }}">
                                            @endif

                                            @if($p->kartu_pelajar_path)
                                                <p class="fw-bold mb-2">Kartu Pelajar</p>
                                                <img src="{{ asset('storage/'.$p->kartu_pelajar_path) }}"
                                                     class="ktp-image img-fluid rounded shadow"
                                                     alt="Kartu Pelajar {{ $p->user->name }}">
                                            @endif

                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            @if($p->ktp_path)
                                                <a href="{{ asset('storage/'.$p->ktp_path) }}" download class="btn btn-success">
                                                    <i class="bi bi-download"></i> Unduh KTP
                                                </a>
                                            @elseif($p->kartu_pelajar_path)
                                                <a href="{{ asset('storage/'.$p->kartu_pelajar_path) }}" download class="btn btn-success">
                                                    <i class="bi bi-download"></i> Unduh Kartu Pelajar
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted fst-italic">Belum diunggah</span>
                        @endif
                    </td>

                    <td>{{ ucfirst($p->request_status) }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.member.approve', $p->id) }}" class="d-inline">@csrf
                            <button class="btn btn-success btn-sm">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.member.reject', $p->id) }}" class="d-inline">@csrf
                            <button class="btn btn-danger btn-sm">Tolak</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Belum ada permintaan</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('styles')
    <style>
        .ktp-image {
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }
        .ktp-image:hover {
            transform: scale(1.05);
        }
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('ktp-image')) {
                const src = e.target.getAttribute('src');
                const overlay = document.createElement('div');
                overlay.style.position = 'fixed';
                overlay.style.top = 0;
                overlay.style.left = 0;
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.85)';
                overlay.style.display = 'flex';
                overlay.style.alignItems = 'center';
                overlay.style.justifyContent = 'center';
                overlay.style.zIndex = 1055;
                overlay.innerHTML = `<img src="${src}" style="max-width:90%; max-height:90%; border-radius:10px; box-shadow:0 0 20px rgba(255,255,255,0.3)">`;
                overlay.addEventListener('click', () => overlay.remove());
                document.body.appendChild(overlay);
            }
        });
    </script>
@endpush

