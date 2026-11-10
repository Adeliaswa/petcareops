<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'appointment.pet.customer',
            'appointment.groomer',
            'appointment.services',
            'payments.pencatat',
        ]);

        return view('staf.invoices.show', compact('invoice'));
    }

    public function store(
        Appointment $appointment,
        InvoiceService $invoiceService
    ): RedirectResponse {
        $invoice = $invoiceService->buatUntukAppointment($appointment);

        return redirect()
            ->route('staf.invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat.');
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load([
            'appointment.pet.customer',
            'appointment.services',
            'payments.pencatat',
        ]);

        return view('staf.invoices.print', compact('invoice'));
    }

    public function storePayment(
        Request $request,
        Invoice $invoice,
        PaymentService $paymentService
    ): RedirectResponse {
        $data = $request->validate([
            'metode' => ['required', 'in:tunai,transfer,qris'],
            'nominal' => ['required', 'integer', 'min:1'],
            'dibayar_pada' => ['nullable', 'date'],
        ]);

        $paymentService->catat(
            $invoice,
            $data,
            $request->user()
        );

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroyPayment(
        Payment $payment,
        PaymentService $paymentService
    ): RedirectResponse {
        $paymentService->hapus($payment, request()->user());

        return back()->with('success', 'Catatan pembayaran berhasil dihapus.');
    }
}
