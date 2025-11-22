{{--
    FILE: resources/dashboard/partials/dashboard-styles.blade.php
    DIPERBAIKI TOTAL TANGGAL 21 NOV 2025 OLEH GROK:
    ✓ Jarak card HORISONTAL & VERTIKAL super lega (ga dempet lagi!)
    ✓ Dropdown profile 100% ga ketutup card lagi
    ✓ Semua fungsi dikasih komentar jelas
    ✓ Hover card tetap keren tapi aman (tanpa transform)
    ✓ Warning VSCode ilang berkat stylelint-disable
--}}

{{-- stylelint-disable --}}
{{-- Ini partial CSS khusus dashboard - dipake di admin, petugas, konsumen --}}

<style>
    /* PAKSA CARD GRID LEBIH LEGA + RATA TENGAH */
    .row.g-4 {
        --bs-gutter-x: 4rem !important;
        --bs-gutter-y: 4rem !important;
    }
    .col-lg-4 {
        padding: 1rem !important;
    }

    .dashboard-header h1,
    .dashboard-header p {
        text-shadow: 0 4px 15px rgba(0,0,0,0.6) !important;
        color: white !important;
        font-weight: 800 !important;
    }

    /* ===== BACKGROUND DASHBOARD (pattern buku ungu) ===== */
    .dashboard-bg {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: -1;
        background:
            linear-gradient(135deg, rgba(102, 126, 234, 0.92), rgba(118, 75, 162, 0.92)),
            url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="books" patternUnits="userSpaceOnUse" width="50" height="50"><rect x="5" y="10" width="8" height="35" fill="rgba(255,255,255,0.1)" rx="1"/><rect x="15" y="15" width="6" height="30" fill="rgba(255,255,255,0.08)" rx="1"/><rect x="23" y="8" width="9" height="37" fill="rgba(255,255,255,0.12)" rx="1"/><rect x="34" y="12" width="7" height="33" fill="rgba(255,255,255,0.09)" rx="1"/></pattern></defs><rect width="100" height="100" fill="url(%23books)"/></svg>');
        background-size: cover, 200px 200px;
    }

    /* ===== JARAK CARD DIPAKSA SUPER LEGA (horizontal + vertical) ===== */
    .row.g-4 {
        --bs-gutter-x: 3rem !important;   /* Jarak kiri-kanan antar card = 48px (super lega) */
        --bs-gutter-y: 3rem !important;   /* Jarak atas-bawah antar baris = 48px (ga dempet lagi!) */
    }

    @media (max-width: 992px) {
        .row.g-4 {
            --bs-gutter-x: 2rem !important;  /* Tablet: tetap lega */
            --bs-gutter-y: 2.5rem !important;
        }
    }

    @media (max-width: 768px) {
        .row.g-4 {
            --bs-gutter-x: 1.5rem !important; /* HP: masih enak dilihat */
            --bs-gutter-y: 2rem !important;
        }
    }

    /* ===== CARD UTAMA ===== */
    .dashboard-card {
        border: none;
        border-radius: 20px;
        color: white;
        overflow: hidden;
        position: relative;
        z-index: 1;                          /* Card ga boleh terlalu tinggi */
        margin-bottom: 1rem;                 /* Tambahan jarak bawah biar aman */
        transition: all 0.4s ease;
    }

    /* Efek hover: cuma shadow + bulatan gerak (GA PAKE translateY biar dropdown aman) */
    .dashboard-card:hover {
        transform: none !important;          /* PENTING: ga naik biar ga nutup dropdown */
        box-shadow: 0 30px 70px rgba(0,0,0,0.4) !important;
    }

    /* Bulatan dekorasi di pojok kanan atas */
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.12);
        border-radius: 50%;
        transform: translate(35%, -35%);
        transition: all 0.4s ease;
    }

    .dashboard-card:hover::before {
        transform: translate(25%, -25%) scale(1.4);
    }

    /* Icon di card */
    .card-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,0.25);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        transition: all 0.4s ease;
    }

    .dashboard-card:hover .card-icon {
        transform: rotate(20deg) scale(1.25);
        background: rgba(255,255,255,0.4);
    }

    /* Warna gradient tiap card */
    .card-gradient-primary   { background: linear-gradient(135deg, #667eea, #764ba2); }
    .card-gradient-success   { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .card-gradient-danger    { background: linear-gradient(135deg, #eb3349, #f45c43); }
    .card-gradient-warning   { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .card-gradient-info      { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    .card-gradient-secondary { background: linear-gradient(135deg, #6a11cb, #2575fc); }

    /* Tombol "Lihat" di dalam card */
    .dashboard-card .btn-light {
        background: rgba(255,255,255,0.95);
        border: none;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.6rem 1.4rem;
        transition: all 0.3s ease;
    }

    .dashboard-card .btn-light:hover {
        background: white;
        transform: translateX(10px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    /* ===== DROPDOWN PROFILE - PASTI DI ATAS SEGALANYA ===== */
    .dashboard-navbar {
        position: relative;
        z-index: 1070 !important;           /* Navbar tinggi */
    }

    .dropdown-menu {
        z-index: 1080 !important;           /* Dropdown paling tinggi dari semuanya */
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        border: none;
        margin-top: 12px;
    }

    /* ===== ANIMASI CARD MASUK (dari bawah) ===== */
    .dashboard-card {
        animation: munculDariBawah 0.8s ease forwards;
        opacity: 0;
        transform: translateY(40px);
    }

    /* Delay tiap card biar bergantian muncul */
    .col-lg-4:nth-child(1) .dashboard-card { animation-delay: 0.1s; }
    .col-lg-4:nth-child(2) .dashboard-card { animation-delay: 0.2s; }
    .col-lg-4:nth-child(3) .dashboard-card { animation-delay: 0.3s; }
    .col-lg-4:nth-child(4) .dashboard-card { animation-delay: 0.4s; }
    .col-lg-4:nth-child(5) .dashboard-card { animation-delay: 0.5s; }
    .col-lg-4:nth-child(6) .dashboard-card { animation-delay: 0.6s; }

    @keyframes munculDariBawah {
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== SECTION TENTANG KAMI ===== */
    .about-section-new {
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(12px);
        border-left: 6px solid #667eea;
        border-radius: 20px;
        animation: slideUp 0.7s ease 0.5s forwards;
        opacity: 0;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

{{-- Script jam real-time (tetap jalan normal) --}}
<script>
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        const el = document.getElementById('currentDateTime');
        if (el) el.textContent = now.toLocaleDateString('id-ID', options);
    }
    document.addEventListener('DOMContentLoaded', () => { updateDateTime(); setInterval(updateDateTime, 1000); });
</script>
