<?php

use App\Http\Controllers\Admin\DurationFactorController;
use App\Http\Controllers\Admin\GroomerController;
use App\Http\Controllers\Admin\GroomerShiftController;
use App\Http\Controllers\Admin\GroomerTimeOffController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffAccountController;
use App\Http\Controllers\AppointmentStatusController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingEstimationController;
use App\Http\Controllers\BookingStatusController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\InvoiceController;

Route::get('/', [PageController::class, 'publicHome'])
    ->name('home');

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

        Route::get('/pelanggan', [
    BookingController::class,
    'cariPelangganPublik',
])->name('pelanggan');
    });

// Status booking publik (JSON)
// Halaman status booking publik
Route::get('/status/{token}', [
    BookingStatusController::class,
    'show',
])->middleware('throttle:30,1')
    ->name('booking.status');

// Data status booking dalam format JSON
Route::get('/status/{token}/data', [
    BookingStatusController::class,
    'data',
])->middleware('throttle:30,1')
    ->name('booking.status.data');

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

            Route::get('/appointments/{appointment}', [
                PageController::class,
                'staffAppointmentDetail',
            ])->name('appointments.show');

            // Invoice dan pembayaran
Route::post('/appointments/{appointment}/invoice', [
    InvoiceController::class,
    'store',
])->name('appointments.invoice.store');

Route::get('/invoices/{invoice}', [
    InvoiceController::class,
    'show',
])->name('invoices.show');

Route::get('/invoices/{invoice}/print', [
    InvoiceController::class,
    'print',
])->name('invoices.print');

Route::post('/invoices/{invoice}/payments', [
    InvoiceController::class,
    'storePayment',
])->name('invoices.payments.store');

// Penghapusan pembayaran hanya untuk admin
Route::delete('/payments/{payment}', [
    InvoiceController::class,
    'destroyPayment',
])->middleware('role:admin')->name('payments.destroy');

            Route::post('/appointments/{appointment}/batal', [
                AppointmentStatusController::class,
                'batalkan',
            ])->name('appointments.cancel');

            Route::get('/pengingat', [
    ReminderController::class,
    'index',
])->name('pengingat.index');

Route::post('/pengingat/jalankan', [
    ReminderController::class,
    'jalankan',
])->middleware('throttle:5,1')->name('pengingat.jalankan');

            Route::get('/kalender-groomer', [
                PageController::class,
                'groomerCalendar',
            ])->name('groomers.calendar');

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

            // CRUD pemilik (JSON)
            Route::resource('pemilik', CustomerController::class)
                ->parameters(['pemilik' => 'customer'])
                ->except(['create', 'edit']);

                Route::get('/hewan/{pet}/riwayat-grooming', [
    PageController::class,
    'staffPetGroomingHistory',
])->name('pets.grooming-history');

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

            Route::post('/tugas/{appointment}/mulai', [
                AppointmentStatusController::class,
                'mulai',
            ])->name('tasks.start');

            Route::post('/tugas/{appointment}/selesai', [
    AppointmentStatusController::class,
    'selesai',
])->name('tasks.finish');

        });

});

require __DIR__.'/auth.php';
