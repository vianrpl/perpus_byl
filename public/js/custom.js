// public/js/custom.js

document.addEventListener('DOMContentLoaded', function() {
    // 1) beri class .fade-in pada tabel saat load sudah di layout (CSS melakukan animasi)
    // 2) beri efek stagger pada tiap baris <tbody> jika ada
    const tables = document.querySelectorAll('table.fade-in');
    tables.forEach(tbl => {
        // tambahkan kelas fade-in sudah di CSS
        // sekarang stagger rows:
        const rows = tbl.querySelectorAll('tbody tr');
        rows.forEach((r, i) => {
            r.classList.add('row-appear');
            // berikan delay bertahap, misal 40ms per baris
            r.style.animationDelay = (i * 0.04) + 's';
        });
    });

    // tombol: optional - klik tombol beri efek kecil (ripple-like)
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mousedown', () => {
            btn.style.transform = 'scale(0.99)';
        });
        btn.addEventListener('mouseup', () => {
            btn.style.transform = '';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
});
