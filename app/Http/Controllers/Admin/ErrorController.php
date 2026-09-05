<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ErrorController extends Controller
{
    protected string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
    }

    public function index(): View
    {
        $files = collect(File::files($this->logPath))->map(fn ($file) => [
            'name' => $file->getFilename(),
            'size' => $file->getSize(),
            'modified' => date('Y-m-d H:i:s', $file->getMTime()),
        ])->values();

        return $this->view('admin.errors.index', compact('files'));
    }

    public function phpErrors(): View
    {
        return $this->index();
    }

    public function search(Request $request): View
    {
        $term = (string) $request->input('q', '');
        $matches = [];

        foreach (File::files($this->logPath) as $file) {
            $content = File::get($file->getPathname());
            if ($term !== '' && Str::contains($content, $term)) {
                $matches[] = ['file' => $file->getFilename(), 'preview' => Str::limit($content, 1000)];
            }
        }

        return $this->view('admin.errors.search', compact('matches', 'term'));
    }

    public function download(string $filename)
    {
        $path = $this->logPath . DIRECTORY_SEPARATOR . basename($filename);
        abort_unless(File::exists($path), 404);

        return response()->download($path);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $filename = basename((string) $request->input('filename'));
        $path = $this->logPath . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($path)) {
            File::delete($path);
        }

        return back()->with('success', 'Log file deleted.');
    }

    public function clearAll(): RedirectResponse
    {
        foreach (File::files($this->logPath) as $file) {
            File::delete($file->getPathname());
        }

        return back()->with('success', 'All log files cleared.');
    }
}
