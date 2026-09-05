<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerItem;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
        public function show(BannerItem $bannerItem)
    {
        try {
            // Load relationships
            $bannerItem->load('promoCodes');
            
            return response()->json([
                'success' => true,
                'item' => $bannerItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات العنصر: ' . $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'banner_id' => 'required|exists:banners,id',
            'item_order' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_alt' => 'nullable|string|max:255',
            'link_url' => 'nullable|url',
            'link_target' => 'nullable|in:_blank,_self',
            'is_link_active' => 'nullable',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'tag_text' => 'nullable|string|max:50',
            'tag_color' => 'nullable|string|max:7',
            'tag_bg_color' => 'nullable|string|max:7',
            'is_active' => 'nullable',
            'promo_codes' => 'nullable|array',
            'promo_codes.*' => 'exists:promo_codes,id',
        ]);

        // Upload images
        $imagePath = $this->uploadImage($request->file('image'), 'banners');
        $mobileImagePath = $request->hasFile('mobile_image') 
            ? $this->uploadImage($request->file('mobile_image'), 'banners/mobile')
            : null;

        // Create banner item
        $bannerItem = BannerItem::create([
            'banner_id' => $validated['banner_id'],
            'item_order' => $validated['item_order'],
            'image_url' => $imagePath,
            'mobile_image_url' => $mobileImagePath,
            'image_alt' => $validated['image_alt'],
            'link_url' => $validated['link_url'],
            'link_target' => $validated['link_target'] ?? '_self',
            'is_link_active' => $validated['is_link_active']??true,
            'product_id' => $validated['product_id'],
            'category_id' => $validated['category_id'],
            'tag_text' => $validated['tag_text'],
            'tag_color' => $validated['tag_color'],
            'tag_bg_color' => $validated['tag_bg_color'],
            'is_active' => $validated['is_active']??true,
        ]);

        // Attach promo codes
        if (!empty($validated['promo_codes'])) {
            $bannerItem->promoCodes()->sync($validated['promo_codes']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Banner item added successfully.',
            'item' => $bannerItem->load('promoCodes')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BannerItem $bannerItem)
    {
        $validated = $request->validate([
            'item_order' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_alt' => 'nullable|string|max:255',
            'link_url' => 'nullable|url',
            'link_target' => 'nullable|in:_blank,_self',
            'is_link_active' => 'nullable',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'tag_text' => 'nullable|string|max:50',
            'tag_color' => 'nullable|string|max:7',
            'tag_bg_color' => 'nullable|string|max:7',
            'is_active' => 'nullable',
            'promo_codes' => 'nullable|array',
            'promo_codes.*' => 'exists:promo_codes,id',
        ]);

        // Update images if provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($bannerItem->image_url && Storage::exists($bannerItem->image_url)) {
                Storage::delete($bannerItem->image_url);
            }
            
            $validated['image_url'] = $this->uploadImage($request->file('image'), 'banners');
        }

        if ($request->hasFile('mobile_image')) {
            // Delete old mobile image
            if ($bannerItem->mobile_image_url && Storage::exists($bannerItem->mobile_image_url)) {
                Storage::delete($bannerItem->mobile_image_url);
            }
            
            $validated['mobile_image_url'] = $this->uploadImage($request->file('mobile_image'), 'banners/mobile');
        }

        // Remove image fields from validated array
        unset($validated['image'], $validated['mobile_image']);

        // Update banner item
        $bannerItem->update($validated);

        // Sync promo codes
        if (isset($validated['promo_codes'])) {
            $bannerItem->promoCodes()->sync($validated['promo_codes']);
        } else {
            $bannerItem->promoCodes()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Banner item updated successfully.',
            'item' => $bannerItem->fresh()->load('promoCodes')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BannerItem $bannerItem)
    {
        // Delete images
        if ($bannerItem->image_url && Storage::exists($bannerItem->image_url)) {
            Storage::delete($bannerItem->image_url);
        }
        
        if ($bannerItem->mobile_image_url && Storage::exists($bannerItem->mobile_image_url)) {
            Storage::delete($bannerItem->mobile_image_url);
        }

        $bannerItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner item deleted successfully.'
        ]);
    }

    /**
     * Toggle item status
     */
    public function toggleStatus(BannerItem $bannerItem)
    {
        $bannerItem->update(['is_active' => !$bannerItem->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $bannerItem->is_active
        ]);
    }

    /**
     * Reorder items
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:banner_items,id',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            BannerItem::where('id', $item['id'])->update(['item_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Upload image
     */
    private function uploadImage($image, $folder)
    {
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs($folder, $filename, 'public');
        
        return $path;
    }
}