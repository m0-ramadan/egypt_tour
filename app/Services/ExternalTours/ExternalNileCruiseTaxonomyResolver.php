<?php

namespace App\Services\ExternalTours;

use App\Models\NileCruiseCategory;
use App\Models\NileCruiseType;
use Illuminate\Support\Str;

class ExternalNileCruiseTaxonomyResolver
{
    public function resolve(array $facts): array
    {
        $result = ['nile_cruise_type_id' => null, 'nile_cruise_category_id' => null, 'warnings' => []];
        if (($facts['package_type'] ?? null) !== 'nile_cruise') {
            return $result;
        }

        // Breadcrumbs are authoritative; never infer a comfort tier from marketing copy.
        $breadcrumbs = $facts['breadcrumbs'] ?? [];
        $typeSlug = null;
        foreach (array_merge($breadcrumbs, [$facts['title'] ?? '', $facts['source_slug'] ?? '']) as $label) {
            $label = Str::slug($label);
            if (str_contains($label, 'lake-nasser')) {
                $typeSlug = 'lake-nasser-cruise';
            } elseif (preg_match('/dahabiya|dahabeya/', $label)) {
                $typeSlug = 'dahabiya-nile-cruise';
            } elseif (str_contains($label, 'luxor') && str_contains($label, 'aswan') && str_contains($label, 'cruise')) {
                $typeSlug = 'luxor-aswan-nile-cruises';
            }
            if ($typeSlug) {
                break;
            }
        }

        $categorySlug = null;
        foreach (array_reverse($breadcrumbs) as $label) {
            $label = Str::slug($label);
            if (preg_match('/^(standard|ultra-deluxe|deluxe|luxury)-nile-cruises?$/', $label, $match)) {
                $categorySlug = $match[1] . '-nile-cruises';
                $typeSlug ??= 'luxor-aswan-nile-cruises';
                break;
            }
        }

        $type = $typeSlug ? NileCruiseType::where('slug', $typeSlug)->where('is_active', true)->first() : null;
        if (!$type) {
            $result['warnings'][] = 'Nile Cruise Type could not be resolved to an active local type; assign it in the admin.';
            return $result;
        }
        $result['nile_cruise_type_id'] = $type->id;

        if ($type->slug === 'luxor-aswan-nile-cruises') {
            $category = $categorySlug ? NileCruiseCategory::where('nile_cruise_type_id', $type->id)
                ->where('slug', $categorySlug)->where('is_active', true)->first() : null;
            $result['nile_cruise_category_id'] = $category?->id;
            if (!$category) {
                $result['warnings'][] = 'Nile Cruise Category could not be resolved from source breadcrumbs; assign it in the admin.';
            }
        }

        return $result;
    }
}
