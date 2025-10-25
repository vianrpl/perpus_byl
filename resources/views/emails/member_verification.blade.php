<!DOCTYPE html>
<html>
<body>
    <p>Halo {{ $user->name }},</p>
    <p>Berikut kode verifikasi pendaftaran member Anda:</p>
    <h2>{{ $code }}</h2>
    <p>Kode berlaku selama 30 menit.</p>
    <p>Terima kasih,<br>Perpustakaan Boyolali</p>
</body>
</html>
