<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        // Date filters
        if ($request->filled('starts_from')) {
            $query->where('starts_at', '>=', $request->starts_from);
        }
        
        if ($request->filled('starts_to')) {
            $query->where('starts_at', '<=', $request->starts_to);
        }
        
        if ($request->filled('expires_from')) {
            $query->where('expires_at', '>=', $request->expires_from);
        }
        
        if ($request->filled('expires_to')) {
            $query->where('expires_at', '<=', $request->expires_to);
        }

        // Value filters
        if ($request->filled('value_from')) {
            $query->where('value', '>=', $request->value_from);
        }
        
        if ($request->filled('value_to')) {
            $query->where('value', '<=', $request->value_to);
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Statistics
        $totalCoupons = Coupon::count();
        $activeCoupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })->count();
        
        $inactiveCoupons = Coupon::where('is_active', false)->count();
        $expiredCoupons = Coupon::where('expires_at', '<', now())->count();
        $percentageCoupons = Coupon::where('type', 'percentage')->count();
        $fixedCoupons = Coupon::where('type', 'fixed')->count();

        // Get paginated results
        $coupons = $query->paginate(20)->appends($request->all());

        return view('Admin.coupons.index', compact(
            'coupons',
            'totalCoupons',
            'activeCoupons',
            'inactiveCoupons',
            'expiredCoupons',
            'percentageCoupons',
            'fixedCoupons'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons,code|string|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $coupon = Coupon::create($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'تم إنشاء الكوبون بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        $usages = $coupon->usages()
            ->with('user')
            ->latest()
            ->paginate(20);

        $usageStatistics = [
            'total' => $coupon->usages()->count(),
            'today' => $coupon->usages()->whereDate('created_at', today())->count(),
            'this_week' => $coupon->usages()->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month' => $coupon->usages()->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('Admin.coupons.show', compact('coupon', 'usages', 'usageStatistics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $coupon)
    {
        return view('Admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id . '|string|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $coupon->update($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'تم تحديث الكوبون بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'تم حذف الكوبون بنجاح');
    }

    /**
     * Bulk actions for coupons.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'coupon_ids' => 'required|array',
            'coupon_ids.*' => 'exists:coupons,id',
        ]);

        $couponIds = $request->coupon_ids;

        switch ($request->action) {
            case 'activate':
                Coupon::whereIn('id', $couponIds)->update(['is_active' => true]);
                $message = 'تم تفعيل الكوبونات المحددة';
                break;

            case 'deactivate':
                Coupon::whereIn('id', $couponIds)->update(['is_active' => false]);
                $message = 'تم تعطيل الكوبونات المحددة';
                break;

            case 'delete':
                Coupon::whereIn('id', $couponIds)->delete();
                $message = 'تم حذف الكوبونات المحددة';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Export coupons.
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:excel,csv,pdf',
            'columns' => 'nullable|array',
        ]);

        $query = Coupon::query();

        // Apply filters same as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // ... (same filters as index method)

        $coupons = $query->get();

        // Implementation for export based on type
        // This is a simplified version - you'll need to implement
        // the actual export logic based on your needs

        return response()->json([
            'success' => true,
            'message' => 'Export functionality will be implemented',
        ]);
    }

    /**
     * Generate coupon code.
     */
    public function generateCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Coupon::where('code', $code)->exists());

        return response()->json([
            'success' => true,
            'code' => $code,
        ]);
    }

    /**
     * Validate coupon code.
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'except' => 'nullable|exists:coupons,id',
        ]);

        $exists = Coupon::where('code', $request->code)
            ->when($request->except, function ($q) use ($request) {
                $q->where('id', '!=', $request->except);
            })
            ->exists();

        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'الكود مستخدم بالفعل' : 'الكود متاح',
        ]);
    }
}