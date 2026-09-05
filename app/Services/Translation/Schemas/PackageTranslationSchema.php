<?php

namespace App\Services\Translation\Schemas;

use App\Models\Package;
use App\Models\Itinerary;
use App\Models\PackageHighlight;
use App\Models\PackageInclusion;
use App\Models\NileCruiseCabin;
use App\Models\NileCruiseDuration;
use App\Models\NileCruiseItineraryDay;
use App\Services\Translation\DTOs\TranslationUnit;

class PackageTranslationSchema
{
    /**
     * Build all TranslationUnits for a Package model across active target languages.
     *
     * @param Package $package
     * @param string $sourceLang
     * @param array $targetLanguages
     * @param bool $missingOnly
     * @return TranslationUnit[]
     */
    public function extractUnits(Package $package, string $sourceLang, array $targetLanguages, bool $missingOnly = true): array
    {
        $units = [];

        foreach ($targetLanguages as $targetLang) {
            if (strtolower($sourceLang) === strtolower($targetLang)) {
                continue;
            }

            // 1. Core Package Text Fields
            $coreFields = [
                'title' => 'text',
                'subtitle' => 'text',
                'short_description' => 'html',
                'description' => 'html',
                'schedule_text' => 'text',
                'pickup_location' => 'text',
                'dropoff_location' => 'text',
                'destinations_text' => 'text',
                'location_summary' => 'text',
                'cancellation_policy' => 'html',
                'terms_conditions' => 'html',
                'seo_title' => 'text',
                'seo_description' => 'text',
                'breadcrumb_title' => 'text',
            ];

            foreach ($coreFields as $field => $type) {
                $translations = $package->getTranslations($field);
                $sourceText = $translations[$sourceLang] ?? null;
                $targetText = $translations[$targetLang] ?? null;

                if (empty(trim((string) $sourceText))) {
                    continue;
                }

                if ($missingOnly && !empty(trim((string) $targetText))) {
                    continue; // Skip already translated manual or existing content
                }

                // If description is large, chunk by paragraphs if needed
                $units[] = new TranslationUnit(
                    entityType: 'package',
                    entityId: $package->id,
                    field: $field,
                    sourceLanguage: $sourceLang,
                    targetLanguage: $targetLang,
                    sourceText: (string) $sourceText,
                    structuredType: $type
                );
            }

            // 2. FAQs (json_array / faq_json)
            if (!empty($package->faq_json) && is_array($package->faq_json)) {
                $faqUnits = $this->extractFaqUnits($package, $sourceLang, $targetLang, $missingOnly);
                $units = array_merge($units, $faqUnits);
            }

            // 3. What to Bring (json_array)
            if (!empty($package->what_to_bring) && is_array($package->what_to_bring)) {
                $wtbUnits = $this->extractJsonArrayUnits($package, 'what_to_bring', $sourceLang, $targetLang, $missingOnly);
                $units = array_merge($units, $wtbUnits);
            }

            // 4. Highlights
            foreach ($package->highlights as $highlight) {
                $hTranslations = $highlight->getTranslations('title');
                $srcH = $hTranslations[$sourceLang] ?? null;
                $tgtH = $hTranslations[$targetLang] ?? null;

                if (!empty(trim((string) $srcH)) && (!$missingOnly || empty(trim((string) $tgtH)))) {
                    $units[] = new TranslationUnit(
                        entityType: 'package_highlight',
                        entityId: $highlight->id,
                        field: 'title',
                        sourceLanguage: $sourceLang,
                        targetLanguage: $targetLang,
                        sourceText: (string) $srcH,
                        structuredType: 'text'
                    );
                }
            }

            // 5. Inclusions / Exclusions
            foreach ($package->inclusions as $inclusion) {
                $iTranslations = $inclusion->getTranslations('content');
                $srcI = $iTranslations[$sourceLang] ?? null;
                $tgtI = $iTranslations[$targetLang] ?? null;

                if (!empty(trim((string) $srcI)) && (!$missingOnly || empty(trim((string) $tgtI)))) {
                    $units[] = new TranslationUnit(
                        entityType: 'package_inclusion',
                        entityId: $inclusion->id,
                        field: 'content',
                        sourceLanguage: $sourceLang,
                        targetLanguage: $targetLang,
                        sourceText: (string) $srcI,
                        structuredType: 'text'
                    );
                }
            }

            // 6. Itineraries (Day-by-Day)
            foreach ($package->itineraries as $itinerary) {
                foreach (['title' => 'text', 'description' => 'html', 'overnight_location' => 'text'] as $itField => $itType) {
                    $itTrans = $itinerary->getTranslations($itField);
                    $srcIt = $itTrans[$sourceLang] ?? null;
                    $tgtIt = $itTrans[$targetLang] ?? null;

                    if (!empty(trim((string) $srcIt)) && (!$missingOnly || empty(trim((string) $tgtIt)))) {
                        $units[] = new TranslationUnit(
                            entityType: 'itinerary',
                            entityId: $itinerary->id,
                            field: $itField,
                            sourceLanguage: $sourceLang,
                            targetLanguage: $targetLang,
                            sourceText: (string) $srcIt,
                            structuredType: $itType
                        );
                    }
                }
            }

            // 7. Nile Cruise Specific Content (Cabins & Durations)
            if ($package->package_type === 'nile_cruise') {
                foreach ($package->nileCruiseCabins as $cabin) {
                    // Translate cabin name / description if available
                    if (!empty($cabin->description) && is_array($cabin->description)) {
                        $srcCab = $cabin->description[$sourceLang] ?? null;
                        $tgtCab = $cabin->description[$targetLang] ?? null;
                        if (!empty(trim((string) $srcCab)) && (!$missingOnly || empty(trim((string) $tgtCab)))) {
                            $units[] = new TranslationUnit(
                                entityType: 'nile_cruise_cabin',
                                entityId: $cabin->id,
                                field: 'description',
                                sourceLanguage: $sourceLang,
                                targetLanguage: $targetLang,
                                sourceText: (string) $srcCab,
                                structuredType: 'text'
                            );
                        }
                    }
                }

                foreach ($package->nileCruiseDurations as $duration) {
                    foreach ($duration->itineraryDays as $ncDay) {
                        foreach (['title' => 'text', 'description' => 'html', 'overnight' => 'text'] as $ncField => $ncType) {
                            $ncTrans = $ncDay->getTranslations($ncField);
                            $srcNc = $ncTrans[$sourceLang] ?? null;
                            $tgtNc = $ncTrans[$targetLang] ?? null;

                            if (!empty(trim((string) $srcNc)) && (!$missingOnly || empty(trim((string) $tgtNc)))) {
                                $units[] = new TranslationUnit(
                                    entityType: 'nile_cruise_itinerary_day',
                                    entityId: $ncDay->id,
                                    field: $ncField,
                                    sourceLanguage: $sourceLang,
                                    targetLanguage: $targetLang,
                                    sourceText: (string) $srcNc,
                                    structuredType: $ncType
                                );
                            }
                        }
                    }
                }
            }
        }

        return $units;
    }

