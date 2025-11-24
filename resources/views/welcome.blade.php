<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpus Mbolali - Daftar Buku</title>

    {{-- CSS Welcome Page --}}
    <link rel="stylesheet" href="{{ asset('css/welcome-page.css') }}">

    {{-- FontAwesome buat icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="welcome-container">

    {{-- ===== HEADER ===== --}}
    <div class="welcome-header">
        <h1><i class="fas fa-book-open"></i> Perpus Mbolali</h1>
        <p>Jelajahi Koleksi Buku Perpustakaan Kami</p>
    </div>

    {{-- ===== SEARCH BOX ===== --}}
    <div class="search-section">
        <form action="/" method="GET" class="search-form">
            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Cari berdasarkan judul, pengarang, penerbit, kategori, atau tahun..."
                value="{{ request('search') }}"
            >
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    {{-- ===== TABEL BUKU ===== --}}
    <div class="table-section">

        {{-- Kalau ada hasil pencarian, tampilkan info --}}
        @if(request('search'))
            <p style="margin-bottom: 1rem; color: #667eea; font-weight: 600;">
                <i class="fas fa-info-circle"></i>
                Hasil pencarian untuk: "<strong>{{ request('search') }}</strong>"
                ({{ $bukus->total() }} buku ditemukan)
            </p>
        @endif

        @if($bukus->count() > 0)
            <table class="books-table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th>Kategori</th>
                    <th>Tahun</th>
                    <th>Stok</th>
                </tr>
                </thead>
                <tbody>
                @foreach($bukus as $index => $buku)
                    <tr>
                        <td>{{ ($bukus->currentPage() - 1) * $bukus->perPage() + $loop->iteration }}</td>
                        <td><strong>{{ $buku->judul }}</strong></td>
                        <td>{{ $buku->pengarang }}</td>
                        <td>{{ $buku->penerbits->nama_penerbit ?? '-' }}</td>
                        <td>{{ $buku->kategoris->nama_kategori ?? '-' }}</td>
                        <td>{{ $buku->tahun_terbit }}</td>
                        <td>
                            @if($buku->jumlah > 5)
                                <span class="stock-badge stock-available">
                                            {{ $buku->jumlah }} Buku
                                        </span>
                            @elseif($buku->jumlah > 0)
                                <span class="stock-badge stock-low">
                                            {{ $buku->jumlah }} Buku
                                        </span>
                            @else
                                <span class="stock-badge stock-empty">
                                            Habis
                                        </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            {{-- Tampilan kalau ga ada buku --}}
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>Buku Tidak Ditemukan</h3>
                <p>Coba kata kunci lain atau hapus pencarian</p>
                @if(request('search'))
                    <a href="/" style="color: #667eea; text-decoration: none; font-weight: 600; margin-top: 1rem; display: inline-block;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Semua Buku
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- ===== PAGINATION ===== --}}
    @if($bukus->hasPages())
        <div class="pagination-section">
            {{ $bukus->links('pagination::bootstrap-4') }}
        </div>
    @endif


    {{-- ===== FOOTER CTA (Call to Action) ===== --}}
    <div class="cta-section">
        <h2>Bergabunglah dengan Kami!</h2>
        <p>Daftar sekarang untuk meminjam buku dan akses fitur lengkap perpustakaan</p>

        <div class="cta-buttons">
            @guest
                {{-- Kalau belum login, tampilkan tombol register & login --}}
                <a href="{{ route('register') }}" class="btn-cta btn-cta-primary">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </a>
                <a href="{{ route('login') }}" class="btn-cta btn-cta-outline">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            @else
                {{-- Kalau udah login, tampilkan tombol dashboard --}}
                <a href="{{ route('dashboard') }}" class="btn-cta btn-cta-primary">
                    <i class="fas fa-tachometer-alt"></i> Ke Dashboard
                </a>
            @endguest
        </div>
    </div>

</div>
</body>
</html>
