<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\DurationFactor;
use App\Models\Groomer;
use App\Models\GroomerTimeOff;
use App\Models\Pet;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        $stats = [
            'appointments_today' => Appointment::query()
                ->whereDate('mulai', today())
                ->where('status', '!=', 'dibatalkan')
                ->count(),

            'waiting_appointments' => Appointment::query()
                ->where('status', 'menunggu')
                ->count(),

            'customers' => Customer::query()->count(),

            'pets' => Pet::query()->count(),

            'active_groomers' => Groomer::query()
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->count(),
        ];

        $todayAppointments = Appointment::query()
            ->with([
                'pet.customer',
                'groomer.user',
                'services',
            ])
            ->whereDate('mulai', today())
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('mulai')
            ->limit(6)
            ->get();

        return view(
            'dashboard',
            compact('stats', 'todayAppointments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN - MASTER DATA
    |--------------------------------------------------------------------------
    */

    public function adminServices()
    {
        $services = Service::query()
            ->latest()
            ->paginate(15);

        return view(
            'admin.services.index',
            compact('services')
        );
    }

    public function adminDurationFactors()
    {
        $factors = DurationFactor::query()
            ->orderBy('kategori')
            ->orderBy('key')
            ->paginate(15);

        return view(
            'admin.duration-factors.index',
            compact('factors')
        );
    }

    public function adminGroomers()
    {
        $groomers = Groomer::query()
            ->with('user:id,name,email,is_active')
            ->latest()
            ->paginate(15);

        return view(
            'admin.groomers.index',
            compact('groomers')
        );
    }

    public function adminGroomerShifts(string $groomer)
    {
        $groomer = Groomer::query()
            ->with('user:id,name,email')
            ->findOrFail($groomer);

        $shifts = $groomer->shifts()
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view(
            'admin.groomers.shifts.index',
            compact('groomer', 'shifts')
        );
    }

    public function adminGroomerTimeOff(string $groomer)
    {
        $groomer = Groomer::query()
            ->with('user:id,name,email')
            ->findOrFail($groomer);

        $timeOffs = $groomer->timeOffs()
            ->orderBy('tanggal_mulai')
            ->get();

        return view(
            'admin.groomers.time-off.index',
            compact('groomer', 'timeOffs')
        );
    }

    public function adminTimeOff()
    {
        $timeOffs = GroomerTimeOff::query()
            ->with('groomer.user:id,name')
            ->orderBy('tanggal_mulai')
            ->get();

        return view(
            'admin.time-off.index',
            compact('timeOffs')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - APPOINTMENT M5
    |--------------------------------------------------------------------------
    */

    public function staffAppointments()
    {
        $appointments = Appointment::query()
            ->with([
                'pet.customer',
                'groomer.user',
                'services',
            ])
            ->latest('mulai')
            ->paginate(15);

        return view(
            'staff.appointments.index',
            compact('appointments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - DETAIL APPOINTMENT M5 + INVOICE M6
    |--------------------------------------------------------------------------
    */

    public function staffAppointmentDetail(Appointment $appointment)
    {
        $appointment->load([
            'pet.customer',
            'groomer.user',
            'services',
            'groomingRecord',
            'invoice',
        ]);

        return view(
            'staff.appointments.show',
            compact('appointment')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - CUSTOMERS M2
    |--------------------------------------------------------------------------
    */

    public function staffCustomers(Request $request)
    {
        $search = trim($request->input('search', ''));

        $customers = Customer::query()
            ->withCount('pets')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->whereRaw('LOWER(nama) LIKE ?', [
                            '%' . strtolower($search) . '%',
                        ])
                        ->orWhere(
                            'no_wa',
                            'like',
                            '%' . $search . '%'
                        );
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'staff.customers.index',
            compact('customers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - PETS M2
    |--------------------------------------------------------------------------
    */

    public function staffPets(Request $request)
    {
        $search = trim($request->input('search', ''));

        $pets = Pet::query()
            ->with('customer:id,nama,no_wa')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->whereRaw('LOWER(nama) LIKE ?', [
                            '%' . strtolower($search) . '%',
                        ])
                        ->orWhereRaw('LOWER(spesies) LIKE ?', [
                            '%' . strtolower($search) . '%',
                        ])
                        ->orWhereRaw('LOWER(ras) LIKE ?', [
                            '%' . strtolower($search) . '%',
                        ])
                        ->orWhereHas(
                            'customer',
                            function ($customerQuery) use ($search) {
                                $customerQuery->whereRaw(
                                    'LOWER(nama) LIKE ?',
                                    ['%' . strtolower($search) . '%']
                                );
                            }
                        );
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'staff.pets.index',
            compact('pets')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC - HOME
    |--------------------------------------------------------------------------
    */

    public function publicHome()
    {
        $services = Service::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'deskripsi',
                'durasi_dasar',
                'harga',
            ]);

        return view(
            'public.home',
            compact('services')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC - BOOKING
    |--------------------------------------------------------------------------
    */

    public function publicBooking()
    {
        $services = Service::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        $factors = DurationFactor::query()
            ->orderBy('kategori')
            ->orderBy('key')
            ->get();

        return view(
            'booking.create',
            compact('services', 'factors')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - BOOKING
    |--------------------------------------------------------------------------
    */

    public function staffBooking()
    {
        $services = Service::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        $factors = DurationFactor::query()
            ->orderBy('kategori')
            ->orderBy('key')
            ->get();

        return view(
            'staff.booking.create',
            compact('services', 'factors')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GROOMER - TASKS M5
    |--------------------------------------------------------------------------
    */

    public function groomerTasks()
    {
        $groomer = auth()->user()->groomer;

        $appointments = Appointment::query()
            ->with([
                'pet.customer',
                'services',
            ])
            ->when(
                $groomer,
                fn ($query) => $query->where(
                    'groomer_id',
                    $groomer->id
                ),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->where('status', '!=', 'dibatalkan')
            ->orderByDesc('mulai')
            ->paginate(15);

        return view(
            'groomer.tasks.index',
            compact('groomer', 'appointments')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - KALENDER WORKLOAD GROOMER M5
    |--------------------------------------------------------------------------
    |
    | Data untuk:
    | resources/views/staff/groomers/calendar.blade.php
    |
    | Variabel:
    | - $tanggalMulai
    | - $tanggalAkhir
    | - $groomerId
    | - $groomers
    | - $appointmentsByDate
    |
    */

    public function groomerCalendar(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'groomer_id' => [
                'nullable',
                'integer',
                'exists:groomers,id',
            ],
        ]);

        $tanggalMulai = $validated['tanggal_mulai']
            ?? today()->startOfWeek()->toDateString();

        $tanggalAkhir = $validated['tanggal_akhir']
            ?? today()->endOfWeek()->toDateString();

        $start = Carbon::parse($tanggalMulai);
        $end = Carbon::parse($tanggalAkhir);

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'tanggal_akhir' =>
                    'Tanggal akhir tidak boleh sebelum tanggal mulai.',
            ]);
        }

        if ($start->diffInDays($end) > 31) {
            throw ValidationException::withMessages([
                'tanggal_akhir' =>
                    'Rentang kalender maksimal 31 hari.',
            ]);
        }

        $groomerId = $validated['groomer_id'] ?? null;

        $groomers = Groomer::query()
            ->with('user')
            ->orderBy('id')
            ->get();

        $appointments = Appointment::query()
            ->with([
                'pet.customer',
                'groomer.user',
                'services',
            ])
            ->whereDate('mulai', '>=', $tanggalMulai)
            ->whereDate('mulai', '<=', $tanggalAkhir)
            ->when(
                $groomerId,
                fn ($query) => $query->where(
                    'groomer_id',
                    $groomerId
                )
            )
            ->orderBy('mulai')
            ->get();

        $appointmentsByDate = $appointments->groupBy(
            fn ($appointment) =>
                $appointment->mulai->toDateString()
        );

        return view(
            'staff.groomers.calendar',
            compact(
                'tanggalMulai',
                'tanggalAkhir',
                'groomerId',
                'groomers',
                'appointmentsByDate'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF - RIWAYAT GROOMING HEWAN M5
    |--------------------------------------------------------------------------
    |
    | Data untuk:
    | resources/views/staff/pets/grooming-history.blade.php
    |
    | Variabel:
    | - $pet
    | - $groomingRecords
    |
    */

    public function staffPetGroomingHistory(Pet $pet)
    {
        $pet->load('customer');

        $groomingRecords = Appointment::query()
            ->with([
                'groomer.user',
                'services',
                'groomingRecord',
            ])
            ->where('pet_id', $pet->id)
            ->whereIn('status', [
                'selesai',
                'dijemput',
            ])
            ->whereHas('groomingRecord')
            ->orderByDesc('mulai')
            ->paginate(10);

        return view(
            'staff.pets.grooming-history',
            compact(
                'pet',
                'groomingRecords'
            )
        );
    }
}
