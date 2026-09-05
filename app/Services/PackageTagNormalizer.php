<?php

namespace App\Services;

use Illuminate\Support\Str;

class PackageTagNormalizer
{
    public function normalizeList(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = preg_split('/[\r\n,]+/u', $raw) ?: [];
        }

        $normalized = [];
        foreach ((array) $raw as $item) {
            if (is_array($item)) {
                $item = $item['name'] ?? $item['en'] ?? reset($item) ?: '';
            }

            $name = $this->normalizeName((string) $item);
            if ($name === '') {
                continue;
            }

            $duplicateKey = mb_strtolower($name, 'UTF-8');
            $normalized[$duplicateKey] ??= $name;
        }

        return array_values($normalized);
    }

    public function normalizeName(string $name): string
    {
        $name = preg_replace('/^[\s#＃]+/u', '', trim($name)) ?? '';
        $name = preg_replace('/[\p{Z}\s]+/u', ' ', $name) ?? '';

        return trim($name);
    }

    public function slug(string $name): string
    {
        $canonical = mb_strtolower($this->normalizeName($name), 'UTF-8');
        $slug = Str::slug($canonical, '-', null);

        if ($slug === '') {
            $slug = 'tag-' . substr(hash('sha256', $canonical), 0, 20);
        }

        if (mb_strlen($slug, 'UTF-8') > 130) {
            $hash = substr(hash('sha256', $canonical), 0, 12);
            $slug = rtrim(mb_substr($slug, 0, 117, 'UTF-8'), '-') . '-' . $hash;
        }

        return $slug;
    }

    public function validationErrors(mixed $raw): array
    {
        $names = $this->normalizeList($raw);
        $errors = [];
        $maxCount = (int) config('translation.max_tag_count', 30);
        $maxLength = (int) config('translation.max_tag_length', 500);

        if (count($names) > $maxCount) {
            $errors[] = "A package may contain at most {$maxCount} tags.";
        }

        foreach ($names as $name) {
            if (mb_strlen($name, 'UTF-8') > $maxLength) {
                $errors[] = "Each package tag may contain at most {$maxLength} characters.";
                break;
            }
        }

        return $errors;
    }
}
