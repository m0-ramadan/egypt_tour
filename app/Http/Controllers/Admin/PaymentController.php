<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payments\BookingPaymentSynchronizer;
use App\Services\Payments\PaymobService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['booking', 'paymentMethod'])
            ->when($request->filled('q') || $request->filled('search'), function ($query) use ($request) {
                $search = '%' . ($request->input('q') ?: $request->input('search')) . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_reference', 'like', $search)
                        ->orWhere('gateway_reference', 'like', $search)
                        ->orWhere('gateway_transaction_id', 'like', $search)
                        ->orWhere('status', 'like', $search)
                        ->orWhere('currency_code', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('currency_code'), fn ($q) => $q->where('currency_code', strtoupper($request->input('currency_code'))))
            ->when($request->filled('payment_type'), fn ($q) => $q->where('payment_type', $request->input('payment_type')))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.payments.index', compact('payments'));
    }

    public function create(): View
    {
        return $this->view('admin.payments.create');
    }

    public function store(Request $request, BookingPaymentSynchronizer $synchronizer): RedirectResponse
    {
        $data = $this->validatedManualPayment($request);

        if (! empty($data['payment_method_id'])) {
            $method = PaymentMethod::find($data['payment_method_id']);

            if ($method && strtolower((string) $method->provider) === 'paymob') {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'Paymob payments must be created through the Paymob checkout flow.',
                ]);
            }
        }

        $payment = Payment::create($data);

        if ($payment->booking_id) {
            $synchronizer->sync($payment->booking_id);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment created.');
    }

    public function show(Payment $payment): View
    {
        $payment->load(['booking', 'paymentMethod', 'refunds']);

        return $this->view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment): View
    {
        return $this->view('admin.payments.edit', compact('payment'));
    }

    public function update(
        Request $request,
        Payment $payment,
        BookingPaymentSynchronizer $synchronizer,
    ): RedirectResponse {
        if ($payment->isPaymob()) {
            $data = $request->validate([
                'notes' => ['nullable', 'string'],
            ]);

            $payment->update($data);

            return redirect()->route('admin.payments.index')
                ->with('success', 'Paymob gateway-owned fields are read-only; notes updated.');
        }

        $payment->update($this->validatedManualPayment($request, $payment));

        if ($payment->booking_id) {
            $synchronizer->sync($payment->booking_id);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        try {
            $payment->delete();
        } catch (\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted.');
    }

    public function statistics()
    {
        return response()->json([
            'total' => Payment::count(),
            'paid' => Payment::where('status', Payment::STATUS_PAID)->count(),
            'pending' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'failed' => Payment::where('status', Payment::STATUS_FAILED)->count(),
            'partially_refunded' => Payment::where('status', Payment::STATUS_PARTIALLY_REFUNDED)->count(),
            'refunded' => Payment::where('status', Payment::STATUS_REFUNDED)->count(),
            'sum_paid' => Payment::whereIn('status', [
                Payment::STATUS_PAID,
                Payment::STATUS_PARTIALLY_REFUNDED,
            ])->sum('amount'),
        ]);
    }

    public function updateStatus(
        Request $request,
        Payment $payment,
        BookingPaymentSynchronizer $synchronizer,
    ): RedirectResponse {
        if ($payment->isPaymob()) {
            return back()->with('error', 'Paymob payment status is gateway-owned and cannot be changed manually.');
        }

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Payment::STATUS_PENDING,
                    Payment::STATUS_PAID,
                    Payment::STATUS_FAILED,
                    Payment::STATUS_PARTIALLY_PAID,
                    Payment::STATUS_REFUNDED,
                    Payment::STATUS_PARTIALLY_REFUNDED,
                ]),
            ],
        ]);

        $payment->update(['status' => $data['status']]);

        if ($payment->booking_id) {
            $synchronizer->sync($payment->booking_id);
        }

        return back()->with('success', 'Payment status updated.');
    }

    public function refund(Request $request, Payment $payment, PaymobService $paymob): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'gt:0'],
        ]);

        try {
            $refund = $paymob->refund(
                $payment,
                $data['amount'] ?? null,
                auth('admin')->id(),
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Refund failed: ' . $exception->getMessage());
        }

        return back()->with(
            'success',
            $refund->status === 'pending'
                ? 'Refund submitted and is pending Paymob confirmation.'
                : 'Refund completed successfully.'
        );
    }

    public function reconcile(Payment $payment, PaymobService $paymob): RedirectResponse
    {
        try {
            $payment = $paymob->reconcile($payment);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Reconciliation failed: ' . $exception->getMessage());
        }

        return back()->with('success', 'Payment reconciled: ' . $payment->status);
    }

    public function export()
    {
        return response()->json(
            Payment::with(['booking', 'paymentMethod', 'refunds'])->latest()->get()
        );
    }

    private function validatedManualPayment(Request $request, ?Payment $payment = null): array
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'transaction_reference' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('payments', 'transaction_reference')->ignore($payment?->id),
            ],
            'gateway_reference' => ['nullable', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'status' => [
                'required',
                Rule::in([
                    Payment::STATUS_PENDING,
                    Payment::STATUS_PAID,
                    Payment::STATUS_FAILED,
                    Payment::STATUS_PARTIALLY_PAID,
                    Payment::STATUS_REFUNDED,
                    Payment::STATUS_PARTIALLY_REFUNDED,
                ]),
            ],
            'payment_type' => ['required', Rule::in(['full', 'deposit', 'installment', 'refund'])],
            'paid_at' => ['nullable', 'date'],
            'gateway_payload' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        if (is_string($data['gateway_payload'] ?? null)
            && trim((string) $data['gateway_payload']) !== '') {
            $decoded = json_decode((string) $data['gateway_payload'], true);

            if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'gateway_payload' => 'Gateway payload must be valid JSON.',
                ]);
            }

            $data['gateway_payload'] = $decoded;
        } elseif (($data['gateway_payload'] ?? null) === '') {
            $data['gateway_payload'] = null;
        }

        $data['currency_code'] = strtoupper($data['currency_code']);

        return $data;
    }
}
