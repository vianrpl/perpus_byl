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

Route::get('/get-rak-by-buku/{id_buku}', [App\Http\Controllers\PenataanBukusController::class, 'getRakByBuku']);
Route::get('/raks/{id_rak}', [RaksController::class, 'show'])->name('raks.show');
Route::get('/get-bukus-for-selection', [App\Http\Controllers\BukusController::class, 'getForSelection']);


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
    Route::resource('bukus.items', BukuItemsController::class);
    Route::resource('penataan_bukus', PenataanBukusController::class);

});

// ==========================
// ADMIN SAJA
// ==========================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
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
