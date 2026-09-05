<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerService
{
    /**
     * Get filtered banners
     *
     * @param Request $request
     * @param bool $paginate
     * @return mixed
     */
    public function getFilteredBanners(Request $request, $paginate = true)
    {
        $query = Banner::with(['type', 'items', 'gridLayout', 'sliderSetting']);

        // Filter by type
        if ($request->has('type') && $request->type != 'all') {
            $query->whereHas('type', function ($q) use ($request) {
                $q->where('name', $request->type);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('is_active', $request->status == 'active');
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Search by title
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Order by
        $orderBy = $request->get('order_by', 'section_order');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        return $paginate 
            ? $query->paginate($request->get('per_page', 10))
            : $query->get();
    }

    /**
     * Create grid layout
     *
     * @param int $bannerId
     * @param array $data
     * @return void
     */
    public function createGridLayout($bannerId, $data)
    {
        \App\Models\BannerGridLayout::create([
            'banner_id' => $bannerId,
            'grid_type' => $data['grid_type'] ?? 'responsive',
            'desktop_columns' => $data['desktop_columns'] ?? 3,
            'tablet_columns' => $data['tablet_columns'] ?? 2,
            'mobile_columns' => $data['mobile_columns'] ?? 1,
            'row_gap' => $data['row_gap'] ?? 20,
            'column_gap' => $data['column_gap'] ?? 20,
        ]);
    }

    /**
     * Create slider setting
     *
     * @param int $bannerId
     * @param array $data
     * @return void
     */
    public function createSliderSetting($bannerId, $data)
    {
        \App\Models\SliderSetting::create([
            'banner_id' => $bannerId,
            'autoplay' => $data['autoplay'] ?? true,
            'autoplay_speed' => $data['autoplay_speed'] ?? 3000,
            'arrows' => $data['arrows'] ?? true,
            'dots' => $data['dots'] ?? true,
            'infinite' => $data['infinite'] ?? true,
        ]);
    }

    /**
     * Validate banner data
     *
     * @param array $data
     * @return array
     */
    public function validateBannerData(array $data)
    {
        return [
            'title' => 'required|string|max:255',
            'banner_type_id' => 'required|exists:banner_types,id',
            'section_order' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}