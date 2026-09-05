<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Package;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['client', 'package'])
            ->when($request->filled('q') || $request->filled('search'), function ($query) use ($request) {
                $search = '%' . ($request->input('q') ?: $request->input('search')) . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('booking_number', 'like', $search)
                        ->orWhereHas('client', function ($client) use ($search) {
                            $client->where('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            })
            ->when($request->filled('package_id'), fn ($q) => $q->where('package_id', $request->integer('package_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.bookings.index', ['bookings' => $bookings]);
    }

    public function create(): View
    {
        return $this->view('admin.bookings.create', [
            'clients' => Client::query()->orderBy('first_name')->get(),
            'packages' => Package::query()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data): void {
            $client = Client::query()->lockForUpdate()->findOrFail($data['client_id']);

            $clientChanges = ['last_activity' => now()];

            if ($request->filled('email')) {
                $clientChanges['email'] = $request->input('email');
            }

            if ($request->filled('phone')) {
                $clientChanges['phone'] = $request->input('phone');
            }

            $client->forceFill($clientChanges)->save();

            Booking::create([
                'client_id' => $data['client_id'],
                'package_id' => $data['package_id'],
                'booking_number' => $data['booking_number'],
                'status' => $data['status'],
                'total_amount' => $data['total_amount'],
                'paid_amount' => 0,
                'currency_code' => $data['currency_code'],
                'payment_status' => 'unpaid',
                'booking_date' => now()->toDateString(),
                'travel_date' => $data['travel_date'],
                'adults' => $data['adults'],
                'children' => 0,
                'infants' => 0,
                'special_requests' => $data['special_requests'],
            ]);
        }, 3);

        return $this->success('admin.bookings.index', 'Booking created.');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['client', 'package', 'payments', 'travelers', 'items.cabin']);

        return $this->view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking): View
    {
        return $this->view('admin.bookings.edit', [
            'booking' => $booking->load(['client', 'package']),
            'clients' => Client::query()->orderBy('first_name')->get(),
            'packages' => Package::query()->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $this->validated($request, $booking);

        if ($booking->payments()->exists()) {
            $factor = (int) config('services.paymob.minor_unit_factor', 100);

            if (Money::toMinor((string) $data['total_amount'], $factor)
                    !== Money::toMinor((string) $booking->total_amount, $factor)
                || strtoupper($data['currency_code']) !== strtoupper((string) $booking->currency_code)) {
                return back()
                    ->withInput()
                    ->with('error', 'Total amount and currency cannot change after a payment attempt exists.');
            }
        }

        DB::transaction(function () use ($request, $booking, $data): void {
            $booking->forceFill([
                'client_id' => $data['client_id'],
                'package_id' => $data['package_id'],
                'booking_number' => $data['booking_number'],
                'status' => $data['status'],
                'total_amount' => $data['total_amount'],
                'currency_code' => $data['currency_code'],
                'travel_date' => $data['travel_date'],
                'adults' => $data['adults'],
                'special_requests' => $data['special_requests'],
            ])->save();

            $client = Client::query()->lockForUpdate()->findOrFail($data['client_id']);
            $clientChanges = ['last_activity' => now()];

            if ($request->filled('email')) {
                $clientChanges['email'] = $request->input('email');
            }

            if ($request->filled('phone')) {
                $clientChanges['phone'] = $request->input('phone');
            }

            $client->forceFill($clientChanges)->save();
        }, 3);

        return $this->success('admin.bookings.index', 'Booking updated.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        try {
            $booking->delete();
        } catch (\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return $this->success('admin.bookings.index', 'Booking deleted.');
    }

    public function statistics()
    {
        return response()->json([
            'total' => Booking::count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'pending' => Booking::where('status', 'pending')->count(),
        ]);
    }

    public function yearlyStats(Request $request, $year = null)
    {
        $targetYear = $year ?: $request->input('year', date('Y'));
        $monthsData = [];
        $monthKeys = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($m = 1; $m <= 12; $m++) {
            $monthsData[] = [
                'month' => admin_t($monthKeys[$m - 1]),
                'total' => Booking::whereYear('created_at', $targetYear)
                    ->whereMonth('created_at', $m)
                    ->count(),
            ];
        }

        return response()->json($monthsData);
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
        ]);

        $changes = ['status' => $data['status']];

        if ($data['status'] === 'confirmed') {
            $changes['confirmed_at'] = $booking->confirmed_at ?? now();
        }

        if ($data['status'] === 'cancelled') {
            $changes['cancelled_at'] = now();
        }

        $booking->update($changes);

        return back()->with('success', 'Booking status updated.');
    }

    public function print(Booking $booking): View
    {
        $booking->load(['client', 'package', 'travelers', 'items.cabin']);

        return $this->view('admin.bookings.print', compact('booking'));
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        if ($request->input('action') === 'delete') {
            $blocked = 0;

            Booking::whereIn('id', (array) $request->input('ids', []))
                ->get()
                ->each(function (Booking $booking) use (&$blocked): void {
                    try {
                        $booking->delete();
                    } catch (\LogicException) {
                        $blocked++;
                    }
                });

            if ($blocked > 0) {
                return back()->with('error', "{$blocked} booking(s) were protected because payment history exists.");
            }
        }

        return back()->with('success', 'Bulk action applied.');
    }

    private function validated(Request $request, ?Booking $booking = null): array
    {
        if ($request->filled('booking_reference') && ! $request->filled('booking_number')) {
            $request->merge(['booking_number' => $request->input('booking_reference')]);
        }

        if ($request->filled('notes') && ! $request->filled('special_requests')) {
            $request->merge(['special_requests' => $request->input('notes')]);
        }

        if ($request->filled('travellers_count') && ! $request->filled('adults')) {
            $request->merge(['adults' => $request->input('travellers_count')]);
        }

        if (! $request->filled('booking_number')) {
            $request->merge([
                'booking_number' => 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8)),
            ]);
        }

        $clientId = (int) $request->input('client_id');

        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'booking_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bookings', 'booking_number')->ignore($booking?->id),
            ],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
            'total_amount' => ['required', 'numeric', 'gte:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'travel_date' => ['required', 'date'],
            'adults' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('clients', 'email')->ignore($clientId),
            ],
        ]);

        $data['currency_code'] = strtoupper($data['currency_code']);

        return $data;
    }
}
