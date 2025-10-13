@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4 fw-bold text-center">Edit Profil</h1>
    <style>
        /* Header card full biru */
        .card-header {
            background: #0d6efd;
            padding: 0;
            border-bottom: none;
        }

        /* Tab nav clean */
        .nav-tabs {
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            color: #fff;
            font-weight: 500;
            border: none;
            border-radius: 0;
            padding: 10px 20px;
            transition: background 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        /* Tab aktif */
        .nav-tabs .nav-link.active {
            background: #084298;
            color: #fff;
        }

        /* Card body biar nyatu */
        .card-body {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 20px;
        }
    </style>

    <div class="container">
        <div class="card shadow-sm">
            {{-- Global Flash Message --}}
            @if (session('status'))
                <div id="global-alert"
                     class="alert alert-success text-center mx-auto mt-3"
                     style="max-width: 600px;">
                    @if (session('status') === 'profile-updated')
                        ✅ Profil berhasil diperbarui.
                    @elseif (session('status') === 'password-updated')
                        🔑 Password berhasil diperbarui.
                    @elseif (session('status') === 'photo-updated')
                        📸 Foto profil berhasil diperbarui.
                    @elseif (session('status') === 'account-deleted')
                        ❌ Akun berhasil dihapus.
                    @else
                        {{ session('status') }}
                    @endif
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        let alert = document.getElementById('global-alert');
                        if (alert) {
                            setTimeout(() => {
                                alert.style.transition = "opacity 0.5s ease";
                                alert.style.opacity = 0;
                                setTimeout(() => alert.remove(), 500);
                            }, 3000); // hilang setelah 3 detik
                        }
                    });
                </script>
            @endif

            <div class="card-header">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#foto">Foto Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#info">Email</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#password">Password</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#hapus">Hapus Akun</a>
                    </li>
                </ul>
            </div>
            <div class="card-body tab-content">
                {{-- Tab Foto Profil --}}

                <div class="tab-pane fade show active" id="foto">
                    <div class="text-center mb-3">
                        <img src="{{ $user->photo ? asset('storage/'.$user->photo) : 'https://ui-avatars.com/api/?name='.$user->name.'&background=random' }}"
                             class="rounded-circle mb-3" width="125" height="125" alt="Foto Profil">
                    </div>
                    <form action="{{ route('profile.update.photo') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <input type="file" name="photo" class="form-control mb-3" accept="image/*">
                        <button type="submit" class="btn btn-primary w-100">Update Foto</button>
                    </form>
                </div>

                {{-- Tab Informasi --}}
                <div class="tab-pane fade" id="info">
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Tab Password --}}
                <div class="tab-pane fade" id="password">
                    @include('profile.partials.update-password-form')
                </div>

                {{-- Tab Hapus Akun --}}
                <div class="tab-pane fade" id="hapus">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
