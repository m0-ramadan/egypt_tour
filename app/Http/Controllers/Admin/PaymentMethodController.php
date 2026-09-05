<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(Request $request): View
    {
        $paymentMethods = PaymentMethod::query()
            ->when($request->filled('q') || $request->filled('search'), function ($query) use ($request) {
                $search = '%' . ($request->input('q') ?: $request->input('search')) . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('provider', 'like', $search);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->input('status') === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->input('status') === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.payment-methods.index', compact('paymentMethods'));
    }

    public function create(): View
    {
        return $this->view('admin.payment-methods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data): void {
            if ($data['is_default']) {
                PaymentMethod::query()->update(['is_default' => false]);
            }

            PaymentMethod::create($data);
        }, 3);

        return $this->success('admin.payment-methods.index', 'Payment method created.');
    }

    public function show(PaymentMethod $paymentMethod): View
    {
        return $this->view('admin.payment-methods.show', compact('paymentMethod'));
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return $this->view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $this->validated($request, $paymentMethod);

        DB::transaction(function () use ($paymentMethod, $data): void {
            if ($data['is_default']) {
                PaymentMethod::query()
                    ->whereKeyNot($paymentMethod->id)
                    ->update(['is_default' => false]);
            }

            $paymentMethod->update($data);
        }, 3);

        return $this->success('admin.payment-methods.index', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        try {
            $paymentMethod->delete();
        } catch (\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return $this->success('admin.payment-methods.index', 'Payment method deleted.');
    }

    public function toggleStatus(PaymentMethod $paymentMethod): RedirectResponse
    {
        // Deactivation is allowed (useful for emergency gateway shutdown);
        // deletion remains protected when payment history exists.
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return back()->with('success', 'Payment method status updated.');
    }

    private function validated(Request $request, ?PaymentMethod $paymentMethod = null): array
    {
        if ($request->filled('type') && ! $request->filled('provider')) {
            $request->merge(['provider' => $request->input('type')]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'provider' => ['nullable', 'string', 'max:120'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'description' => ['nullable', 'string'],
            'config' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $config = $data['config'] ?? null;

        if (is_string($config) && trim($config) !== '') {
            $decoded = json_decode($config, true);

            if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'config' => 'Configuration must be valid JSON.',
                ]);
            }

            $data['config'] = $decoded;
        } elseif ($config === '' || $config === null) {
            $data['config'] = null;
        }

        $data['provider'] = strtolower(trim((string) ($data['provider'] ?? 'manual'))) ?: 'manual';
        $data['currency_code'] = strtoupper((string) ($data['currency_code'] ?? 'USD'));
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $data['code'] = $paymentMethod?->code
            ?: PaymentMethod::generateUniqueCode($data['name']);

        return $data;
    }
}
