<?php

namespace App\Http\Controllers\Admin;

use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Visitor::query();

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('ip', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%")
                  ->orWhere('device', 'like', "%{$search}%")
                  ->orWhere('path', 'like', "%{$search}%");
            });
        }

        if ($request->filled('device_type')) {
            $deviceType = $request->string('device_type');
            if ($deviceType === 'mobile') {
                $query->where('is_mobile', true);
            } elseif ($deviceType === 'desktop') {
                $query->where('is_desktop', true);
            } elseif ($deviceType === 'tablet') {
                $query->where('is_tablet', true);
            } elseif ($deviceType === 'bot') {
                $query->where('is_bot', true);
            }
        }

        if ($request->filled('date_range')) {
            $range = $request->string('date_range');
            if ($range === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($range === '7days') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($range === '30days') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        $totalVisitors = Visitor::count();
        $todayVisitors = Visitor::whereDate('created_at', today())->count();
        $monthVisitors = Visitor::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $uniqueVisitors = Visitor::distinct('ip')->count('ip');

        $mobileCount = Visitor::where('is_mobile', true)->count();
        $desktopCount = Visitor::where('is_desktop', true)->count();
        $tabletCount = Visitor::where('is_tablet', true)->count();
        $botCount = Visitor::where('is_bot', true)->count();

        $deviceTotal = max($totalVisitors, 1);
        $mobilePercent = round(($mobileCount / $deviceTotal) * 100, 1);
        $desktopPercent = round(($desktopCount / $deviceTotal) * 100, 1);
        $tabletPercent = round(($tabletCount / $deviceTotal) * 100, 1);

        $topCountries = Visitor::select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $topPlatforms = Visitor::select('platform', DB::raw('count(*) as total'))
            ->whereNotNull('platform')
            ->where('platform', '!=', '')
            ->groupBy('platform')
            ->orderByDesc('total')
            ->take(7)
            ->get();

        $topBrowsers = Visitor::select('browser', DB::raw('count(*) as total'))
            ->whereNotNull('browser')
            ->where('browser', '!=', '')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->take(7)
            ->get();

        $visitors = $query->latest()->paginate($this->perPage($request))->withQueryString();

        return $this->view('admin.visitors.index', compact(
            'visitors',
            'totalVisitors',
            'todayVisitors',
            'monthVisitors',
            'uniqueVisitors',
            'mobileCount',
            'desktopCount',
            'tabletCount',
            'botCount',
            'mobilePercent',
            'desktopPercent',
            'tabletPercent',
            'topCountries',
            'topPlatforms',
            'topBrowsers'
        ));
    }

    public function chartData(Request $request): JsonResponse
    {
        $year = $request->input('year');

        $query = Visitor::query();
        if (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        $topCountries = (clone $query)
            ->select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        if ($topCountries->isEmpty()) {
            $countries = ['Egypt', 'Saudi Arabia', 'UAE', 'United States', 'Germany'];
            $count = [50, 30, 20, 15, 10];
        } else {
            $countries = $topCountries->pluck('country')->all();
            $count = $topCountries->pluck('total')->all();
        }

        return response()->json([
            'countries' => $countries,
            'count' => $count,
            'labels' => $countries,
            'values' => $count,
        ]);
    }

    public function quickStats(): JsonResponse
    {
        return response()->json([
            'visitors_today' => Visitor::whereDate('created_at', today())->count(),
            'visitors_total' => Visitor::count(),
            'unique_visitors' => Visitor::distinct('ip')->count('ip'),
        ]);
    }

    public function ordersStats(int $year): JsonResponse
    {
        return response()->json(['year' => $year, 'months' => []]);
    }
}
