<?php

use App\Http\Controllers\Admin\DurationFactorController;
use App\Http\Controllers\Admin\GroomerController;
use App\Http\Controllers\Admin\GroomerShiftController;
use App\Http\Controllers\Admin\GroomerTimeOffController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffAccountController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingEstimationController;
use App\Http\Controllers\BookingStatusController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    // Jika belum login, arahkan ke halaman login
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Jika sudah login, arahkan sesuai role
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('resepsionis')) {
        return redirect()->route('staf.appointments.index');
    }

    if ($user->hasRole('groomer')) {
        return redirect()->route('groomer.tasks.index');
    }

    abort(403, 'Akun tidak memiliki role yang sesuai.');

})->name('home');

// Halaman booking publik
Route::get('/booking', [PageController::class, 'publicBooking'])
    ->name('booking.create');

// Proses booking publik
Route::prefix('booking')
    ->name('booking.')
    ->middleware('throttle:30,1')
    ->group(function () {
        Route::post('/identifikasi', [
            BookingController::class,
            'identifikasi',
        ])->name('identifikasi');

        Route::post('/estimasi', [
            BookingEstimationController::class,
            'estimasiPublik',
        ])->name('estimasi');

        Route::post('/slot', [
            BookingEstimationController::class,
            'slotPublik',
        ])->name('slot');

        Route::post('/konfirmasi', [
            BookingController::class,
            'konfirmasi',
        ])->name('konfirmasi');
    });

// Status booking publik (JSON)
Route::get('/status/{token}', [
    BookingStatusController::class,
    'show',
])
    ->middleware('throttle:30,1')
    ->name('booking.status');

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

            // Dashboard
            Route::get('/dashboard', [
                PageController::class,
                'dashboard',
            ])->name('dashboard');

            // Pengelolaan akun staf
            Route::get('/staff', [
                StaffAccountController::class,
                'index',
            ])->name('staff.index');

            Route::get('/staff/create', [
                StaffAccountController::class,
                'create',
            ])->name('staff.create');

            Route::post('/staff', [
                StaffAccountController::class,
                'store',
            ])->name('staff.store');

            Route::patch('/staff/{user}/deactivate', [
                StaffAccountController::class,
                'destroy',
            ])->name('staff.deactivate');

            Route::patch('/staff/{user}/activate', [
                StaffAccountController::class,
                'activate',
            ])->name('staff.activate');

            Route::put('/staff/{user}/reset-password', [
                StaffAccountController::class,
                'resetPassword',
            ])->name('staff.reset-password');

            // Halaman Blade admin
            Route::prefix('halaman')->name('pages.')->group(function () {
                Route::get('/layanan', [
                    PageController::class,
                    'adminServices',
                ])->name('services');

                Route::get('/faktor-durasi', [
                    PageController::class,
                    'adminDurationFactors',
                ])->name('duration-factors');

                Route::get('/groomer', [
                    PageController::class,
                    'adminGroomers',
                ])->name('groomers');

                Route::get('/groomer/{groomer}/shift', [
                    PageController::class,
                    'adminGroomerShifts',
                ])->name('groomer-shifts');

                Route::get('/groomer/{groomer}/libur', [
                    PageController::class,
                    'adminGroomerTimeOff',
                ])->name('groomer-time-off');

                Route::get('/libur', [
                    PageController::class,
                    'adminTimeOff',
                ])->name('time-off');
            });

            // CRUD layanan grooming (JSON)
            Route::resource('layanan', ServiceController::class)
                ->parameters(['layanan' => 'service'])
                ->except(['create', 'edit']);

            // CRUD faktor durasi (JSON)
            Route::resource('faktor-durasi', DurationFactorController::class)
                ->parameters(['faktor-durasi' => 'factor'])
                ->except(['create', 'edit']);

            // Pengelolaan groomer (JSON)
            Route::get('groomer', [GroomerController::class, 'index'])
                ->name('groomer.index');

            Route::get('groomer/{groomer}', [GroomerController::class, 'show'])
                ->name('groomer.show');

            Route::put('groomer/{groomer}', [GroomerController::class, 'update'])
                ->name('groomer.update');

            // Shift groomer (JSON)
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

            // Jadwal libur groomer (JSON)
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

            Route::get('libur', [
                GroomerTimeOffController::class,
                'indexAll',
            ])->name('libur.index');
        });

    // Booking staf: admin dan resepsionis
    Route::prefix('staf/booking')
        ->name('staf.booking.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {

            // Halaman form booking staf
            Route::get('/', [
                PageController::class,
                'staffBooking',
            ])->name('create');

            // Proses booking staf (JSON)
            Route::post('/estimasi', [
                BookingEstimationController::class,
                'estimasiStaf',
            ])->name('estimasi');

            Route::post('/slot', [
                BookingEstimationController::class,
                'slotStaf',
            ])->name('slot');

            Route::post('/konfirmasi', [
                BookingController::class,
                'konfirmasiWalkIn',
            ])->name('konfirmasi');

            Route::get('/pelanggan', [
                BookingController::class,
                'cariPelanggan',
            ])->name('pelanggan');
        });

    // Halaman staf: admin dan resepsionis
    Route::prefix('staf')
        ->name('staf.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {

            Route::get('/appointments', [
                PageController::class,
                'staffAppointments',
            ])->name('appointments.index');

            // Halaman Blade pemilik dan hewan
            Route::prefix('halaman')->name('pages.')->group(function () {
                Route::get('/pemilik', [
                    PageController::class,
                    'staffCustomers',
                ])->name('customers');

                Route::get('/hewan', [
                    PageController::class,
                    'staffPets',
                ])->name('pets');
            });

            
            // Pilihan faktor durasi untuk form hewan.
            // Admin dan resepsionis hanya mendapatkan akses membaca.
            Route::get('/faktor-durasi/options', [
                DurationFactorController::class,
                'options',
            ])->name('faktor-durasi.options');

            // CRUD pemilik (JSON)
            Route::resource('pemilik', CustomerController::class)
                ->parameters(['pemilik' => 'customer'])
                ->except(['create', 'edit']);


            // CRUD pemilik (JSON)
            Route::resource('pemilik', CustomerController::class)
                ->parameters(['pemilik' => 'customer'])
                ->except(['create', 'edit']);

            // CRUD hewan (JSON)
            Route::resource('hewan', PetController::class)
                ->parameters(['hewan' => 'pet'])
                ->except(['create', 'edit']);
        });
        

    // Halaman khusus groomer
    Route::prefix('groomer')
        ->name('groomer.')
        ->middleware('role:groomer')
        ->group(function () {
            Route::get('/tugas', [
                PageController::class,
                'groomerTasks',
            ])->name('tasks.index');
        });
});

require __DIR__.'/auth.php';
