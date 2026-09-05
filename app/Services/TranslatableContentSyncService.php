<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Attraction;
use App\Models\BlogCategory;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Itinerary;
use App\Models\Language;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Notification;
use App\Models\Package;
use App\Models\PackageAttraction;
use App\Models\PackageCategory;
use App\Models\PackageHighlight;
use App\Models\PackageInclusion;
use App\Models\PackagePrice;
use App\Models\PackageTag;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Review;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\Video;

class TranslatableContentSyncService
{
    public function __construct(
        protected TranslationService $translationService
    ) {}

    /**
     * كل موديل والحقول المترجمة فيه
     */
    protected array $translatableMap = [
        Country::class => ['name'],
        City::class => ['name'],
        Destination::class => ['name', 'short_description', 'description', 'seo_title', 'seo_description'],
        PackageCategory::class => ['name', 'description', 'seo_title', 'seo_description'],
        Package::class => [
            'title',
            'subtitle',
            'short_description',
            'description',
            'schedule_text',
            'pickup_location',
            'dropoff_location',
            'destinations_text',
            'location_summary',
            'cancellation_policy',
            'terms_conditions',
            'seo_title',
            'seo_description',
            'breadcrumb_title',
        ],
        Post::class => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
        Article::class => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
        ArticleCategory::class => ['name', 'description'],
        Attraction::class => ['name', 'description'],
        BlogCategory::class => ['name', 'description'],
        Faq::class => ['question', 'answer'],
        Form::class => ['name', 'description'],
        FormField::class => ['label', 'placeholder', 'help_text'],
        Itinerary::class => ['title', 'description'],
        Menu::class => ['name'],
        MenuItem::class => ['title'],
        Notification::class => ['title', 'message'],
        PackageAttraction::class => ['title', 'description'],
        PackageHighlight::class => ['title', 'description'],
        PackageInclusion::class => ['title', 'description'],
        PackagePrice::class => ['title', 'description', 'label', 'season_name', 'notes'],
        PackageTag::class => ['name'],
        Page::class => ['title', 'body', 'seo_title', 'seo_description'],
        PaymentMethod::class => ['name', 'description'],
        Review::class => ['title', 'content'],
        SeoMeta::class => ['title', 'description'],
        Setting::class => ['value'],
        Tag::class => ['name'],
        Testimonial::class => ['name', 'position', 'content'],
        Video::class => ['title', 'description'],
    ];

    public function syncNewLanguage(Language $language): void
    {
        $defaultLanguage = Language::query()
            ->where('is_default', true)
            ->first();

        foreach ($this->translatableMap as $modelClass => $fields) {
            $modelClass::query()->chunk(100, function ($items) use ($fields, $language, $defaultLanguage) {
                $pending = [];

                foreach ($items as $item) {
                    foreach ($fields as $field) {
                        $value = $item->{$field};

                        if (!is_array($value) || empty($value)) {
                            continue;
                        }

                        if (array_key_exists($language->code, $value)) {
                            continue;
                        }

                        $sourceText = $this->resolveSourceText($value, $defaultLanguage?->code);
                        $pending[$item->getKey() . ':' . $field] = $sourceText;
                    }
                }

                $translated = $this->translationService->translateTextsToLanguage($pending, $language->code);

                foreach ($items as $item) {
                    $dirty = false;

                    foreach ($fields as $field) {
                        $key = $item->getKey() . ':' . $field;
                        if (!array_key_exists($key, $pending)) {
                            continue;
                        }

                        $value = $item->{$field};
                        $value[$language->code] = $translated[$key] ?? $pending[$key];
                        $item->{$field} = $value;
                        $dirty = true;
                    }
                    if ($dirty) {
                        $item->save();
                    }
                }
            });
        }
    }

    public function removeLanguage(string $languageCode): void
    {
        if (trim($languageCode) === '') {
            return;
        }

        foreach ($this->translatableMap as $modelClass => $fields) {
            $modelClass::query()->chunk(100, function ($items) use ($fields, $languageCode) {
                foreach ($items as $item) {
                    $dirty = false;

                    foreach ($fields as $field) {
                        $value = $item->{$field};

                        if (!is_array($value) || !array_key_exists($languageCode, $value)) {
                            continue;
                        }

                        unset($value[$languageCode]);

                        $item->{$field} = $value;
                        $dirty = true;
                    }

                    if ($dirty) {
                        $item->save();
                    }
                }
            });
        }
    }

    protected function resolveSourceText(array $value, ?string $defaultLanguageCode = null): string
    {
        if ($defaultLanguageCode && !empty($value[$defaultLanguageCode])) {
            return (string) $value[$defaultLanguageCode];
        }

        if (!empty($value['en'])) {
            return (string) $value['en'];
        }

        if (!empty($value['ar'])) {
            return (string) $value['ar'];
        }

        foreach ($value as $translation) {
            if (is_string($translation) && trim($translation) !== '') {
                return trim($translation);
            }
        }

        return '';
    }

}
