<?php

namespace App\Http\Controllers\Admin;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscribeController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = NewsletterSubscriber::query()
            ->when($request->filled('q'), fn($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.subscriptions.index', ['subscriptions' => $subscriptions]);
    }

    public function create(): View
    {
        return $this->view('admin.subscriptions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'name' => ['nullable', 'string'],
            'country_id' => ['nullable', 'integer'],
            'preferences' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'verified_at' => ['nullable', 'date'],
            'unsubscribed_at' => ['nullable', 'date'],
        ]);

        NewsletterSubscriber::create($data);

        return $this->success('admin.subscriptions.index', 'NewsletterSubscriber created.');
    }

    public function show(NewsletterSubscriber $newsletterSubscriber): View
    {
        return $this->view('admin.subscriptions.show', compact('newsletterSubscriber'));
    }

    public function edit(NewsletterSubscriber $newsletterSubscriber): View
    {
        return $this->view('admin.subscriptions.edit', compact('newsletterSubscriber'));
    }

    public function update(Request $request, NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'name' => ['nullable', 'string'],
            'country_id' => ['nullable', 'integer'],
            'preferences' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'verified_at' => ['nullable', 'date'],
            'unsubscribed_at' => ['nullable', 'date'],
        ]);

        $newsletterSubscriber->update($data);

        return $this->success('admin.subscriptions.index', 'NewsletterSubscriber updated.');
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->delete();

        return $this->success('admin.subscriptions.index', 'NewsletterSubscriber deleted.');
    }

    // public function destroy(NewsletterSubscriber $subscription): RedirectResponse
    // {
    //     $subscription->delete();
    //     return back()->with('success', 'Subscription deleted.');
    // }

}
