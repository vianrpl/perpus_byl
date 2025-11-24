{{-- FILE: resources/views/dashboard/konsumen.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="dashboard-wrapper">
        {{-- Background dengan pattern buku --}}
        <div class="dashboard-bg"></div>

        <div class="container position-relative">
            {{-- Header Section --}}
            <div class="dashboard-header text-center mb-4">
                <h1 class="fw-bold text-white mb-2">
                    <i class="fas fa-user me-2"></i>Dashboard Member
                </h1>
                <p class="text-white-50 mb-0">Selamat datang, {{ Auth::user()->name }}</p>
            </div>

            {{-- Navbar Dashboard dengan DateTime --}}
            <nav class="navbar navbar-expand-lg dashboard-navbar shadow mb-4 rounded-3">
                <div class="container-fluid">
                <span class="navbar-brand fw-bold">
                    <i class="fas fa-book-open text-primary me-2"></i>
                    Perpus Mbolali
                </span>

                    {{-- DateTime Display --}}
                    <div class="datetime-display d-none d-md-flex align-items-center me-3">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <span id="currentDateTime" class="text-muted fw-medium"></span>
                    </div>

                    <div class="dropdown ms-auto">
                        <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" href="#"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=4facfe&color=fff' }}"
                                 alt="Foto Profil" class="rounded-circle me-2" width="32" height="32">
                            <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                            {{-- Badge Status Member --}}
                            @if(Auth::user()->is_member)
                                <span class="badge bg-success ms-2">Member Aktif</span>
                            @elseif(Auth::user()->is_verified_member)
                                <span class="badge bg-warning ms-2">Terverifikasi</span>
                            @else
                                <span class="badge bg-secondary ms-2">Non-Member</span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li class="dropdown-header">
                                <small class="text-muted">Role: {{ ucfirst(Auth::user()->role) }}</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <i class="fas fa-user-edit me-2 text-primary"></i> Edit Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            {{-- Cards Row dengan gap (g-4 = 24px jarak) --}}
            {{-- ✅ CARDS ROW - CUMA DAFTAR BUKU & PROFIL --}}
            <div class="row g-4 mb-5 justify-content-center">

                {{-- 📚 Daftar Buku --}}
                <div class="col-lg-6 col-md-6">
                    <div class="dashboard-card card-gradient-primary h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Daftar Buku</h5>
                                    <p class="card-text text-white-50 small mb-0">Lihat semua koleksi buku perpustakaan</p>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('bukus.index') }}" class="btn btn-light btn-sm rounded-pill">
                                    Lihat Koleksi <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 👤 Profil --}}
                <div class="col-lg-6 col-md-6">
                    <div class="dashboard-card card-gradient-success h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Profil Saya</h5>
                                    <p class="card-text text-white-50 small mb-0">Kelola informasi akun kamu</p>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm rounded-pill">
                                    Lihat Profil <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- About Section --}}
            <div class="about-section-new rounded-4 shadow-lg p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-info-circle text-primary me-2"></i>Tentang Kami
                        </h4>
                        <p class="text-muted mb-0">
                            Perpus Mbolali adalah aplikasi perpustakaan digital yang dibuat untuk memudahkan siapa saja
                            dalam mencari, melihat, dan mengenal koleksi buku. Dengan tampilan yang simpel dan mudah dipakai,
                            aplikasi ini diharapkan bisa bikin baca buku jadi lebih seru dan nggak ribet.
                        </p>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="fas fa-book-reader text-primary" style="font-size: 5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Fixed di Bawah --}}

    {{-- Include Styles & Script dari Partial --}}
    @include('dashboard.partials.dashboard-styles')
@endsection
