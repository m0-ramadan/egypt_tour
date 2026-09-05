<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Package;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class BaseWebsiteController extends Controller
{
    protected function active($query)
    {
        return $query->where('is_active', true);
    }

    protected function translated(mixed $value, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($value instanceof Model) {
            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return (string) (
                $value[$locale]
                ?? $value['en']
                ?? $value['ar']
                ?? $value['it']
                ?? $value['It']
                ?? reset($value)
                ?? ''
            );
        }

        return trim((string) ($value ?? ''));
    }

    protected function shortText(mixed $value, int $limit = 150): string
    {
        return Str::limit(strip_tags($this->translated($value)), $limit);
    }

    protected function localizedUiText(mixed $value, ?string $fallback = ''): string
    {
        $text = trim($this->translated($value));

        if ($text === '') {
            $text = trim((string) ($fallback ?? ''));
        }

        return $text !== '' ? __($text) : '';
    }

    protected function localizedModelText(?Model $model, string $attribute): string
    {
        if (!$model) {
            return '';
        }

        return $this->localizedUiText($model->getRawOriginal($attribute) ?? $model->{$attribute});
    }

    protected function packageTourTypeLabel(Package $package): string
    {
        $tourType = trim((string) $package->tour_type);

        if ($tourType === '') {
            return $this->typeLabel((string) $package->package_type);
        }

        return match (Str::lower(str_replace(['-', ' '], '_', $tourType))) {
            'private' => __('Private'),
            'group', 'small_group', 'small_group_tour' => __('Small Group Tour'),
            'shared' => __('Shared'),
            'custom' => __('Custom'),
            default => $this->localizedUiText($tourType),
        };
    }

    protected function packageScheduleLabel(Package $package): string
    {
        $schedule = $package->relationLoaded('cruise') && $package->cruise?->sailing_days
            ? $package->cruise->sailing_days
            : ($package->getRawOriginal('schedule_text') ?? $package->schedule_text);

        return $this->localizedUiText($schedule);
    }

    protected function localizedTagNames(iterable $items, int $limit = 0): array
    {
        $collection = collect($items);

        if ($limit > 0) {
            $collection = $collection->take($limit);
        }

        return $collection
            ->map(function ($item) {
                if ($item instanceof Model) {
                    return $this->localizedUiText($item->getRawOriginal('title') ?? $item->getRawOriginal('name') ?? $item->title ?? $item->name);
                }

                return $this->localizedUiText($item);
            })
            ->filter()
            ->values()
            ->all();
    }


    protected function packagePrice(Package $package): string
    {
        $priceFrom = $package->price_from ?? $package->start_from_price ?? $package->base_price ?? null;

        if ($priceFrom === null) {
            $firstPrice = $package->relationLoaded('prices') ? $package->prices->first() : null;
            $priceFrom = $firstPrice?->amount;
        }

        if ($priceFrom === null && !empty($package->price_to)) {
            $priceFrom = $package->price_to;
        }

        if ($priceFrom === null) {
            return __('Ask for price');
        }

        $currency = $package->relationLoaded('currency') ? $package->currency : null;
        $symbol = $currency?->symbol ?: Currency::query()->where('is_default', true)->value('symbol') ?: '$';

        $priceFrom = (float) $priceFrom;

        return __('From') . ' ' . $symbol . number_format($priceFrom, 0);
    }

    protected function packageDuration(Package $package): string
    {
        if (!empty($package->duration_text)) {
            $customDuration = $this->localizedUiText(
                $package->getRawOriginal('duration_text') ?? $package->duration_text
            );

            if (is_numeric($customDuration)) {
                $unit = $package->duration_type === 'hours' ? __('Hours') : __('Days');

                return $customDuration . ' ' . $unit;
            }

            return $customDuration;
        }

        if (!empty($package->duration_hours)) {
            return $package->duration_hours . ' ' . __('Hours');
        }

        $parts = collect([
            !empty($package->duration_days) ? $package->duration_days . ' ' . __('Days') : null,
            !empty($package->duration_nights) ? $package->duration_nights . ' ' . __('Nights') : null,
        ])->filter();

        if ($parts->isNotEmpty()) {
            return $parts->implode(' / ');
        }

        return __('Flexible');
    }

    protected function packageRoute(Package $package): string
    {
        return match ($package->package_type) {
            'day_tour', 'shore_excursion' => route('website.tours.show', $package->slug),
            default => route('website.trips.show', $package->slug),
        };
    }

    protected function getPackageImage(Package $package, ?string $fallback = null): string
    {
        if (!empty($package->featured_image)) {
            $url = $this->imageUrl($package->featured_image, '');
            if ($url !== '') {
                return $url;
            }
        }

        $gallery = $package->getRawOriginal('gallery_images') ?? $package->gallery_images;
        if (!empty($gallery)) {
            $decoded = is_array($gallery) ? $gallery : json_decode((string) $gallery, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $path = is_array($item) ? ($item['path'] ?? $item['url'] ?? null) : $item;
                    if ($path) {
                        $url = $this->imageUrl((string) $path, '');
                        if ($url !== '') {
                            return $url;
                        }
                    }
                }
            }
        }

        if ($package->relationLoaded('category') && !empty($package->category?->image)) {
            $url = $this->imageUrl($package->category->image, '');
            if ($url !== '') {
                return $url;
            }
        }

        if ($package->package_type === 'nile_cruise') {
            if ($package->relationLoaded('nileCruiseCategory') && $package->nileCruiseCategory?->image_url) {
                return $package->nileCruiseCategory->image_url;
            }
            if ($package->relationLoaded('nileCruiseType') && $package->nileCruiseType?->image_url) {
                return $package->nileCruiseType->image_url;
            }
            return asset('website/images/nile-cruises/luxor-aswan.jpg');
        }

        return $fallback ?: asset('website/photos/home2.webp');
    }

    protected function packageCard(Package $package): array
    {
        $highlights = $package->relationLoaded('highlights')
            ? $this->localizedTagNames($package->highlights, 4)
            : [];

        if (empty($highlights) && $package->relationLoaded('tags')) {
            $highlights = $this->localizedTagNames($package->tags, 4);
        }

        return [
            'id' => $package->id,
            'slug' => $package->slug,
            'title' => $this->translated($package->getRawOriginal('title') ?? $package->title),
            'subtitle' => $this->translated($package->getRawOriginal('subtitle') ?? $package->subtitle),
            'description' => $this->shortText($package->getRawOriginal('short_description') ?: $package->getRawOriginal('description'), 190),
            'image' => $this->getPackageImage($package),
            'price' => $this->packagePrice($package),
            'duration' => $this->packageDuration($package),
            'tour_type' => $this->packageTourTypeLabel($package),
            'route_text' => $package->route_text ?: $this->translated($package->getRawOriginal('destinations_text') ?? null),
            'is_ultra_luxury' => (bool) $package->is_ultra_luxury,
            'is_best_seller' => (bool) $package->is_best_seller,
            'url' => $this->packageRoute($package),
            'tags' => $highlights,
        ];
    }

    protected function packageListingCard(Package $package, ?string $buttonText = null): array
    {
        $highlights = $package->relationLoaded('highlights')
            ? $this->localizedTagNames($package->highlights, 2)
            : [];

        if (empty($highlights) && $package->relationLoaded('tags')) {
            $highlights = $this->localizedTagNames($package->tags, 2);
        }

        return [
            'title' => $this->translated($package->getRawOriginal('title') ?? $package->title),
            'url' => $this->packageRoute($package),
            'image' => $this->getPackageImage($package),
            'price' => $this->packagePrice($package),
            'badge' => $package->is_ultra_luxury
                ? __('Ultra Luxury')
                : ($package->is_best_seller ? __('Best Seller') : null),
            'duration' => $this->packageDuration($package),
            'tour_type' => $this->packageTourTypeLabel($package),
            'schedule' => $this->packageScheduleLabel($package),
            'country' => $this->localizedModelText($package->primaryCountry, 'name'),
            'description' => $this->shortText(
                $package->getRawOriginal('short_description') ?: $package->getRawOriginal('description'),
                170
            ),
            'highlights' => $highlights,
            'button_text' => $buttonText ?: $this->packageButtonText($package),
            'type_label' => match ((string) $package->package_type) {
                'day_tour' => __('Day Trip'),
                'travel_package' => __('Tour Package'),
                'nile_cruise' => __('Nile Cruise'),
                default => $this->typeLabel((string) $package->package_type),
            },
        ];
    }

    protected function packageButtonText(Package $package): string
    {
        return match ($package->package_type) {
            'day_tour', 'shore_excursion' => __('View Tour'),
            default => __('View Journey'),
        };
    }

    protected function typeLabel(string $type): string
    {
        return match ($type) {
            'travel_package' => __('Travel Packages'),
            'nile_cruise' => __('Nile Cruises'),
            'day_tour' => __('Day Tours'),
            'shore_excursion' => __('Shore Excursions'),
            'multi_country' => __('Multi Country Tours'),
            'deal' => __('Travel Deals'),
            'custom' => __('Tailor-made Trips'),
            default => Str::headline(str_replace('_', ' ', $type)),
        };
    }

    protected function lang(): string
    {
        return app()->getLocale() ?: 'en';
    }

    protected function transValue($value, ?string $fallback = ''): string
    {
        if ($value === null) {
            return $fallback ?? '';
        }

        if (is_array($value)) {
            return (string) ($value[$this->lang()] ?? $value['en'] ?? $value['ar'] ?? reset($value) ?? $fallback ?? '');
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return (string) ($decoded[$this->lang()] ?? $decoded['en'] ?? $decoded['ar'] ?? reset($decoded) ?? $fallback ?? '');
            }
            return $value;
        }

        return (string) $value;
    }

    protected function cleanHtml(?string $html): string
    {
        return trim((string) $html);
    }

    protected function plainText(?string $html, int $limit = 180): string
    {
        return Str::limit(trim(strip_tags((string) $html)), $limit);
    }

    protected function imageUrl(?string $path, ?string $fallback = null): string
    {
        $fallback = $fallback ?: asset('website/photos/home2.webp');

        if (!$path) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            $storagePath = Str::after($path, 'storage/');

            return Storage::disk('public')->exists($storagePath) || file_exists(public_path($path))
                ? asset($path)
                : $fallback;
        }

        if (Str::startsWith($path, 'website/')) {
            return file_exists(public_path($path)) ? asset($path) : $fallback;
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return $fallback;
    }

    protected function money($amount, ?string $symbol = '$'): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return ($symbol ?: '$') . rtrim(rtrim(number_format((float) $amount, 2), '0'), '.');
    }
}
