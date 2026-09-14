<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->role === 'admin') {
        return app(AdminUserController::class)->dashboard();
    }

    if (auth()->check() && auth()->user()->role === 'operator') {
        return redirect()->route('pengaduan.tickets');
    }

    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/layanan-permohonan', [PengaduanController::class, 'create'])->name('pengaduan.create');
    Route::post('/layanan-permohonan', [PengaduanController::class, 'store'])->name('pengaduan.store');
    Route::get('/tiket-saya', [PengaduanController::class, 'tickets'])->name('pengaduan.tickets');
    Route::get('/tiket-saya/{pengaduan}', [PengaduanController::class, 'showTicket'])->name('pengaduan.show');
    Route::get('/tiket-saya/{pengaduan}/bukti', [PengaduanController::class, 'evidence'])->name('pengaduan.evidence');
    Route::patch('/tiket-saya/{pengaduan}/status', [PengaduanController::class, 'updateStatus'])->name('pengaduan.status');
    Route::patch('/tiket-saya/{pengaduan}/tanggapan', [PengaduanController::class, 'respond'])->name('pengaduan.respond');

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin-panel')->group(function () {
        Route::get('/dashboard', [AdminUserController::class, 'dashboard'])->name('dashboard');
        Route::get('/laporan', [AdminUserController::class, 'reports'])->name('reports');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    });
});

require __DIR__.'/auth.php';
