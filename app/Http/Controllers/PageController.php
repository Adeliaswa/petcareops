<?php

namespace App\Http\Controllers;

use App\Models\DurationFactor;
use App\Models\Groomer;
use App\Models\GroomerTimeOff;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Pet;
use Illuminate\Http\Request;
use App\Models\Appointment;

class PageController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
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

    return view('staff.appointments.index', compact('appointments'));
}

    public function staffCustomers(Request $request)
{
    $search = trim($request->input('search', ''));

    $customers = Customer::query()
        ->withCount('pets')
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(nama) LIKE ?', [
                    '%' . strtolower($search) . '%',
                ])->orWhere('no_wa', 'like', '%' . $search . '%');
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
                    '%' . strtolower($search) . '%',
                ])
                ->orWhereRaw('LOWER(spesies) LIKE ?', [
                    '%' . strtolower($search) . '%',
                ])
                ->orWhereRaw('LOWER(ras) LIKE ?', [
                    '%' . strtolower($search) . '%',
                ])
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->whereRaw('LOWER(nama) LIKE ?', [
                        '%' . strtolower($search) . '%',
                    ]);
                });
            });
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('staff.pets.index', compact('pets'));
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
        ->where('status', '!=', 'dibatalkan')
        ->orderByDesc('mulai')
        ->paginate(15);

    return view('groomer.tasks.index', compact('groomer', 'appointments'));
}
}