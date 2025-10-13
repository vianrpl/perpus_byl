@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4 fw-bold text-center">Dashboard Petugas</h1>
        <p class="text-center text-muted">Halo, {{ Auth::user()->name }}</p>

        {{-- Navbar kecil khusus dashboard --}}
        @if(Request::is('dashboard*'))
            <nav class="navbar navbar-expand-lg shadow-sm mb-4 rounded bg-white">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold">📖 Perpus Mbolali</span>

                    <div class="dropdown ms-auto">
                        <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . Auth::user()->name . '&background=random' }}"
                                 alt="Foto Profil"
                                 class="rounded-circle me-2"
                                 width="32" height="32">
                            {{ Auth::user()->name }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text">Role: {{ Auth::user()->role }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    Edit Profil
                                </a>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        @endif

        {{-- Card Menu --}}
        <div class="row">
            {{-- Daftar Buku --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h4>Lihat Daftar Buku</h4>
                        <p>Semua koleksi buku tersedia</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <a href="{{ route('bukus.index') }}" class="small-box-footer">
                        Lihat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            {{-- Rak --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h4>Rak</h4>
                        <p>Daftar rak penyimpanan buku</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <a href="{{ route('raks.index') }}" class="small-box-footer">
                        Lihat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            {{-- Lokasi Rak --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h4>Lokasi Rak</h4>
                        <p>Lokasi tiap rak buku</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <a href="{{ route('lokasi_raks.index') }}" class="small-box-footer">
                        Lihat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            {{-- Penerbit --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-warning text-white">
                    <div class="inner">
                        <h4>Penerbit</h4>
                        <p>Daftar penerbit buku</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <a href="{{ route('penerbits.index') }}" class="small-box-footer text-white">
                        Lihat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            {{-- Kategori --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4>Kategori</h4>
                        <p>Kategori utama koleksi buku</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <a href="{{ route('kategoris.index') }}" class="small-box-footer">
                        Lihat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            {{-- Sub Kategori --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h4>Sub Kategori</h4>
                        <p>Detail sub kategori buku</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <a href="{{ route('sub_kategoris.index') }}" class="small-box-footer">
                        Lihat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- About Section --}}
        <div class="about-section mt-5 p-4 rounded shadow-sm">
            <h4 class="fw-bold">Tentang Kami</h4>
            <p class="text-muted">
                Perpus Mbolali adalah aplikasi perpustakaan digital yang dibuat untuk memudahkan siapa saja dalam mencari, melihat, dan mengenal koleksi buku.
                Dengan tampilan yang simpel dan mudah dipakai, aplikasi ini diharapkan bisa bikin baca buku jadi lebih seru dan nggak ribet.
            </p>
        </div>
    </div>

    {{-- Custom CSS --}}
    <style>
        body {
            background-color: #f4f6f9; /* abu muda, lembut */
            color: #333;
        }

        .navbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #ddd;
        }

        .card.dashboard-card {
            background-color: #ffffff;
            color: #333;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease-in-out;
        }

        .card.dashboard-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .about-section {
            background-color: #ffffff;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Biar semua small-box teksnya putih */
        .small-box,
        .small-box .inner,
        .small-box .icon,
        .small-box .small-box-footer {
            color: #fff !important;
        }

        /* Hover efek untuk semua small-box */
        .small-box {
            transition: all 0.3s ease-in-out;
            border-radius: 12px; /* biar halus */
        }

        .small-box:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            filter: brightness(1.1); /* warna jadi lebih terang saat hover */
        }
    </style>
@endsection
