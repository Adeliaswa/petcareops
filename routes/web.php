<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\DurationFactorController;
use App\Http\Controllers\Admin\GroomerController;
use App\Http\Controllers\Admin\GroomerShiftController;
use App\Http\Controllers\Admin\GroomerTimeOffController;
use App\Http\Controllers\BookingEstimationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Estimasi dan pencarian slot untuk pelanggan publik
Route::prefix('booking')
    ->name('booking.')
    ->middleware('throttle:30,1')
    ->group(function () {
        Route::post('/estimasi', [
            BookingEstimationController::class,
            'estimasiPublik',
        ])->name('estimasi');

        Route::post('/slot', [
            BookingEstimationController::class,
            'slotPublik',
        ])->name('slot');
    });

Route::middleware(['auth', 'active'])->group(function () {

    // Profil untuk semua staf
    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Halaman khusus admin
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/dashboard', function () {
                return view('dashboard');
            })->name('dashboard');

            Route::get('/staff', [
                \App\Http\Controllers\Admin\StaffAccountController::class,
                'index',
            ])->name('staff.index');

            Route::get('/staff/create', [
                \App\Http\Controllers\Admin\StaffAccountController::class,
                'create',
            ])->name('staff.create');

            Route::post('/staff', [
                \App\Http\Controllers\Admin\StaffAccountController::class,
                'store',
            ])->name('staff.store');

            Route::patch('/staff/{user}/deactivate', [
                \App\Http\Controllers\Admin\StaffAccountController::class,
                'destroy',
            ])->name('staff.deactivate');

            Route::patch('/staff/{user}/activate', [
                \App\Http\Controllers\Admin\StaffAccountController::class,
                'activate',
            ])->name('staff.activate');

            Route::put('/staff/{user}/reset-password', [
                \App\Http\Controllers\Admin\StaffAccountController::class,
                'resetPassword',
            ])->name('staff.reset-password');

            // CRUD layanan grooming
            Route::resource('layanan', ServiceController::class)
                ->parameters(['layanan' => 'service'])
                ->except(['create', 'edit']);

            Route::resource('faktor-durasi', DurationFactorController::class)
                ->parameters(['faktor-durasi' => 'factor'])
                ->except(['create', 'edit']);

            // Pengelolaan groomer
            Route::get('groomer', [GroomerController::class, 'index'])
                ->name('groomer.index');

            Route::get('groomer/{groomer}', [GroomerController::class, 'show'])
                ->name('groomer.show');

            Route::put('groomer/{groomer}', [GroomerController::class, 'update'])
                ->name('groomer.update');

            // Shift groomer
            Route::get('groomer/{groomer}/shift', [
                GroomerShiftController::class,
                'index',
            ])->name('groomer.shift.index');

            Route::post('groomer/{groomer}/shift', [
                GroomerShiftController::class,
                'store',
            ])->name('groomer.shift.store');

            Route::put('groomer/{groomer}/shift/{shift}', [
                GroomerShiftController::class,
                'update',
            ])->name('groomer.shift.update');

            Route::delete('groomer/{groomer}/shift/{shift}', [
                GroomerShiftController::class,
                'destroy',
            ])->name('groomer.shift.destroy');

            // Jadwal libur groomer
            Route::get('groomer/{groomer}/libur', [
                GroomerTimeOffController::class,
                'index',
            ])->name('groomer.time-off.index');

            Route::post('groomer/{groomer}/libur', [
                GroomerTimeOffController::class,
                'store',
            ])->name('groomer.time-off.store');

            Route::put('groomer/{groomer}/libur/{timeOff}', [
                GroomerTimeOffController::class,
                'update',
            ])->name('groomer.time-off.update');

            Route::delete('groomer/{groomer}/libur/{timeOff}', [
                GroomerTimeOffController::class,
                'destroy',
            ])->name('groomer.time-off.destroy');

            Route::get('libur', [GroomerTimeOffController::class, 'indexAll'])
                ->name('libur.index');
        });

    // Estimasi dan pencarian slot untuk admin dan resepsionis
    Route::prefix('staf/booking')
        ->name('staf.booking.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {
            Route::post('/estimasi', [
                BookingEstimationController::class,
                'estimasiStaf',
            ])->name('estimasi');

            Route::post('/slot', [
                BookingEstimationController::class,
                'slotStaf',
            ])->name('slot');
        });

    // Halaman staf: admin dan resepsionis
    Route::prefix('staf')
        ->name('staf.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {
            Route::get('/appointments', function () {
                return view('staff.appointments.index');
            })->name('appointments.index');

            // CRUD pemilik
            Route::resource('pemilik', CustomerController::class)
                ->parameters(['pemilik' => 'customer'])
                ->except(['create', 'edit']);

            // CRUD hewan
            Route::resource('hewan', PetController::class)
                ->parameters(['hewan' => 'pet'])
                ->except(['create', 'edit']);
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