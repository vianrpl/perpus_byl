{{-- FILE: resources/views/auth/register.blade.php --}}
    <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Perpus Mbolali</title>

    {{-- CSS Auth Page --}}
    <link rel="stylesheet" href="{{ asset('css/auth-page.css') }}">

    {{-- FontAwesome buat icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        {{-- Header dengan logo buku --}}
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 class="auth-title">Daftar Akun Baru</h1>
            <p class="auth-subtitle">Bergabung dengan Perpus Mbolali</p>
        </div>

        {{-- Form Register --}}
        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            {{-- Nama Lengkap --}}
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Nama lengkap kamu"
                    required
                    autofocus
                >
                @error('name')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@example.com"
                    required
                >
                @error('email')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    required
                >
                @error('password')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Ketik ulang password"
                    required
                >
                @error('password_confirmation')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tombol Daftar --}}
            <button type="submit" class="btn-auth-primary">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>

        {{-- Footer: Link ke Login --}}
        <div class="auth-footer">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="auth-link">Login di sini</a>
        </div>
    </div>
</div>
</body>
</html>
