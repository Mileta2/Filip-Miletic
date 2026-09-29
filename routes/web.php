<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/prijava', [LoginController::class, 'create'])->name('login');
    Route::post('/prijava', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/odjava', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/promena-lozinke', [PasswordController::class, 'edit'])->name('password.change');
    Route::put('/promena-lozinke', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/kontrolna-tabla', DashboardController::class)->name('dashboard');

        Route::view('/admin', 'dashboards.admin')
            ->middleware('role:super_admin')->name('admin.dashboard');
        Route::view('/profesor', 'dashboards.professor')
            ->middleware('role:professor')->name('professor.dashboard');
        Route::view('/student', 'dashboards.student')
            ->middleware('role:student')->name('student.dashboard');
    });
});
