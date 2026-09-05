<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactUsController extends Controller
{
    public function index(Request $request): View
    {
        $contact_us = Inquiry::query()
            ->when($request->filled('q'), fn($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.contact-us.index', ['contacts' => $contact_us]);
    }

    public function create(): View
    {
        return $this->view('admin.contact-us.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Inquiry::create($data);

        return $this->success('admin.contact-us.index', 'ContactUs created.');
    }

    public function show(Inquiry $contactUs): View
    {
        return $this->view('admin.contact-us.show', compact('contactUs'));
    }

    public function edit(Inquiry $contactUs): View
    {
        return $this->view('admin.contact-us.edit', compact('contactUs'));
    }

    public function update(Request $request, Inquiry $contactUs): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $contactUs->update($data);

        return $this->success('admin.contact-us.index', 'ContactUs updated.');
    }

    public function destroy(Inquiry $contactUs): RedirectResponse
    {
        $contactUs->delete();

        return $this->success('admin.contact-us.index', 'ContactUs deleted.');
    }

    public function reply(Request $request, Inquiry $contactUs): RedirectResponse
    {
        $request->validate(['reply' => ['required', 'string']]);
        $contactUs->update([
            'notes' => trim(($contactUs->notes ?? '') . PHP_EOL . 'Reply: ' . $request->input('reply')),
            'status' => 'replied',
        ]);

        return back()->with('success', 'Reply stored.');
    }

    public function updateStatus(Request $request, Inquiry $contactUs): RedirectResponse
    {
        $request->validate(['status' => ['required', 'string']]);
        $contactUs->update(['status' => $request->input('status')]);

        return back()->with('success', 'Status updated.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        Inquiry::whereIn('id', (array) $request->input('ids', []))->update(['status' => $request->input('status')]);
        return back()->with('success', 'Bulk status updated.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        Inquiry::whereIn('id', (array) $request->input('ids', []))->delete();
        return back()->with('success', 'Messages deleted.');
    }
}
