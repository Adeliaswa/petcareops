<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Services\ReminderService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(
        Request $request,
        WhatsAppService $whatsAppService
    ): View {
        $request->validate([
            'status' => [
                'nullable',
                'in:menunggu,terkirim,ditutup',
            ],
        ]);

        $reminders = Reminder::query()
            ->with([
                'pet.customer',
                'servisTerakhir',
            ])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request->status
                )
            )
            ->orderBy('tanggal_jatuh_tempo')
            ->paginate(15)
            ->withQueryString();

        $reminders->getCollection()->each(
            function (Reminder $reminder) use ($whatsAppService) {
                $customer = $reminder->pet?->customer;

                if (
                    $reminder->status === 'menunggu' &&
                    $customer &&
                    $customer->no_wa
                ) {
                    $pesan = $whatsAppService
                        ->buatPesanPengingat($reminder);

                    $reminder->setAttribute(
                        'wa_link',
                        $whatsAppService->buatLinkManual(
                            $customer->no_wa,
                            $pesan
                        )
                    );
                }
            }
        );

        return view(
            'staf.pengingat.index',
            compact('reminders')
        );
    }

    public function jalankan(
        ReminderService $reminderService
    ) {
        $jumlahDibuat = $reminderService->generateDueReminders(
            cobaUlangMenunggu: true
        );

        return redirect()
            ->route('staf.pengingat.index')
            ->with(
                'success',
                "Proses selesai. {$jumlahDibuat} pengingat baru dibuat."
            );
    }
}