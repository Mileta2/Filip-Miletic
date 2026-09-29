<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfessorController as AdminProfessorController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
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

        Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::resource('studenti', AdminStudentController::class)
                ->parameters(['studenti' => 'student'])->names('students')->except('destroy');
            Route::put('/studenti/{student}/lozinka', [AdminStudentController::class, 'resetPassword'])->name('students.password');
            Route::patch('/studenti/{student}/status', [AdminStudentController::class, 'toggle'])->name('students.toggle');

            Route::resource('profesori', AdminProfessorController::class)
                ->parameters(['profesori' => 'professor'])->names('professors');
            Route::put('/profesori/{professor}/lozinka', [AdminProfessorController::class, 'resetPassword'])->name('professors.password');
            Route::patch('/profesori/{professor}/status', [AdminProfessorController::class, 'toggle'])->name('professors.toggle');
        });
        Route::view('/profesor', 'dashboards.professor')
            ->middleware('role:professor')->name('professor.dashboard');
        Route::view('/student', 'dashboards.student')
            ->middleware('role:student')->name('student.dashboard');
    });
});
