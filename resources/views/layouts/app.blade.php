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
    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <style>
        /* ===========================================
           MODAL PINJAM BUKU - MODERN & ATTRACTIVE
        =========================================== */

        /* Modal Custom */
        .modal-pinjam .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .modal-pinjam .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1.5rem;
        }

        .modal-pinjam .modal-title {
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .modal-pinjam .btn-close {
            filter: invert(1);
        }

        /* Search Box Modern */
        .search-modern {
            border-radius: 50px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .search-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }

        /* Table Modern */
        .table-modern {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .table-modern thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f8f9fa;
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        /* Button Eksemplar - Glow Effect */
        .btn-eksemplar {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255,107,107,0.3);
        }

        .btn-eksemplar:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,107,107,0.4);
            background: linear-gradient(135deg, #ee5a52 0%, #ff6b6b 100%);
        }

        /* Selected Items Card */
        .selected-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 10px 30px rgba(102,126,234,0.3);
        }

        .selected-item {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .remove-btn {
            background: rgba(255,107,107,0.8);
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #ff6b6b;
            transform: scale(1.1);
        }

        /* Eksemplar Modal */
        .eksemplar-list .list-group-item {
            border: none;
            border-radius: 10px;
            margin: 0.25rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }

        .eksemplar-list .list-group-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* Detail Form Modern */
        .form-modern .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1.25rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-modern .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
            background: white;
            transform: translateY(-2px);
        }

        .btn-lanjut {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 700;
            color: white;
            box-shadow: 0 4px 15px rgba(17,153,142,0.3);
            transition: all 0.3s ease;
        }

        .btn-lanjut:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .btn-lanjut:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(17,153,142,0.4);
        }

        /* Animasi Fade In */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Icons */
        .icon-buku { color: #ffd93d; }
        .icon-eksemplar { color: #ff6b6b; }
        .icon-check { color: #38ef7d; }

        /* ===========================================
            🔥 SCROLL HORIZONTAL UNTUK TABEL DI HP
        =========================================== */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto; /* Biar bisa scroll kanan-kiri */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling di iOS */
        }

        /* Biar tabel ga terlalu kecil di HP */
        .table-responsive-wrapper table {
            min-width: 800px; /* Minimal lebar tabel */
            width: 100%;
        }


        /* Styling scrollbar biar keren (opsional) */
        .table-responsive-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }
    </style>
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

            {{-- === Tambahan Menu Daftar Member & Notifikasi Admin === --}}
            @auth
                {{-- ✅ Menu Daftar Member untuk user yang sudah verif tapi belum jadi member --}}
                @if(auth()->user()->is_verified_member && !auth()->user()->is_member)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('member.ask') }}">
                            <i class="bi bi-person-plus"></i> Daftar Member
                        </a>
                    </li>
                @endif

                {{-- ✅ Notifikasi untuk Admin/Petugas jika ada permintaan pendaftaran member --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'petugas')
                    @php
                        $pendingCount = \App\Models\MemberProfile::where('request_status', 'pending')->count();
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('admin.member.requests') }}">
                            <i class="bi bi-envelope"></i>
                            @if($pendingCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $pendingCount }}
                </span>
                            @endif
                        </a>
                    </li>
                @endif
            @endauth
            {{-- === Akhir Tambahan === --}}

        </ul>
        @endif

        <!-- Right navbar -->
        <ul class="navbar-nav ms-auto align-items-center">

            {{-- 🔹 Dropdown Profil --}}

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

                    {{-- ✅ MENU KHUSUS ADMIN & PETUGAS DOANG --}}
                    @if(in_array(Auth::user()->role, ['admin', 'petugas']))

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
                    @endif
                    <div class="menu-title">Sistem</div>

                        <li class="nav-item">
                            <a href="{{ route('profile.edit') }}" class="nav-link {{ Request::is('profile*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user"></i>
                                <p>Profil</p>
                            </a>
                        </li>

                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                    @endif

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
                        <li class="nav-item">
                            <a href="{{ route('admin.member.kelola') }}"
                               class="nav-link {{ Request::is('admin/member/kelola*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-id-card"></i>
                                <p>Kelola Member</p>
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

<script>
    // =====================================================
    // ⚡ AUTO-WRAP SEMUA TABEL (Super Fast!)
    // =====================================================
    (function() {
        function wrapTables() {
            // Cari semua tabel yang belum di-wrap
            document.querySelectorAll('table').forEach(table => {
                // Skip kalau:
                // 1. Udah punya wrapper
                // 2. Ada class .no-scroll
                // 3. Di dalam .no-wrap
                if (table.closest('.table-responsive') ||
                    table.closest('.table-scroll') ||
                    table.closest('.no-scroll') ||
                    table.closest('.no-wrap') ||
                    table.classList.contains('no-scroll')) {
                    return;
                }

                // Buat wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';

                // Wrap tabel
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });
        }

        // Run saat load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', wrapTables);
        } else {
            wrapTables();
        }

        // Run lagi setelah 500ms (untuk tabel dinamis)
        setTimeout(wrapTables, 500);

        // Observer untuk tabel yang muncul via AJAX/modal
        const observer = new MutationObserver(mutations => {
            let hasNewTable = false;
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && (node.tagName === 'TABLE' || node.querySelector('table'))) {
                        hasNewTable = true;
                    }
                });
            });
            if (hasNewTable) {
                setTimeout(wrapTables, 100);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // kasih efek fade pas klik pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function () {
                document.body.style.transition = 'opacity 0.2s ease';
                document.body.style.opacity = '0.5';
            });
        });

        window.addEventListener('pageshow', function () {
            document.body.style.opacity = '1';
        });
    });
</script>



</body>
</html>
