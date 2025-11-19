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

Route::get('/get-rak-by-buku/{id_buku}', [App\Http\Controllers\PenataanBukusController::class, 'getRakByBuku']);
Route::get('/raks/{id_rak}', [RaksController::class, 'show'])->name('raks.show');
Route::get('/get-bukus-for-selection', [App\Http\Controllers\BukusController::class, 'getForSelection']);

//Route member
Route::middleware(['auth'])->group(function () {
    Route::get('/member/ask', [MemberController::class, 'showAskPage'])->name('member.ask');
    Route::post('/member/send-code', [MemberController::class, 'sendVerificationCode'])->name('member.send_code');
    Route::post('/member/verify-code', [MemberController::class, 'verifyCode'])->name('member.verify_code');

    Route::post('/member/register', [MemberController::class, 'submitRegistration'])->name('member.register');

    Route::middleware(['role:admin,petugas'])->group(function () {
        Route::get('/admin/member/requests', [AdminMemberController::class, 'index'])->name('admin.member.requests');
        //untuk liat data diri
        Route::get('/admin/member/{user}/profile', [App\Http\Controllers\AdminMemberController::class, 'showProfile'])->name('admin.member.profile');
        Route::post('/admin/member/requests/{id}/approve', [AdminMemberController::class, 'approve'])->name('admin.member.approve');
        Route::post('/admin/member/requests/{id}/reject', [AdminMemberController::class, 'reject'])->name('admin.member.reject');
        Route::post('/admin/member/send-code/{id}', [AdminMemberController::class, 'sendCodeToUser'])->name('admin.member.send_code_to_user');
    });
});

// ==========================
// ROUTE PEMINJAMAN
// ==========================
Route::middleware(['auth'])->group(function () {
    // ==========================
    // USER (KIRIM PERMINTAAN PINJAM)
    // ==========================
    Route::post('/peminjaman/storeRequest', [PeminjamanController::class, 'storeRequest'])
        ->name('peminjaman.storeRequest');

    // routes/web.php
    Route::get('/peminjaman/search-buku', [PeminjamanController::class, 'searchBuku'])->name('peminjaman.searchBuku');
    Route::get('/peminjaman/bukus', [PeminjamanController::class, 'getBukusForSelection'])->name('peminjaman.getBukus');
    Route::get('/get-eksemplar-by-buku/{id_buku}', [PeminjamanController::class, 'getEksemplarByBuku'])->name('peminjaman.getEksemplar');
    Route::get('/peminjaman/members', [PeminjamanController::class, 'getMembersForSelection'])->name('peminjaman.getMembers');
    // ==========================
    // ADMIN / PETUGAS (KELOLA REQUEST)
    // ==========================

    Route::post('/peminjaman/{id}/extend', [PeminjamanController::class, 'extend'])->middleware('can:isStaff')->name('peminjaman.extend');
    Route::post('/peminjaman/{id}/perpanjang', [PeminjamanController::class, 'perpanjang'])->name('peminjaman.perpanjang');


    // ==========================
    // KEMBALIKAN BUKU
    // ==========================
    Route::post('/peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan'])
        ->middleware('can:isStaff')
        ->name('peminjaman.kembalikan');

    // ✅ TAMBAHKAN ROUTE UPDATE INI BIAR ERRORMU HILANG
    // ini dipakai waktu admin/petugas menyetujui request dari view yang pakai route('peminjaman.update')
    Route::put('/peminjaman/{id}/update', [PeminjamanController::class, 'update'])
        ->middleware('can:isStaff')
        ->name('peminjaman.update');

    // ==========================
    // RESOURCE INDEX (LIST PEMINJAMAN)
    // ==========================
    // BENAR: DELETE DULUAN, BARU GET
    Route::delete('/peminjaman/{id}', [PeminjamanController::class, 'destroy'])
        ->name('peminjaman.destroy');

    Route::get('/peminjaman', [PeminjamanController::class, 'index'])
        ->name('peminjaman.index');

    Route::get('/peminjaman/active/{id_member}', [PeminjamanController::class, 'getActiveLoans'])->name('peminjaman.active');
});


// ==========================
// ROUTE UMUM
// ==========================
Route::get('/', function () {
    return view('welcome');
});



// ==========================
// ADMIN & PETUGAS
// ==========================
Route::middleware(['auth', 'role:admin,petugas,konsumen'])->group(function () {
    Route::resource('bukus', BukusController::class);
    Route::resource('raks', RaksController::class);
    Route::resource('penerbits', PenerbitsController::class);
    Route::resource('lokasi_raks', LokasiRaksController::class);
    Route::resource('kategoris', KategorisController::class);
    Route::resource('sub_kategoris', SubKategorisController::class);
    //bulk
    Route::match(['post', 'delete'], 'bukus/{buku}/items/bulk-delete', [BukuItemsController::class, 'bulkDelete'])
        ->name('bukus.items.bulkDelete');

    Route::resource('bukus.items', BukuItemsController::class);

    Route::resource('penataan_bukus', PenataanBukusController::class);

});

// ==========================
// ADMIN SAJA
// ==========================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    //bulk
    Route::delete('/users/bulk-destroy', [App\Http\Controllers\UserController::class, 'bulkDestroy'])->name('users.bulkDestroy');
});



// ==========================
// ROUTE PROFIL (SEMUA YANG LOGIN)
// ==========================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.update.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================
// AUTH & DASHBOARD
// ==========================
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return view('dashboard.admin');
        } elseif ($role === 'petugas') {
            return view('dashboard.petugas');
        } else {
            return view('dashboard.konsumen');
        }
    })->name('dashboard');
});
