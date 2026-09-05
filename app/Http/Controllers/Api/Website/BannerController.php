<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\BannerResource;

class BannerController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        $query = Banner::with([
            'type',
            'items',
            'sliderSetting',
            'gridLayout'
        ])->where('is_active', true);
        // فلترة حسب النوع لو موجودة
        if ($request->has('type')) {
            $query->whereHas('type', function ($q) use ($request) {
                $q->where('name', $request->type);
            });
        }

        $today = now();
        $query->where(function ($q) use ($today) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
        });

        // ترتيب حسب section_order
        $banners = $query->orderBy('section_order')->get();
        return $this->successResponse(BannerResource::collection($banners), 'تم جلب البانر بنجاح');
    }
}
