<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\DurationFactor;
use App\Models\Groomer;
use App\Models\GroomerTimeOff;
use App\Models\Pet;
use App\Models\Service;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard()
{
    $todayAppointments = Appointment::query()
        ->with([
            'pet.customer',
            'groomer.user',
            'services',
        ])
        ->whereDate('mulai', today()->toDateString())
        ->where('status', '!=', 'dibatalkan')
        ->orderBy('mulai')
        ->get();

    $stats = [
        'appointments_today' => $todayAppointments->count(),

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

    return view('dashboard', compact(
        'stats',
        'todayAppointments'
    ));
}

    public function adminServices()
    {
        $services = Service::query()
            ->latest()
            ->paginate(15);

        return view('admin.services.index', compact('services'));
    }

    public function adminDurationFactors()
    {
        $factors = DurationFactor::query()
            ->orderBy('kategori')
            ->orderBy('key')
            ->paginate(15);

        return view('admin.duration-factors.index', compact('factors'));
    }

    public function adminGroomers()
    {
        $groomers = Groomer::query()
            ->with('user:id,name,email,is_active')
            ->latest()
            ->paginate(15);

        return view('admin.groomers.index', compact('groomers'));
    }

    public function adminGroomerShifts(string $groomer)
    {
        $groomer = Groomer::with('user:id,name,email')
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
        $groomer = Groomer::with('user:id,name,email')
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
        $timeOffs = GroomerTimeOff::with('groomer.user:id,name')
            ->orderBy('tanggal_mulai')
            ->get();

        return view('admin.time-off.index', compact('timeOffs'));
    }

    public function staffAppointments(Request $request)
{
    $filters = [
        'search' => trim($request->input('search', '')),
        'status' => $request->input('status', ''),
        'groomer_id' => $request->input('groomer_id', ''),
        'tanggal' => $request->input('tanggal', ''),
    ];

    $groomers = Groomer::query()
        ->with('user:id,name')
        ->orderBy('id')
        ->get();

    $appointments = Appointment::query()
        ->with([
            'pet.customer',
            'groomer.user',
            'services',
            'invoice.payments',
        ])
        ->when($filters['search'] !== '', function ($query) use ($filters) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->whereHas('pet', function ($petQuery) use ($search) {
                    $petQuery->where('nama', 'ilike', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('nama', 'ilike', "%{$search}%");
                        });
                });
            });
        })
        ->when(
            in_array($filters['status'], [
                'menunggu',
                'dikerjakan',
                'selesai',
                'dijemput',
                'dibatalkan',
            ], true),
            fn ($query) => $query->where('status', $filters['status'])
        )
        ->when(
            $filters['groomer_id'] !== ''
                && ctype_digit((string) $filters['groomer_id']),
            fn ($query) => $query->where(
                'groomer_id',
                $filters['groomer_id']
            )
        )
        ->when(
            $filters['tanggal'] !== ''
                && strtotime($filters['tanggal']),
            fn ($query) => $query->whereDate(
                'mulai',
                $filters['tanggal']
            )
        )
        ->latest('mulai')
        ->paginate(15)
        ->withQueryString();

    return view('staff.appointments.index', compact(
        'appointments',
        'groomers',
        'filters'
    ));
}

    public function staffAppointmentDetail(Appointment $appointment)
    {
        $appointment->load([
            'pet.customer',
            'groomer.user',
            'services',
            'invoice.payments',
        ]);

        return view('staff.appointments.show', compact('appointment'));
    }

    public function groomerCalendar(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
            'groomer_id' => ['nullable', 'integer', 'exists:groomers,id'],
        ]);

        $tanggalMulai = $validated['tanggal_mulai']
            ?? now()->startOfWeek()->toDateString();

        $tanggalAkhir = $validated['tanggal_akhir']
            ?? now()->endOfWeek()->toDateString();

        $groomerId = $validated['groomer_id'] ?? null;

        $groomers = Groomer::query()
            ->with('user:id,name')
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
                fn ($query) => $query->where('groomer_id', $groomerId)
            )
            ->orderBy('mulai')
            ->get();

        $appointmentsByDate = $appointments->groupBy(
            fn ($appointment) => $appointment->mulai->toDateString()
        );

        return view('staff.groomers.calendar', compact(
            'groomers',
            'appointmentsByDate',
            'tanggalMulai',
            'tanggalAkhir',
            'groomerId'
        ));
    }

    public function staffCustomers(Request $request)
    {
        $search = trim($request->input('search', ''));

        $customers = Customer::query()
            ->withCount('pets')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(nama) LIKE ?', [
                        '%'.strtolower($search).'%',
                    ])->orWhere('no_wa', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.customers.index', compact('customers'));
    }

    public function staffPets(Request $request)
    {
        $search = trim($request->input('search', ''));

        $pets = Pet::query()
            ->with('customer:id,nama,no_wa')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(nama) LIKE ?', [
                        '%'.strtolower($search).'%',
                    ])
                        ->orWhereRaw('LOWER(spesies) LIKE ?', [
                            '%'.strtolower($search).'%',
                        ])
                        ->orWhereRaw('LOWER(ras) LIKE ?', [
                            '%'.strtolower($search).'%',
                        ])
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->whereRaw('LOWER(nama) LIKE ?', [
                                '%'.strtolower($search).'%',
                            ]);
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.pets.index', compact('pets'));
    }

    public function staffPetGroomingHistory(Pet $pet)
    {
        $pet->load('customer');

        $groomingRecords = $pet->appointments()
            ->whereHas('groomingRecord')
            ->with([
                'groomer.user',
                'services',
                'groomingRecord',
            ])
            ->latest('mulai')
            ->paginate(10)
            ->withQueryString();

        return view('staff.pets.grooming-history', compact(
            'pet',
            'groomingRecords'
        ));
    }

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

    return view('public.home', compact('services'));
}

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

        return view('booking.create', compact('services', 'factors'));
    }

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
                fn ($query) => $query->where('groomer_id', $groomer->id),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->whereDate('mulai', today()->toDateString())
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('mulai')
            ->paginate(15);

        return view('groomer.tasks.index', compact(
            'groomer',
            'appointments'
        ));
    }
}
