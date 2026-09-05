<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\PackageCategory;
use App\Models\SavvyTourTemplate;
use App\Services\ReadyTourImportService;
use App\Services\SavvyHostTourTemplateService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ReadyTourController extends Controller
{
    public function __construct(
        protected SavvyHostTourTemplateService $syncService,
        protected ReadyTourImportService $importService
    ) {}

    /**
     * Display listing of Ready Tours (AI Tour Templates) with stats & filters.
     */
    public function index(Request $request)
    {
        $query = SavvyTourTemplate::with(['importedPackage', 'previewMedia']);

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('remote_id', 'like', "%{$search}%")
                  ->orWhere('remote_slug', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%")
                  ->orWhere('remote_category', 'like', "%{$search}%");
            });
        }

        // Tour Type filter
        if ($request->filled('tour_type')) {
            $query->where('remote_tour_type', $request->input('tour_type'));
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('remote_category', $request->input('category'));
        }

        // Region filter
        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }

        // City filter
        if ($request->filled('city')) {
            $city = $request->input('city');
            $query->whereJsonContains('cities', $city);
        }

        // Difficulty filter
        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->input('difficulty'));
        }

        // Duration unit filter
        if ($request->filled('duration_unit')) {
            $query->where('duration_unit', $request->input('duration_unit'));
        }

        // Featured filter
        if ($request->has('featured') && $request->input('featured') !== '') {
            $query->where('remote_is_featured', (bool) $request->input('featured'));
        }

        // Import status filter
        if ($request->filled('import_status')) {
            $status = $request->input('import_status');
            if ($status === 'imported') {
                $query->whereIn('import_status', ['imported', 'imported_with_warnings']);
            } else {
                $query->where('import_status', $status);
            }
        }

        // Price range filter
        if ($request->filled('price_min')) {
            $query->where('suggested_min_price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('suggested_min_price', '<=', (float) $request->input('price_max'));
        }

        // Sort
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'popularity':
                $query->orderByDesc('popularity_score');
                break;
            case 'price_low':
                $query->orderBy('suggested_min_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('suggested_min_price', 'desc');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            case 'remote_sort':
                $query->orderBy('remote_sort_order');
                break;
            case 'newest':
            default:
                $query->orderByDesc('id');
                break;
        }

        $templates = $query->paginate(12)->withQueryString();

        // Calculate statistics
        $stats = [
            'total' => SavvyTourTemplate::count(),
            'imported' => SavvyTourTemplate::whereIn('import_status', ['imported', 'imported_with_warnings'])->count(),
            'not_imported' => SavvyTourTemplate::where('import_status', 'not_imported')->count(),
            'excursions' => SavvyTourTemplate::where('remote_tour_type', 'excursion')->count(),
            'packages' => SavvyTourTemplate::where('remote_tour_type', 'package')->count(),
            'nile_cruises' => SavvyTourTemplate::where('remote_tour_type', 'nile_cruise')->count(),
            'last_sync' => SavvyTourTemplate::max('last_synced_at'),
        ];

        // Filter options for dropdowns
        $categories = PackageCategory::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $regions = SavvyTourTemplate::query()
            ->whereNotNull('region')
            ->distinct()
            ->pluck('region');

        return view('admin.ready-tours.index', compact('templates', 'stats', 'categories', 'cities', 'regions'));
    }

    /**
     * Start SavvyHost templates synchronization.
     */
    public function sync(Request $request)
    {
        $processUuid = Str::uuid()->toString();
        $adminId = auth('admin')->id();

        if ($request->ajax() || $request->wantsJson()) {
            // Synchronous run for progress modal
            try {
                $result = $this->syncService->syncAll($processUuid, $adminId);

                return response()->json([
                    'success' => true,
                    'process_uuid' => $processUuid,
                    'result' => $result,
                    'message' => 'Synchronization completed successfully!',
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'process_uuid' => $processUuid,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        try {
            $this->syncService->syncAll($processUuid, $adminId);
            return redirect()->route('admin.ready-tours.index')
                ->with('success', 'Ready tours synchronized successfully from SavvyHost.');
        } catch (Exception $e) {
            return redirect()->route('admin.ready-tours.index')
                ->with('error', 'Sync error: ' . $e->getMessage());
        }
    }

    /**
     * Get real-time sync progress from Cache.
     */
    public function syncProgress(string $processUuid): JsonResponse
    {
        $cacheKey = $this->syncService->getProgressCacheKey($processUuid, auth('admin')->id());
        $data = Cache::get($cacheKey, [
            'status' => 'idle',
            'percentage' => 0,
            'message' => 'Preparing synchronization...',
        ]);

        return response()->json($data);
    }

    /**
     * Import single Ready Tour template into a local package.
     */
    public function import(Request $request, SavvyTourTemplate $template): JsonResponse
    {
        $processUuid = Str::uuid()->toString();

        try {
            $result = $this->importService->importTemplate($template, $processUuid, auth('admin')->id());

            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'package_id' => $result['package_id'],
                'message' => $result['message'],
                'warnings' => $result['warnings'],
                'redirect_url' => route('admin.packages.edit', $result['package_id']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get real-time import progress from Cache.
     */
    public function importProgress(string $processUuid): JsonResponse
    {
        $cacheKey = $this->importService->getProgressCacheKey($processUuid, auth('admin')->id());
        $data = Cache::get($cacheKey, [
            'status' => 'idle',
            'percentage' => 0,
            'message' => 'Preparing import...',
        ]);

        return response()->json($data);
    }

    /**
     * Bulk import selected template records.
     */
    public function importSelected(Request $request): JsonResponse
    {
        $request->validate([
            'template_ids' => 'required|array|min:1',
            'template_ids.*' => 'exists:savvy_tour_templates,id',
        ]);

        $processUuid = Str::uuid()->toString();
        $templateIds = $request->input('template_ids');

        try {
            $result = $this->importService->importMultiple($templateIds, $processUuid, auth('admin')->id());

            return response()->json([
                'success' => true,
                'process_uuid' => $processUuid,
                'result' => $result,
                'message' => "Successfully imported {$result['success_count']} tours.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk import all non-imported templates.
     */
    public function importAll(Request $request): JsonResponse
    {
        $templateIds = SavvyTourTemplate::query()
            ->whereNotIn('import_status', ['imported', 'imported_with_warnings'])
            ->pluck('id')
            ->toArray();

        if (empty($templateIds)) {
            return response()->json([
                'success' => true,
                'message' => 'All ready tours are already imported!',
                'result' => ['total' => 0, 'success_count' => 0],
            ]);
        }

        $processUuid = Str::uuid()->toString();

        try {
            $result = $this->importService->importMultiple($templateIds, $processUuid, auth('admin')->id());

            return response()->json([
                'success' => true,
                'process_uuid' => $processUuid,
                'result' => $result,
                'message' => "Successfully imported {$result['success_count']} tours.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
