<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->when($request->filled('q') || $request->filled('search'), function ($query) use ($request) {
                $search = '%' . ($request->input('q') ?: $request->input('search')) . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.clients.index', ['clients' => $clients]);
    }

    public function create(): View
    {
        return $this->view('admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['last_activity'] = now();

        Client::create($data);

        return $this->success('admin.clients.index', 'Client created.');
    }

    public function show(Client $client): View
    {
        return $this->view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return $this->view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $this->validated($request, $client);
        $data['last_activity'] = now();

        $client->update($data);

        return $this->success('admin.clients.index', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        try {
            $client->delete();
        } catch (\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return $this->success('admin.clients.index', 'Client deleted.');
    }

    public function export()
    {
        return response()->json(Client::latest()->get());
    }

    public function bookings(Client $client): View
    {
        $bookings = $client->bookings()->latest()->paginate(20);

        return $this->view('admin.clients.bookings', compact('client', 'bookings'));
    }

    public function inquiries(Client $client): View
    {
        $inquiries = $client->inquiries()->latest()->paginate(20);

        return $this->view('admin.clients.inquiries', compact('client', 'inquiries'));
    }

    public function toggleStatus(Client $client): RedirectResponse
    {
        $client->update(['is_active' => ! (bool) ($client->is_active ?? true)]);

        return back()->with('success', 'Client status updated.');
    }

    private function validated(Request $request, ?Client $client = null): array
    {
        if ($request->filled('date_of_birth') && ! $request->filled('birth_date')) {
            $request->merge(['birth_date' => $request->input('date_of_birth')]);
        }

        $data = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('clients', 'email')->ignore($client?->id),
            ],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'birth_date' => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'passport_expiry' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'newsletter_subscribed' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['newsletter_subscribed'] = $request->boolean('newsletter_subscribed');

        return $data;
    }
}
