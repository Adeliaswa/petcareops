<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {

    // Profil untuk semua staf
    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Halaman khusus admin
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::get('/staff', [\App\Http\Controllers\Admin\StaffAccountController::class, 'index'])
            ->name('staff.index');

        Route::get('/staff/create', [\App\Http\Controllers\Admin\StaffAccountController::class, 'create'])
            ->name('staff.create');

        Route::post('/staff', [\App\Http\Controllers\Admin\StaffAccountController::class, 'store'])
            ->name('staff.store');

        Route::patch('/staff/{user}/deactivate', [\App\Http\Controllers\Admin\StaffAccountController::class, 'destroy'])
            ->name('staff.deactivate');

        Route::patch('/staff/{user}/activate', [\App\Http\Controllers\Admin\StaffAccountController::class, 'activate'])
            ->name('staff.activate');

        Route::put('/staff/{user}/reset-password', [\App\Http\Controllers\Admin\StaffAccountController::class, 'resetPassword'])
            ->name('staff.reset-password');
    });

    // Halaman staf: admin dan resepsionis
    Route::prefix('staf')
        ->name('staf.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {
            Route::get('/appointments', function () {
                return view('staff.appointments.index');
            })->name('appointments.index');
        });

    // Halaman khusus groomer
    Route::prefix('groomer')
        ->name('groomer.')
        ->middleware('role:groomer')
        ->group(function () {
            Route::get('/tugas', function () {
                return view('groomer.tasks.index');
            })->name('tasks.index');
        });
});

require __DIR__.'/auth.php';
