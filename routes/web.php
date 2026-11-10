
<?php

use App\Http\Controllers\Admin\DurationFactorController;
use App\Http\Controllers\Admin\GroomerController;
use App\Http\Controllers\Admin\GroomerShiftController;
use App\Http\Controllers\Admin\GroomerTimeOffController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffAccountController;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentStatusController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingEstimationController;
use App\Http\Controllers\BookingStatusController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\InvoiceController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Halaman utama
Route::get('/', function () {
    if (! auth()->check()) {
        return app(PageController::class)->publicHome();
    }

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


// Booking publik
Route::get('/booking', [
    PageController::class,
    'publicBooking',
])->name('booking.create');


// Proses booking publik
Route::prefix('booking')
    ->name('booking.')
    ->middleware('throttle:30,1')
    ->group(function () {

        Route::post('/identifikasi', [
            BookingController::class,
            'identifikasi',
        ])->name('identifikasi');

        Route::get('/pelanggan', [
            BookingController::class,
            'cariPelangganPublik',
        ])->name('pelanggan');

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


// Status booking publik
Route::get('/status/{token}', [
    BookingStatusController::class,
    'show',
])->middleware('throttle:30,1')
    ->name('booking.status');

Route::get('/status/{token}/data', [
    BookingStatusController::class,
    'data',
])->middleware('throttle:30,1')
    ->name('booking.status.data');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profil', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profil', [
        ProfileController::class,
        'update',
    ])->name('profile.update');


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

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
            Route::prefix('halaman')
                ->name('pages.')
                ->group(function () {

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


            // CRUD layanan grooming
            Route::resource('layanan', ServiceController::class)
                ->parameters(['layanan' => 'service'])
                ->except(['create', 'edit']);


            // CRUD faktor durasi
            Route::resource('faktor-durasi', DurationFactorController::class)
                ->parameters(['faktor-durasi' => 'factor'])
                ->except(['create', 'edit']);


            // Pengelolaan groomer
            Route::get('/groomer', [
                GroomerController::class,
                'index',
            ])->name('groomer.index');

            Route::get('/groomer/{groomer}', [
                GroomerController::class,
                'show',
            ])->name('groomer.show');

            Route::put('/groomer/{groomer}', [
                GroomerController::class,
                'update',
            ])->name('groomer.update');


            // Shift groomer
            Route::get('/groomer/{groomer}/shift', [
                GroomerShiftController::class,
                'index',
            ])->name('groomer.shift.index');

            Route::post('/groomer/{groomer}/shift', [
                GroomerShiftController::class,
                'store',
            ])->name('groomer.shift.store');

            Route::put('/groomer/{groomer}/shift/{shift}', [
                GroomerShiftController::class,
                'update',
            ])->name('groomer.shift.update');

            Route::delete('/groomer/{groomer}/shift/{shift}', [
                GroomerShiftController::class,
                'destroy',
            ])->name('groomer.shift.destroy');


            // Jadwal libur groomer
            Route::get('/groomer/{groomer}/libur', [
                GroomerTimeOffController::class,
                'index',
            ])->name('groomer.time-off.index');

            Route::post('/groomer/{groomer}/libur', [
                GroomerTimeOffController::class,
                'store',
            ])->name('groomer.time-off.store');

            Route::put('/groomer/{groomer}/libur/{timeOff}', [
                GroomerTimeOffController::class,
                'update',
            ])->name('groomer.time-off.update');

            Route::delete('/groomer/{groomer}/libur/{timeOff}', [
                GroomerTimeOffController::class,
                'destroy',
            ])->name('groomer.time-off.destroy');

            Route::get('/libur', [
                GroomerTimeOffController::class,
                'indexAll',
            ])->name('libur.index');
        });


    /*
    |--------------------------------------------------------------------------
    | STAFF BOOKING ROUTES
    |--------------------------------------------------------------------------
    */

    Route::prefix('staf/booking')
        ->name('staf.booking.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {

            Route::get('/', [
                PageController::class,
                'staffBooking',
            ])->name('create');

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


    /*
    |--------------------------------------------------------------------------
    | STAFF OPERATION ROUTES (M4 + M5)
    |--------------------------------------------------------------------------
    */

    Route::prefix('staf')
        ->name('staf.')
        ->middleware('role:admin,resepsionis')
        ->group(function () {

            // Daftar appointment
            // Mempertahankan controller milikmu
            Route::get('/appointments', [
                AppointmentController::class,
                'index',
            ])->name('appointments.index');


            // Detail appointment M5
            Route::get('/appointments/{appointment}', [
                PageController::class,
                'staffAppointmentDetail',
            ])->name('appointments.show');


            // Perubahan status appointment
            // Mempertahankan endpoint milikmu
            Route::patch('/appointments/{appointment}/status', [
                AppointmentController::class,
                'updateStatus',
            ])->name('appointments.status');


            // Pembatalan appointment
            // Mempertahankan endpoint milikmu
            Route::patch('/appointments/{appointment}/cancel', [
                AppointmentController::class,
                'cancel',
            ])->name('appointments.cancel');

            // ==========================================
// M6: INVOICE DAN PEMBAYARAN
// ==========================================

// Membuat invoice dari appointment
Route::post('/appointments/{appointment}/invoice', [
    InvoiceController::class,
    'store',
])->name('appointments.invoice.store');

// Melihat detail invoice
Route::get('/invoices/{invoice}', [
    InvoiceController::class,
    'show',
])->name('invoices.show');

// Mencetak invoice
Route::get('/invoices/{invoice}/print', [
    InvoiceController::class,
    'print',
])->name('invoices.print');

// Mencatat pembayaran
Route::post('/invoices/{invoice}/payments', [
    InvoiceController::class,
    'storePayment',
])->name('invoices.payments.store');

// Menghapus pembayaran khusus admin
Route::delete('/payments/{payment}', [
    InvoiceController::class,
    'destroyPayment',
])->middleware('role:admin')->name('payments.destroy');


// ==========================================
// M6: PENGINGAT
// ==========================================

// Halaman pengingat
Route::get('/pengingat', [
    ReminderController::class,
    'index',
])->name('pengingat.index');

// Menjalankan pengingat
Route::post('/pengingat/jalankan', [
    ReminderController::class,
    'jalankan',
])->middleware('throttle:5,1')->name('pengingat.jalankan');

            // Kalender workload groomer M5
            Route::get('/kalender-groomer', [
                PageController::class,
                'groomerCalendar',
            ])->name('groomers.calendar');


            // Halaman Blade pemilik dan hewan
            Route::prefix('halaman')
                ->name('pages.')
                ->group(function () {

                    Route::get('/pemilik', [
                        PageController::class,
                        'staffCustomers',
                    ])->name('customers');

                    Route::get('/hewan', [
                        PageController::class,
                        'staffPets',
                    ])->name('pets');
                });


            // Pilihan faktor durasi
            Route::get('/faktor-durasi/options', [
                DurationFactorController::class,
                'options',
            ])->name('faktor-durasi.options');


            // CRUD pemilik
            Route::resource('pemilik', CustomerController::class)
                ->parameters(['pemilik' => 'customer'])
                ->except(['create', 'edit']);


            // Riwayat grooming hewan M5
            Route::get('/hewan/{pet}/riwayat-grooming', [
                PageController::class,
                'staffPetGroomingHistory',
            ])->name('pets.grooming-history');


            // CRUD hewan
            Route::resource('hewan', PetController::class)
                ->parameters(['hewan' => 'pet'])
                ->except(['create', 'edit']);
        });


    /*
    |--------------------------------------------------------------------------
    | GROOMER ROUTES (M5)
    |--------------------------------------------------------------------------
    */

    Route::prefix('groomer')
        ->name('groomer.')
        ->middleware('role:groomer')
        ->group(function () {

            // Daftar tugas groomer
            Route::get('/tugas', [
                PageController::class,
                'groomerTasks',
            ])->name('tasks.index');


            // Mulai pengerjaan grooming
            Route::post('/tugas/{appointment}/mulai', [
                AppointmentStatusController::class,
                'mulai',
            ])->name('tasks.start');


            // Selesaikan pengerjaan grooming
            Route::post('/tugas/{appointment}/selesai', [
                AppointmentStatusController::class,
                'selesai',
            ])->name('tasks.finish');
        });
});


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
