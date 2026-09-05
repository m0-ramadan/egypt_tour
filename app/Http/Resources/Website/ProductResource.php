<?php

namespace App\Http\Resources\Website;

use App\Models\Favorite;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->get('Accept-Language', app()->getLocale());

        return [
            // ================== Basic Info ==================
            'id'                => $this->id,
            'name'              => $this->getTranslation('name', $locale),
            'slug'              => $this->slug ?? Str::slug($this->getTranslation('name', 'en')),
            'description'       => $this->getTranslation('description', $locale),
            'price'             => $this->price . 'EGP',
            'price_text' => $this->resolvedPriceText($locale),
            'final_price'       => $this->resolvedPriceText($locale),
            'has_discount'      => (bool) $this->has_discount,
            'includes_tax'      => (bool) $this->includes_tax,
            'includes_shipping' => (bool) $this->includes_shipping,
            'stock'             => (int) $this->stock,
            'status_id'         => (int) $this->status_id,
            'is_active'         => $this->status_id == 1,
            'tax_amount'        => $this->tax_amount ?? 0,

            // ================== Policies ==================
            'instructions'      => null,
            'terms'             => null,

            // ================== Ads ==================
            'text_ads'          => ProductTextAdResource::collection($this->adsText),

            // ================== Image ==================
            'image' => $this->image_path
                ? asset('storage/' . $this->image_path)
                : ($this->primaryImage
                    ? get_user_image($this->primaryImage->path)
                    : url(config('app.default_product_image'))),

            // ================== Rating ==================
            'average_rating'    => round($this->average_rating, 1),
            'total_reviews'     => $this->reviews_count ?? $this->reviews->count(),

            // ================== Favorite ==================
            'is_favorite' => auth()->check()
                ? Favorite::where('user_id', auth()->id())
                ->where('product_id', $this->id)
                ->exists()
                : false,

            // ================== Relations ==================
            'category' => $this->category
                ? new CategoryResource($this->category)
                : null,

            'discount' => $this->discount
                ? new DiscountResource($this->discount)
                : null,

            // ================== Features ==================
            'features' => FeatureResource::collection($this->features),

            // ================== Reviews ==================
            'reviews' => $this->reviews
                ? ReviewResource::collection($this->reviews)
                : null,

            // ================== Offers ==================
            'offers' => $this->offers
                ? OfferResource::collection($this->offers)
                : null,

            // ================== Images ==================
            'images' => $this->images
                ? $this->images
                ->sortBy('order')
                ->values()
                ->map(function ($image) {
                    return [
                        'id'         => $image->id,
                        'path'       => get_user_image($image->path),
                        'alt'        => $image->alt,
                        'type'       => $image->type,
                        'order'      => $image->order,
                        'is_primary' => (bool) $image->is_primary,
                        'is_active'  => (bool) $image->is_active,
                    ];
                })
                : null,

            // ================== Translations Info ==================
            'available_translations' => $this->getAvailableTranslations(),

            // ================== Dates ==================
            'created_at'       => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i'),
            'human_created_at' => $this->created_at?->diffForHumans(),
            'human_updated_at' => $this->updated_at?->diffForHumans(),

            // ================== Meta ==================
            'meta' => [
                'in_stock'          => $this->stock > 0,
                'stock_status'      => $this->stock > 0 ? 'متوفر' : 'نفذت الكمية',
                'stock_class'       => $this->stock > 0 ? 'in-stock' : 'out-of-stock',
                'meta_title'        => $this->getTranslation('meta_title', $locale),
                'meta_description'  => $this->getTranslation('meta_description', $locale),
                'meta_keywords'     => $this->getTranslation('meta_keywords', $locale),
            ],
        ];
    }

    /**
     * الحصول على معلومات الترجمات المتاحة
     */
    protected function getAvailableTranslations(): array
    {
        $translations = [];

        foreach ($this->translatable as $field) {
            $translations[$field] = array_keys($this->getTranslations($field));
        }

        return $translations;
    }
    protected function resolvedPriceText(string $locale): ?string
    {
        // 1) لو null → fallback من price
        if (is_null($this->price_text) || $this->price_text === '') {
            return $this->price ? ($this->price . ' EGP') : null;
        }

        // 2) لو Spatie رجّعها Array (الصح)
        if (is_array($this->price_text)) {
            return $this->getTranslation('price_text', $locale, false)
                ?: $this->getTranslation('price_text', 'ar', false)
                ?: (count($this->price_text) ? reset($this->price_text) : null);
        }

        // 3) لو String (قديمة/مش متحوّلة)
        if (is_string($this->price_text)) {

            $trim = trim($this->price_text);

            // لو String شكله JSON
            if (str_starts_with($trim, '{') && str_ends_with($trim, '}')) {
                $decoded = json_decode($trim, true);
                if (is_array($decoded)) {
                    return $decoded[$locale] ?? $decoded['ar'] ?? (count($decoded) ? reset($decoded) : null);
                }
            }

            // String عادي
            return $this->price_text;
        }

        return null;
    }
}
