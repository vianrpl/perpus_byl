<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BukusController;
use App\Http\Controllers\BukuItemsController;
use App\Http\Controllers\RaksController;
use App\Http\Controllers\PenerbitsController;
use App\Http\Controllers\LokasiRaksController;
use App\Http\Controllers\KategorisController;
use App\Http\Controllers\SubKategorisController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PenataanBukusController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AdminMemberController;
use App\Models\bukus;

// ============================================================
// 🌐 ROUTE PUBLIK (Boleh Diakses Tanpa Login)
// ============================================================

// Halaman utama (welcome page dengan tabel buku)
// ============================================================
// 🌐 ROUTE PUBLIK (Boleh Diakses Tanpa Login)
// ============================================================

// Halaman utama dengan fitur search
Route::get('/', [BukusController::class, 'welcomeSearch']);

// ============================================================
// 🔐 ROUTE YANG BUTUH LOGIN (Semua Role)
// ============================================================

Route::middleware(['auth'])->group(function () {

    // ✅ DAFTAR BUKU - SEMUA ROLE BISA AKSES (tapi cuma lihat aja)
    Route::get('/bukus', [BukusController::class, 'index'])->name('bukus.index');
    Route::get('/bukus/{buku}', [BukusController::class, 'show'])->name('bukus.show');

    // ✅ PROFIL - SEMUA ROLE BISA EDIT PROFIL SENDIRI
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.update.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ DASHBOARD - SEMUA ROLE PUNYA DASHBOARD SENDIRI
    Route::get('/dashboard', function () {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return view('dashboard.admin');
        } elseif ($role === 'petugas') {
            return view('dashboard.petugas');
        } else {
            return view('dashboard.konsumen'); // Dashboard konsumen
        }
    })->name('dashboard');
});

// ============================================================
// 🔧 ROUTE API HELPER (Buat AJAX & Modal)
// ============================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/get-rak-by-buku/{id_buku}', [PenataanBukusController::class, 'getRakByBuku']);
    Route::get('/raks/{id_rak}', [RaksController::class, 'show'])->name('raks.show');
    Route::get('/get-bukus-for-selection', [BukusController::class, 'getForSelection']);
});

// ============================================================
// 👤 ROUTE MEMBER (Konsumen Daftar Jadi Member)
// ============================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/member/ask', [MemberController::class, 'showAskPage'])->name('member.ask');
    Route::post('/member/send-code', [MemberController::class, 'sendVerificationCode'])->name('member.send_code');
    Route::post('/member/verify-code', [MemberController::class, 'verifyCode'])->name('member.verify_code');
    Route::post('/member/register', [MemberController::class, 'submitRegistration'])->name('member.register');
});

// ============================================================
// 📚 ROUTE PEMINJAMAN (Semua yang Login Bisa Pinjam)
// ============================================================

Route::middleware(['auth'])->group(function () {
    // User kirim permintaan pinjam
    Route::post('/peminjaman/storeRequest', [PeminjamanController::class, 'storeRequest'])->name('peminjaman.storeRequest');

    // API buat modal peminjaman
    Route::get('/peminjaman/search-buku', [PeminjamanController::class, 'searchBuku'])->name('peminjaman.searchBuku');
    Route::get('/peminjaman/bukus', [PeminjamanController::class, 'getBukusForSelection'])->name('peminjaman.getBukus');
    Route::get('/get-eksemplar-by-buku/{id_buku}', [PeminjamanController::class, 'getEksemplarByBuku'])->name('peminjaman.getEksemplar');
    Route::get('/peminjaman/members', [PeminjamanController::class, 'getMembersForSelection'])->name('peminjaman.getMembers');

    // Scan barcode buku
    Route::post('/peminjaman/scan-barcode', [PeminjamanController::class, 'scanBarcode'])->name('peminjaman.scanBarcode');

    // Perpanjang pinjaman
    Route::post('/peminjaman/{id}/perpanjang', [PeminjamanController::class, 'perpanjang'])->name('peminjaman.perpanjang');

    // Lihat peminjaman aktif
    Route::get('/peminjaman/active/{id_member}', [PeminjamanController::class, 'getActiveLoans'])->name('peminjaman.active');

    // List semua peminjaman (admin/petugas lihat semua, konsumen cuma punya dia)
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
});

