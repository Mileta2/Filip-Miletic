<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfessorController as AdminProfessorController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DefenseController;
use App\Http\Controllers\Professor\DashboardController as ProfessorDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\TopicSelectionController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TopicWorkflowController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/teme', [TopicController::class, 'index'])->name('topics.index');
Route::get('/diplomski-radovi', [TopicController::class, 'undergraduate'])->name('topics.undergraduate');
Route::get('/master-radovi', [TopicController::class, 'master'])->name('topics.master');
Route::get('/teme/{topic}', [TopicController::class, 'show'])->name('topics.show');

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
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/nova-tema', [TopicController::class, 'create'])->name('topics.create');
        Route::post('/teme', [TopicController::class, 'store'])->name('topics.store');
        Route::get('/teme/{topic}/izmena', [TopicController::class, 'edit'])->name('topics.edit');
        Route::put('/teme/{topic}', [TopicController::class, 'update'])->name('topics.update');
        Route::delete('/teme/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
        Route::get('/teme/{topic}/pdf', [TopicController::class, 'download'])->name('topics.pdf.download');
        Route::delete('/teme/{topic}/pdf', [TopicController::class, 'deletePdf'])->name('topics.pdf.destroy');
        Route::patch('/teme/{topic}/oslobodi', [TopicWorkflowController::class, 'release'])->name('topics.release');
        Route::get('/teme/{topic}/odbrana', [DefenseController::class, 'edit'])->name('topics.defense.edit');
        Route::put('/teme/{topic}/odbrana', [DefenseController::class, 'update'])->name('topics.defense.update');

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
        Route::middleware('role:professor')->prefix('profesor')->name('professor.')->group(function () {
            Route::get('/', ProfessorDashboardController::class)->name('dashboard');
        });
        Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
            Route::get('/', StudentDashboardController::class)->name('dashboard');
            Route::get('/moja-tema', [StudentDashboardController::class, 'topic'])->name('topic');
            Route::post('/teme/{topic}/izbor', [TopicSelectionController::class, 'store'])->name('topics.select');
        });
    });
});