    protected function extractFaqUnits(Package $package, string $sourceLang, string $targetLang, bool $missingOnly): array
    {
        $faqs = $package->faq_json ?: [];
        $sourceFaqs = [];
        $hasMissing = false;

        foreach ($faqs as $index => $faq) {
            $q = $faq['question'] ?? $faq['q'] ?? [];
            $a = $faq['answer'] ?? $faq['a'] ?? [];

            $srcQ = is_array($q) ? ($q[$sourceLang] ?? null) : (string) $q;
            $srcA = is_array($a) ? ($a[$sourceLang] ?? null) : (string) $a;

            $tgtQ = is_array($q) ? ($q[$targetLang] ?? null) : null;
            $tgtA = is_array($a) ? ($a[$targetLang] ?? null) : null;

            if (!empty($srcQ) || !empty($srcA)) {
                $sourceFaqs[] = [
                    'q' => (string) $srcQ,
                    'a' => (string) $srcA,
                ];

                if (empty($tgtQ) || empty($tgtA)) {
                    $hasMissing = true;
                }
            }
        }

        if (empty($sourceFaqs) || ($missingOnly && !$hasMissing)) {
            return [];
        }

        // Batch FAQs in groups of 5
        $batches = array_chunk($sourceFaqs, 5);
        $units = [];

        foreach ($batches as $bIndex => $batch) {
            $units[] = new TranslationUnit(
                entityType: 'package_faq_batch',
                entityId: $package->id,
                field: 'faq_json_batch_' . $bIndex,
                sourceLanguage: $sourceLang,
                targetLanguage: $targetLang,
                sourceText: json_encode($batch, JSON_UNESCAPED_UNICODE),
                structuredType: 'faq_json'
            );
        }

        return $units;
    }

    protected function extractJsonArrayUnits(Package $package, string $field, string $sourceLang, string $targetLang, bool $missingOnly): array
    {
        $items = $package->{$field} ?: [];
        if (!is_array($items)) {
            return [];
        }

        $sourceItems = [];
        $hasMissing = false;

        foreach ($items as $item) {
            if (is_array($item)) {
                $src = $item[$sourceLang] ?? null;
                $tgt = $item[$targetLang] ?? null;
                if (!empty($src)) {
                    $sourceItems[] = (string) $src;
                    if (empty($tgt)) {
                        $hasMissing = true;
                    }
                }
            } elseif (is_string($item) && !empty($item)) {
                $sourceItems[] = $item;
                $hasMissing = true;
            }
        }

        if (empty($sourceItems) || ($missingOnly && !$hasMissing)) {
            return [];
        }

        return [
            new TranslationUnit(
                entityType: 'package',
                entityId: $package->id,
                field: $field,
                sourceLanguage: $sourceLang,
                targetLanguage: $targetLang,
                sourceText: json_encode($sourceItems, JSON_UNESCAPED_UNICODE),
                structuredType: 'json_array'
            )
        ];
    }
}
