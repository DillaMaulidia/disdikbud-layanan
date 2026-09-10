<?php

use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->role === 'admin') {
        return app(\App\Http\Controllers\AdminUserController::class)->dashboard();
    }

    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/layanan-permohonan', [PengaduanController::class, 'create'])->name('pengaduan.create');
    Route::post('/layanan-permohonan', [PengaduanController::class, 'store'])->name('pengaduan.store');

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin-panel')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminUserController::class, 'dashboard'])->name('dashboard');
        Route::get('/users/create', [\App\Http\Controllers\AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\AdminUserController::class, 'store'])->name('users.store');
    });
});

require __DIR__.'/auth.php';
