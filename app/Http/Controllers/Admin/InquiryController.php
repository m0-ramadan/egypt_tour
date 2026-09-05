<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $inquiries = Inquiry::query()
            ->when($request->filled('q'), fn ($q) => $q->where('id', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.inquiries.index', ['inquiries' => $inquiries]);
    }

    public function create(): View
    {
        return $this->view('admin.inquiries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'integer'],
            'package_id' => ['nullable', 'integer'],
            'form_id' => ['nullable', 'integer'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'inquiry_type' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'travel_date' => ['nullable', 'date'],
            'adults' => ['nullable', 'integer'],
            'children' => ['nullable', 'integer'],
            'budget_min' => ['nullable', 'numeric'],
            'budget_max' => ['nullable', 'numeric'],
            'currency_code' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Inquiry::create($data);

        return $this->success('admin.inquiries.index', 'Inquiry created.');
    }

    public function show(Inquiry $inquiry): View
    {
        $inquiry->load(['package', 'tailorMadeRequest']);

        return $this->view('admin.inquiries.show', compact('inquiry'));
    }

    public function edit(Inquiry $inquiry): View
    {
        return $this->view('admin.inquiries.edit', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'integer'],
            'package_id' => ['nullable', 'integer'],
            'form_id' => ['nullable', 'integer'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'inquiry_type' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'travel_date' => ['nullable', 'date'],
            'adults' => ['nullable', 'integer'],
            'children' => ['nullable', 'integer'],
            'budget_min' => ['nullable', 'numeric'],
            'budget_max' => ['nullable', 'numeric'],
            'currency_code' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $inquiry->update($data);

        return $this->success('admin.inquiries.index', 'Inquiry updated.');
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return $this->success('admin.inquiries.index', 'Inquiry deleted.');
    }

    public function statistics()
    {
        return response()->json([
            'total' => Inquiry::count(),
            'new' => Inquiry::where('status', 'new')->count(),
            'closed' => Inquiry::where('status', 'closed')->count(),
        ]);
    }

    public function reply(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $request->validate(['reply' => ['required', 'string']]);
        $inquiry->update([
            'notes' => trim(($inquiry->notes ?? '') . PHP_EOL . 'Reply: ' . $request->input('reply')),
            'status' => 'replied',
        ]);

        return back()->with('success', 'Reply saved.');
    }

    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $request->validate(['status' => ['required', 'string']]);
        $inquiry->update(['status' => $request->input('status')]);

        return back()->with('success', 'Inquiry status updated.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        if ($request->input('action') === 'delete') {
            Inquiry::whereIn('id', (array) $request->input('ids', []))->delete();
        }

        return back()->with('success', 'Bulk action applied.');
    }

}
