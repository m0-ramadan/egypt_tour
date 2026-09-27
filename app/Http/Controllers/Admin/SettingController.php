<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function pages(): View
    {
        $settings = Setting::where('group', 'pages')->pluck('value', 'key');
        $pages = Page::all();
        return $this->view('admin.setting.pages', compact('settings', 'pages'));
    }

    public function edit(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return $this->view('admin.setting.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = time() . '_' . $key . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('settings', $filename, 'public');
                $value = 'storage/' . $path;
            }

            if ($value !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => 'basic',
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'type' => $request->hasFile($key) ? 'image' : 'text',
                    ]
                );
            }
        }

        Cache::flush();

        return back()->with('success', 'Basic settings & images updated successfully.');
    }

    public function updatepages(Request $request): RedirectResponse
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => 'pages',
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'type' => 'text',
                ]
            );
        }

        Cache::flush();

        return back()->with('success', 'Page settings updated successfully.');
    }
}
