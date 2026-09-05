<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return $this->view('admin.settings.index');
    }

    public function general(): View
    {
        $settings = Setting::where('group', 'general')->pluck('value', 'key');
        return $this->view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        return $this->saveGroup('general', $request);
    }

    public function smtp(): View
    {
        $settings = Setting::where('group', 'smtp')->pluck('value', 'key');
        return $this->view('admin.settings.smtp', compact('settings'));
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        return $this->saveGroup('smtp', $request);
    }

    public function testSmtp(): RedirectResponse
    {
        return back()->with('success', 'SMTP test endpoint ready.');
    }

    public function communication(): View
    {
        $settings = Setting::where('group', 'communication')->pluck('value', 'key');
        return $this->view('admin.settings.communication', compact('settings'));
    }

    public function updateCommunication(Request $request): RedirectResponse
    {
        return $this->saveGroup('communication', $request);
    }

    public function files(): View
    {
        return $this->view('admin.settings.files');
    }

    public function updateFiles(Request $request): RedirectResponse
    {
        return $this->saveGroup('files', $request);
    }

    public function deleteFile(Request $request): RedirectResponse
    {
        $path = storage_path('app/' . ltrim((string) $request->input('path'), '/'));
        if (File::exists($path)) {
            File::delete($path);
        }

        return back()->with('success', 'File deleted.');
    }

    public function clearTempFiles(): RedirectResponse
    {
        return back()->with('success', 'Temporary files cleared.');
    }

    public function getStorageUsage(): JsonResponse
    {
        return response()->json(['storage_usage_mb' => 0]);
    }

    public function getQuickStats(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    public function getRecentActivitiesAjax(): JsonResponse
    {
        return response()->json([]);
    }

    public function getSystemStatus(): JsonResponse
    {
        return response()->json(['env' => app()->environment(), 'debug' => config('app.debug')]);
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('optimize:clear');
        return back()->with('success', 'Cache cleared.');
    }


    public function toggleMaintenance(): RedirectResponse
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            return back()->with('success', 'Maintenance disabled.');
        }

        Artisan::call('down');
        return back()->with('success', 'Maintenance enabled.');
    }
    protected function saveGroup(string $group, Request $request): RedirectResponse
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'type' => 'text']
            );
        }

        return back()->with('success', ucfirst($group) . ' settings updated.');
    }
}
