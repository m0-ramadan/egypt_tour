<?php

namespace App\Services;

use Illuminate\Support\Arr;

class PackageAiService
{
    public function __construct(
        protected DeepSeekService $deepSeekService,
        protected TranslationService $translationService
    ) {}

    /**
     * Generate full package data from AI
     */
    public function generate(array $input): ?array
    {
        $prompt = trim($input['prompt'] ?? '');

        if ($prompt === '') {
            return null;
        }

        $destinationName = $input['destination_name'] ?? '';
        $categoryName = $input['category_name'] ?? '';
        $durationDays = $input['duration_days'] ?? null;

        $context = $this->buildPrompt($prompt, $destinationName, $categoryName, $durationDays);

        $result = $this->deepSeekService->askJson(
            prompt: $context,
            systemPrompt: 'You are a professional tourism package generator. Return ONLY valid JSON.',
            temperature: 0.4,
            maxTokens: 2500
        );

        if (!is_array($result)) {
            return null;
        }

        $data = $this->normalize($result);

        // 🔥 ترجمة كل الحقول النصية
        $data = $this->translationService->translateFields($data, [
            'title',
            'subtitle',
            'short_description',
            'description',
            'schedule_text',
            'pickup_location',
            'dropoff_location',
            'destinations_text',
            'location_summary',
            'tour_type',
            'difficulty_level',
            'booking_mode',
            'cancellation_policy',
            'terms_conditions',
            'seo_title',
            'seo_description',
            'breadcrumb_title',
        ]);

        return $data;
    }

    /**
     * Build AI prompt
     */
    protected function buildPrompt(string $prompt, ?string $destination, ?string $category, ?int $days): string
    {
        $duration = $days ? "مدة الرحلة: {$days} أيام" : '';

        return <<<PROMPT
أنت خبير سياحي محترف.

الوجهة: {$destination}
التصنيف: {$category}
{$duration}

وصف المستخدم:
{$prompt}

أعد النتيجة JSON فقط:

{
  "title": "",
  "subtitle": "",
  "short_description": "",
  "description": "",
  "duration_days": 0,
  "duration_nights": 0,
  "schedule_text": "",
  "pickup_location": "",
  "dropoff_location": "",
  "destinations_text": "",
  "location_summary": "",
  "tour_type": "",
  "difficulty_level": "",
  "booking_mode": "",
  "cancellation_policy": "",
  "terms_conditions": "",
  "seo_title": "",
  "seo_description": "",
  "breadcrumb_title": ""
}
PROMPT;
    }

    /**
     * Normalize AI response
     */
    protected function normalize(array $data): array
    {
        return [
            'title' => $this->str($data, 'title'),
            'subtitle' => $this->str($data, 'subtitle'),
            'short_description' => $this->str($data, 'short_description'),
            'description' => $this->str($data, 'description'),
            'duration_days' => $this->int($data, 'duration_days'),
            'duration_nights' => $this->int($data, 'duration_nights'),
            'schedule_text' => $this->str($data, 'schedule_text'),
            'pickup_location' => $this->str($data, 'pickup_location'),
            'dropoff_location' => $this->str($data, 'dropoff_location'),
            'destinations_text' => $this->str($data, 'destinations_text'),
            'location_summary' => $this->str($data, 'location_summary'),
            'tour_type' => $this->str($data, 'tour_type'),
            'difficulty_level' => $this->str($data, 'difficulty_level'),
            'booking_mode' => $this->str($data, 'booking_mode'),
            'cancellation_policy' => $this->str($data, 'cancellation_policy'),
            'terms_conditions' => $this->str($data, 'terms_conditions'),
            'seo_title' => $this->str($data, 'seo_title'),
            'seo_description' => $this->str($data, 'seo_description'),
            'breadcrumb_title' => $this->str($data, 'breadcrumb_title'),
        ];
    }

    protected function str(array $data, string $key): string
    {
        return trim((string) Arr::get($data, $key, ''));
    }

    protected function int(array $data, string $key): int
    {
        return (int) Arr::get($data, $key, 0);
    }
}
