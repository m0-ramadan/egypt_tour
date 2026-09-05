<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Communication;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    public function index(Request $request): View
    {
        $communications = Communication::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . $request->string('q') . '%';
                $query->where('subject', 'like', $search)
                    ->orWhere('content', 'like', $search)
                    ->orWhere('type', 'like', $search)
                    ->orWhere('direction', 'like', $search);
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.communications.index', compact('communications'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'inquiry_id' => ['nullable', 'integer', 'exists:inquiries,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'type' => ['required', 'string', 'max:100'],
            'direction' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'sent_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'attachment_url' => ['nullable', 'string', 'max:255'],
            'created_by' => ['nullable', 'integer', 'exists:admins,id'],
        ]);

        Communication::create($data);

        return back()->with('success', 'Communication created.');
    }

    public function show(Communication $communication): View
    {
        return $this->view('admin.communications.show', compact('communication'));
    }

    public function destroy(Communication $communication): RedirectResponse
    {
        $communication->delete();

        return back()->with('success', 'Communication deleted.');
    }

    public function clientCommunications(Client $client): View
    {
        $communications = $client->communications()->latest()->paginate(20);

        return $this->view('admin.communications.client', compact('client', 'communications'));
    }

    public function inquiryCommunications(Inquiry $inquiry): View
    {
        $communications = $inquiry->communications()->latest()->paginate(20);

        return $this->view('admin.communications.inquiry', compact('inquiry', 'communications'));
    }

    public function bookingCommunications(Booking $booking): View
    {
        $communications = $booking->communications()->latest()->paginate(20);

        return $this->view('admin.communications.booking', compact('booking', 'communications'));
    }

    public function markSent(Communication $communication): RedirectResponse
    {
        $communication->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Communication marked as sent.');
    }
}
