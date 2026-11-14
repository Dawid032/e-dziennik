<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BulkGradeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeHistoryController;
use App\Http\Controllers\HomeroomController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeachingAssignmentController;
use Illuminate\Support\Facades\Route;

// Logowanie. Konta zakłada administrator, dlatego nie ma trasy /register.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Strona główna przekierowuje do panelu odpowiedniego dla roli.
    Route::get('/', DashboardController::class)->name('home');

    Route::get('/account/password', [AccountController::class, 'edit'])->name('account.password');
    Route::put('/account/password', [AccountController::class, 'update'])->name('account.password.update');

    // Administrator
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('users', AdminUserController::class);
        Route::resource('classes', SchoolClassController::class)->except('show')->parameters(['classes' => 'class']);
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('assignments', TeachingAssignmentController::class)->except('show');
    });

    // Nauczyciel
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
        Route::get('/homeroom/{class}', [HomeroomController::class, 'show'])->name('homeroom.show');
        Route::get('/homeroom/{class}/students/{student}', [HomeroomController::class, 'student'])->name('homeroom.student');
    });

    // Oceny, historia i uwagi: nauczyciel oraz administrator
    Route::middleware('role:teacher,admin')->group(function () {
        Route::get('/gradebook', [GradeController::class, 'index'])->name('gradebook.index');
        Route::get('/gradebook/open', [GradeController::class, 'open'])->name('gradebook.open');
        Route::get('/gradebook/{class}/{subject}', [GradeController::class, 'sheet'])->name('gradebook.sheet');
        Route::get('/gradebook/{class}/{subject}/grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('/gradebook/{class}/{subject}/grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('/gradebook/{class}/{subject}/bulk', [BulkGradeController::class, 'create'])->name('grades.bulk.create');
        Route::post('/gradebook/{class}/{subject}/bulk', [BulkGradeController::class, 'store'])->name('grades.bulk.store');

        Route::get('/grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
        Route::put('/grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
        Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

        Route::get('/history', [GradeHistoryController::class, 'index'])->name('history.index');
        Route::resource('notes', NoteController::class)->except('show');
    });

    // Uczeń
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/grades', [StudentDashboardController::class, 'grades'])->name('grades');
        Route::get('/history', [StudentDashboardController::class, 'history'])->name('history');
        Route::get('/notes', [StudentDashboardController::class, 'notes'])->name('notes');
    });

    // Rodzic
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/children/{student}', [ParentDashboardController::class, 'grades'])->name('children.grades');
        Route::get('/children/{student}/history', [ParentDashboardController::class, 'history'])->name('children.history');
        Route::get('/children/{student}/notes', [ParentDashboardController::class, 'notes'])->name('children.notes');
    });
});
