<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'concept'      => ['nullable', 'string', 'max:190'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'currency'     => ['required', 'in:PEN,USD'],
            'status'       => ['required', 'in:paid,pending,cancelled'],
            'paid_at'      => ['nullable', 'date'],
            'receipt'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store("receipts/{$patient->id}", 'public');
        }

        Payment::create([
            'patient_id'   => $patient->id,
            'concept'      => $data['concept'],
            'amount'       => $data['amount'],
            'currency'     => $data['currency'],
            'status'       => $data['status'],
            'paid_at'      => $data['paid_at'] ?? ($data['status'] === 'paid' ? now() : null),
            'receipt_path' => $receiptPath,
            'created_by'   => auth()->id(),
        ]);

        return back()->with('ok_pagos', 'Pago registrado correctamente.');
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'concept'  => ['nullable', 'string', 'max:190'],
            'amount'   => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'in:PEN,USD'],
            'status'   => ['required', 'in:paid,pending,cancelled'],
            'paid_at'  => ['nullable', 'date'],
            'receipt'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('receipt')) {
            if ($payment->receipt_path) {
                Storage::disk('public')->delete($payment->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store("receipts/{$payment->patient_id}", 'public');
        }

        $payment->update(array_merge($data, [
            'paid_at' => $data['paid_at'] ?? ($data['status'] === 'paid' && !$payment->paid_at ? now() : $payment->paid_at),
        ]));

        return back()->with('ok_pagos', 'Pago actualizado.');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->receipt_path) {
            Storage::disk('public')->delete($payment->receipt_path);
        }
        $payment->delete();

        return back()->with('ok_pagos', 'Pago eliminado.');
    }
}
