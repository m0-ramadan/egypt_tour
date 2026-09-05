<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Services\JsonTranslationFileService;
use App\Services\TranslatableContentSyncService;
use App\Support\LocaleNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(Request $request): View
    {
        $languages = Language::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('native_name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.languages.index', ['languages' => $languages]);
    }

    public function create(): View
    {
        return $this->view('admin.languages.create');
    }

    public function store(
        Request $request,
        TranslatableContentSyncService $translatableContentSyncService,
        JsonTranslationFileService $jsonTranslationFileService,
        LocaleNormalizer $localeNormalizer
    ): RedirectResponse {
        $request->merge(['code' => $localeNormalizer->normalize((string) $request->input('code'))]);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:languages,code'],
            'name' => ['required', 'string', 'max:255'],
            'native_name' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_default']) {
            Language::query()->update(['is_default' => false]);
            $data['is_active'] = true;
        }

        $language = Language::create($data);
        Cache::forget('supported_locales');

        if ($language->is_active) {
            $translatableContentSyncService->syncNewLanguage($language);
            $jsonTranslationFileService->ensureLocaleFile($language->code);
        }

        return $this->success('admin.languages.index', 'Language created.');
    }

    public function show(Language $language): View
    {
        return $this->view('admin.languages.show', compact('language'));
    }

    public function edit(Language $language): View
    {
        return $this->view('admin.languages.edit', compact('language'));
    }

    public function update(
        Request $request,
        Language $language,
        TranslatableContentSyncService $translatableContentSyncService,
        JsonTranslationFileService $jsonTranslationFileService,
        LocaleNormalizer $localeNormalizer
    ): RedirectResponse
    {
        $oldCode = $localeNormalizer->normalize($language->code);
        $request->merge(['code' => $localeNormalizer->normalize((string) $request->input('code'))]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:languages,code,' . $language->id],
            'name' => ['required', 'string', 'max:255'],
            'native_name' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_default']) {
            Language::query()
                ->where('id', '!=', $language->id)
                ->update(['is_default' => false]);

            $data['is_active'] = true;
        }

        $language->update($data);
        Cache::forget('supported_locales');

        if ($oldCode !== $language->code) {
            $jsonTranslationFileService->renameLocaleFile($oldCode, $language->code);
        }

        if ($language->is_active) {
            $translatableContentSyncService->syncNewLanguage($language);
            $jsonTranslationFileService->ensureLocaleFile($language->code);
        }

        return $this->success('admin.languages.index', 'Language updated.');
    }

    public function destroy(
        Language $language,
        TranslatableContentSyncService $translatableContentSyncService,
        JsonTranslationFileService $jsonTranslationFileService
    ): RedirectResponse {
        if ($language->is_default) {
            return back()->with('error', 'Default language cannot be deleted.');
        }

        $translatableContentSyncService->removeLanguage($language->code);
        $jsonTranslationFileService->removeLocaleFile($language->code);

        $language->delete();
        Cache::forget('supported_locales');

        return $this->success('admin.languages.index', 'Language deleted.');
    }

    public function toggle(
        Language $language,
        TranslatableContentSyncService $translatableContentSyncService,
        JsonTranslationFileService $jsonTranslationFileService
    ): RedirectResponse
    {
        if ($language->is_default) {
            $language->update(['is_active' => true]);
            $jsonTranslationFileService->ensureLocaleFile($language->code);

            return back()->with('success', 'Default language must stay active.');
        }

        $language->update([
            'is_active' => !(bool) $language->is_active
        ]);
        Cache::forget('supported_locales');

        if ($language->is_active) {
            $translatableContentSyncService->syncNewLanguage($language);
            $jsonTranslationFileService->ensureLocaleFile($language->code);
        }

        return back()->with('success', 'Language status updated.');
    }

    public function setDefault(
        Language $language,
        JsonTranslationFileService $jsonTranslationFileService
    ): RedirectResponse
    {
        Language::query()->update(['is_default' => false]);

        $language->update([
            'is_default' => true,
            'is_active' => true
        ]);
        Cache::forget('supported_locales');

        $jsonTranslationFileService->ensureLocaleFile($language->code);

        return back()->with('success', 'Default language changed.');
    }

    public function toggleAll(
        Request $request,
        TranslatableContentSyncService $translatableContentSyncService,
        JsonTranslationFileService $jsonTranslationFileService
    ): RedirectResponse
    {
        $status = (bool) $request->boolean('status', true);

        Language::query()
            ->where('is_default', false)
            ->update(['is_active' => $status]);

        Language::query()
            ->where('is_default', true)
            ->update(['is_active' => true]);
        Cache::forget('supported_locales');

        if ($status) {
            Language::query()
                ->where('is_active', true)
                ->get()
                ->each(function (Language $language) use ($translatableContentSyncService, $jsonTranslationFileService) {
                    $translatableContentSyncService->syncNewLanguage($language);
                    $jsonTranslationFileService->ensureLocaleFile($language->code);
                });
        }

        return back()->with('success', 'All languages updated.');
    }
}
