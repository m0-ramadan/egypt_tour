<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $settings = Setting::where('group', 'basic')->pluck('value', 'key');
        return $this->view('admin.setting.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ($request->except(['_token']) as $key => $value) {
            Setting::updateOrCreate(
                ['group' => 'basic', 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'type' => 'text']
            );
        }

        return back()->with('success', 'Basic settings updated.');
    }

    public function updatepages(Request $request): RedirectResponse
    {
        foreach ($request->except(['_token']) as $key => $value) {
            Setting::updateOrCreate(
                ['group' => 'pages', 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'type' => 'text']
            );
        }

        return back()->with('success', 'Page settings updated.');
    }
}
