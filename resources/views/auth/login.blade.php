{{-- FILE: resources/views/auth/login.blade.php --}}
    <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpus Mbolali</title>

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
                <i class="fas fa-book-open"></i>
            </div>
            <h1 class="auth-title">Selamat Datang</h1>
            <p class="auth-subtitle">Login ke Perpus Mbolali</p>
        </div>

        {{-- Tampilkan pesan sukses (misal: habis reset password) --}}
        @if (session('status'))
            <div style="background: #d1fae5; color: #065f46; padding: 0.875rem 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

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
                    autofocus
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
                    placeholder="Masukkan password"
                    required
                >
                @error('password')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="remember-me">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me" style="margin-bottom: 0; font-weight: 400;">Ingat Saya</label>
            </div>

            {{-- Tombol Login --}}
            <button type="submit" class="btn-auth-primary">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>

            {{-- Link Lupa Password --}}
            @if (Route::has('password.request'))
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="{{ route('password.request') }}" class="auth-link">
                        Lupa Password?
                    </a>
                </div>
            @endif
        </form>

        {{-- Footer: Link ke Register --}}
        <div class="auth-footer">
            Belum punya akun?
            <a href="{{ route('register') }}" class="auth-link">Daftar Sekarang</a>
        </div>
    </div>
</div>
</body>
</html>