// ============================================================
// ⚙️ ROUTE ADMIN & PETUGAS SAJA
// ============================================================

Route::middleware(['auth', 'role:admin,petugas'])->group(function () {

    // ✅ CRUD BUKU (khusus admin/petugas)
    Route::post('/bukus', [BukusController::class, 'store'])->name('bukus.store');
    Route::get('/bukus/create', [BukusController::class, 'create'])->name('bukus.create');
    Route::get('/bukus/{buku}/edit', [BukusController::class, 'edit'])->name('bukus.edit');
    Route::put('/bukus/{buku}', [BukusController::class, 'update'])->name('bukus.update');
    Route::delete('/bukus/{buku}', [BukusController::class, 'destroy'])->name('bukus.destroy');

    // ✅ BUKU ITEMS (Eksemplar)
    Route::resource('bukus.items', BukuItemsController::class);
    Route::match(['post', 'delete'], 'bukus/{buku}/items/bulk-delete', [BukuItemsController::class, 'bulkDelete'])->name('bukus.items.bulkDelete');

    // ✅ RAK & LOKASI RAK
    Route::resource('raks', RaksController::class)->except(['show']);
    Route::resource('lokasi_raks', LokasiRaksController::class);

    // ✅ PENERBIT
    Route::resource('penerbits', PenerbitsController::class);
    Route::get('/penerbits/{id}/bukus', [PenerbitsController::class, 'getBukus'])->name('penerbits.bukus');

    // ✅ KATEGORI & SUB KATEGORI
    Route::resource('kategoris', KategorisController::class);
    Route::resource('sub_kategoris', SubKategorisController::class);
    Route::get('/kategoris/{id}/sub-kategoris', [KategorisController::class, 'getSubKategoris'])->name('kategoris.sub_kategoris');
    Route::get('/sub_kategoris/{id}/kategoris', [SubKategorisController::class, 'getKategoris'])->name('sub_kategoris.kategoris');
    Route::get('/sub_kategoris/all-kategoris', [SubKategorisController::class, 'getAllKategoris'])->name('sub_kategoris.all_kategoris');

    // ✅ PENATAAN BUKU
    Route::resource('penataan_bukus', PenataanBukusController::class);

    // ✅ KELOLA PEMINJAMAN (Admin/Petugas)
    Route::post('/peminjaman/{id}/extend', [PeminjamanController::class, 'extend'])->name('peminjaman.extend');
    Route::post('/peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');
    Route::put('/peminjaman/{id}/update', [PeminjamanController::class, 'update'])->name('peminjaman.update');
    Route::delete('/peminjaman/{id}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');

    // ✅ KELOLA MEMBER
    Route::get('/admin/member/requests', [AdminMemberController::class, 'index'])->name('admin.member.requests');
    Route::get('/admin/member/{user}/profile', [AdminMemberController::class, 'showProfile'])->name('admin.member.profile');
    Route::post('/admin/member/requests/{id}/approve', [AdminMemberController::class, 'approve'])->name('admin.member.approve');
    Route::post('/admin/member/requests/{id}/reject', [AdminMemberController::class, 'reject'])->name('admin.member.reject');
    Route::post('/admin/member/send-code/{id}', [AdminMemberController::class, 'sendCodeToUser'])->name('admin.member.send_code_to_user');
    Route::get('/admin/member/kelola', [AdminMemberController::class, 'kelolaMember'])->name('admin.member.kelola');
    Route::post('/admin/member/store', [AdminMemberController::class, 'storeMember'])->name('admin.member.store');
});

// ============================================================
// 👑 ROUTE ADMIN SAJA (Kelola User & Role)
// ============================================================

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::delete('/users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulkDestroy');
});

// ============================================================
// 🔑 ROUTE AUTENTIKASI (Login, Register, dll)
// ============================================================
require __DIR__.'/auth.php';
