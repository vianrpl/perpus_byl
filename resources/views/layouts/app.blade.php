<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Perpus</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- FontAwesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS (optional) -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="hold-transition
    @if(Request::is('dashboard')) layout-top-nav @else sidebar-mini layout-fixed @endif">
<div class="wrapper">


    <!-- Navbar -->
    @if(!Request::is('dashboard'))
    <nav class="main-header navbar navbar-expand navbar-dark bg-dark shadow-sm">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>

            {{-- Hanya tampil kalau BUKAN halaman dashboard --}}
            @if(!Request::is('dashboard'))
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
                </li>
            @endif
        </ul>
        @endif

        <!-- Right navbar -->
        <ul class="navbar-nav ms-auto align-items-center">

            {{-- 🔔 NOTIFIKASI PEMINJAMAN BARU --}}
            @if(in_array(Auth::user()->role, ['admin', 'petugas']))
                @php
                    // ambil jumlah permintaan pending dari tabel peminjaman
                    $notifCount = \App\Models\Peminjaman::where('request_status', 'pending')->count();
                    $notifList = \App\Models\Peminjaman::with(['user', 'item.bukus'])
                        ->where('request_status', 'pending')
                        ->latest('id_peminjaman') // 🟢 ganti urut pakai id)
                        ->take(5)
                        ->get();
                @endphp

                <li class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        @if($notifCount > 0)
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                        {{ $notifCount }}
                    </span>
                        @endif
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li class="dropdown-header bg-light fw-bold text-center">
                            Notifikasi Peminjaman
                        </li>
                        @forelse($notifList as $req)
                            <li>
                                <a href="{{ route('peminjaman.requests') }}" class="dropdown-item">
                                    <i class="fas fa-book text-primary me-2"></i>
                                    <strong>{{ $req->user->name }}</strong> minta pinjam
                                    <em>{{ $req->bukuItem->bukus->judul ?? 'Buku' }}</em>
                                </a>
                            </li>
                        @empty
                            <li><span class="dropdown-item text-muted text-center">Tidak ada permintaan baru</span></li>
                        @endforelse
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="{{ route('peminjaman.requests') }}" class="dropdown-item text-center text-primary">
                                <i class="fas fa-eye"></i> Lihat semua
                            </a></li>
                    </ul>
                </li>
            @endif

            {{-- 🔹 Dropdown Profil --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user"></i> Profil
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    @if(!Request::is('dashboard'))
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link text-center">
            <i class="fas fa-book-open"></i>
            <span class="brand-text fw-bold">Perpus Mbolali</span>
        </a>

        <div class="sidebar">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('bukus.index') }}" class="nav-link {{ Request::is('bukus*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Daftar Buku</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('penerbits.index') }}" class="nav-link {{ Request::is('penerbits*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Penerbit</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::is('raks*') || Request::is('lokasi_raks*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-archive"></i>
                            <p>Rak <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview ms-3">
                            <li class="nav-item">
                                <a href="{{ route('raks.index') }}" class="nav-link {{ Request::is('raks*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Data Rak</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lokasi_raks.index') }}" class="nav-link {{ Request::is('lokasi_raks*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Lokasi Rak</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item {{ Request::is('kategoris*') || Request::is('sub_kategoris*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Kategori <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview ms-3">
                            <li class="nav-item">
                                <a href="{{ route('kategoris.index') }}" class="nav-link {{ Request::is('kategoris*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kategori</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('sub_kategoris.index') }}" class="nav-link {{ Request::is('sub_kategoris*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Sub Kategori</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <div class="menu-title">Sistem</div>

                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ Request::is('profile*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Profil</p>
                        </a>
                    </li>
                    @if(in_array(Auth::user()->role, ['admin', 'petugas']))
                    <div class="menu-title">Transaksi</div>
                    <li class="nav-item">
                        <a href="{{ route('penataan_bukus.index') }}" class="nav-link {{ Request::is('penataan*') ? 'active' : '' }}">
                            <i class="far fa-clipboard nav-icon"></i>
                            <p>Penataan Buku</p>
                        </a>
                    </li>
                        <li class="nav-item">
                        <a href="{{ route('peminjaman.index') }}" class="nav-link {{ Request::is('peminjaman*') ? 'active' : '' }}">
                            <i class="far fa-envelope nav-icon"></i>
                            <p>Peminjaman</p>
                        </a>
                    </li>
                    @endif


                </ul>
            </nav>
        </div>
    </aside>
    @endif

    <!-- Content -->
    <div class="content-wrapper">
        <section class="content pt-4">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer text-center small">
        <strong>© 2025 Perpus Mbolali</strong> - All rights reserved.
    </footer>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="{{ asset('js/custom.js') }}"></script>
@stack('scripts')

</body>
</html>
