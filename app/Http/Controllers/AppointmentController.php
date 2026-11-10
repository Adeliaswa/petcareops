<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Groomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'groomer_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:menunggu,dikerjakan,selesai,dijemput,dibatalkan'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $appointments = Appointment::query()
            ->with(['pet.customer', 'groomer.user', 'services'])
            ->when($filters['tanggal'] ?? null, fn ($q, $date) => $q->whereDate('mulai', $date))
            ->when($filters['groomer_id'] ?? null, fn ($q, $id) => $q->where('groomer_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($sub) use ($term) {
                    $sub->where('id', 'like', "%{$term}%")
                        ->orWhereHas('pet', fn ($pet) => $pet->where('nama', 'like', "%{$term}%"))
                        ->orWhereHas('pet.customer', fn ($customer) => $customer->where('nama', 'like', "%{$term}%"));
                });
            })
            ->orderByDesc('mulai')
            ->paginate(15)
            ->withQueryString();

        $groomers = Groomer::query()
            ->with('user:id,name')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get();

        $summary = [
            'total' => Appointment::count(),
            'menunggu' => Appointment::where('status', 'menunggu')->count(),
            'dikerjakan' => Appointment::where('status', 'dikerjakan')->count(),
            'selesai' => Appointment::where('status', 'selesai')->count(),
        ];

        return view('staff.appointments.index', compact('appointments', 'groomers', 'summary', 'filters'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'status' => ['required', 'in:dikerjakan,selesai,dijemput'],
        ]);

        $transitions = [
            'menunggu' => ['dikerjakan'],
            'dikerjakan' => ['selesai'],
            'selesai' => ['dijemput'],
            'dijemput' => [],
            'dibatalkan' => [],
        ];

        DB::transaction(function () use ($appointment, $data, $transitions) {
            $locked = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $allowed = $transitions[$locked->status] ?? [];

            if (! in_array($data['status'], $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => ['Perubahan status tidak valid. Status saat ini: '.($locked->status ?? 'tidak diketahui').'.'],
                ]);
            }

            $locked->status = $data['status'];
            $locked->save();
        });

        return response()->json([
            'message' => 'Status appointment berhasil diperbarui.',
            'status' => $data['status'],
        ]);
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $request->validate([
            'alasan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($appointment) {
            $locked = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);

            if ($locked->status !== 'menunggu') {
                throw ValidationException::withMessages([
                    'status' => ['Hanya appointment berstatus menunggu yang dapat dibatalkan.'],
                ]);
            }

            // Tidak menambah kolom atau mengubah skema database.
            // Alasan pembatalan tidak disimpan karena skema saat ini tidak menyediakan kolom khusus.
            $locked->status = 'dibatalkan';
            $locked->save();
        });

        return response()->json([
            'message' => 'Appointment berhasil dibatalkan.',
            'status' => 'dibatalkan',
        ]);
    }
}
