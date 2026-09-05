<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavvyMedia;
use App\Services\SavvyHostMediaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    /**
     * Display a listing of local SavvyMedia items with search and filters.
     */
    public function index(Request $request)
    {
        $query = SavvyMedia::query();

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        // Storage type filter
        if ($request->filled('storage_type')) {
            $query->where('storage_type', $request->input('storage_type'));
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Country filter
        if ($request->filled('country_slug')) {
            $query->where('country_slug', $request->input('country_slug'));
        }

        // City filter
        if ($request->filled('city_slug')) {
            $query->where('city_slug', $request->input('city_slug'));
        }

        // Sub category filter
        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->input('sub_category'));
        }

        $mediaItems = $query->orderByRaw('COALESCE(remote_created_at, created_at) DESC')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Calculate stats
        $stats = [
            'total' => SavvyMedia::count(),
            'global' => SavvyMedia::where('is_global', true)->orWhere('storage_type', 'global')->count(),
            'private' => SavvyMedia::where(function ($q) {
                $q->where('is_global', false)->orWhere('storage_type', 'private');
            })->count(),
            'downloaded' => SavvyMedia::where('is_downloaded', true)->count(),
            'last_sync' => SavvyMedia::max('last_synced_at'),
        ];

        // Unique filter option values for dropdowns
        $categories = SavvyMedia::whereNotNull('category')->distinct()->pluck('category')->filter()->values();
        $countries = SavvyMedia::whereNotNull('country_slug')->distinct()->pluck('country_slug')->filter()->values();
        $cities = SavvyMedia::whereNotNull('city_slug')->distinct()->pluck('city_slug')->filter()->values();
        $subCategories = SavvyMedia::whereNotNull('sub_category')->distinct()->pluck('sub_category')->filter()->values();
        $storageTypes = SavvyMedia::whereNotNull('storage_type')->distinct()->pluck('storage_type')->filter()->values();

        return view('admin.media.index', compact(
            'mediaItems',
            'stats',
            'categories',
            'countries',
            'cities',
            'subCategories',
            'storageTypes'
        ));
    }

    /**
     * Synchronize media from SavvyHost API into local database and download image files.
     */
    public function sync(Request $request, SavvyHostMediaService $service)
    {
        try {
            $result = $service->syncAllMedia(downloadFiles: true);

            $message = "تم جلب وتنزيل الصور بنجاح. تم معالجة {$result['total_processed']} عنصر وتنزيل {$result['total_downloaded']} صورة محلياً.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $result,
                ]);
            }

            return redirect()->route('admin.media.index')->with('success', $message);
        } catch (Exception $e) {
            Log::error('Media synchronization error from Admin Controller', [
                'message' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل جلب وتنزيل الصور: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.media.index')->with('error', 'فشل جلب وتنزيل الصور: ' . $e->getMessage());
        }
    }

    /**
     * Get the real-time synchronization progress.
     */
    public function syncProgress()
    {
        $progress = Cache::get('savvy_media_sync_progress', [
            'status' => 'idle',
            'processed' => 0,
            'downloaded' => 0,
            'total' => 0,
            'percentage' => 0,
            'current_page' => 1,
            'last_page' => 1,
            'message' => 'في الانتظار...',
        ]);

        return response()->json($progress);
    }
}
