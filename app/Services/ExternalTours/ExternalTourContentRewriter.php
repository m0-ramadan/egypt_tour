<?php

namespace App\Services\ExternalTours;

use App\Services\DeepSeekService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalTourContentRewriter
{
    public function __construct(
        protected ?DeepSeekService $deepSeekService = null
    ) {
        if (!$this->deepSeekService && class_exists(DeepSeekService::class)) {
            try {
                $this->deepSeekService = app(DeepSeekService::class);
            } catch (\Throwable) {
                $this->deepSeekService = null;
            }
        }
    }

    /**
     * Rewrite marketing copy while strictly preserving factual tour information.
     *
     * @param array<string, mixed> $parsedData
     * @return array{data: array<string, mixed>, warnings: array<string>}
     */
    public function rewrite(array $parsedData): array
    {
        $warnings = [];

        // If DeepSeek is not configured or fails, smoothly fallback
        if (!$this->deepSeekService || empty(config('services.deepseek.api_key'))) {
            $warnings[] = 'DeepSeek API key is not configured; applied fallback light rewrite.';
            return [
                'data' => $this->fallbackRewrite($parsedData),
                'warnings' => $warnings,
            ];
        }

        try {
            $prompt = $this->buildPrompt($parsedData);
            $systemPrompt = $this->buildSystemPrompt();

            $aiResult = $this->deepSeekService->askJson($prompt, $systemPrompt, 0.2, 3500);

            if (!$aiResult || !is_array($aiResult)) {
                $warnings[] = 'DeepSeek returned null or invalid JSON; applied fallback light rewrite.';
                return [
                    'data' => $this->fallbackRewrite($parsedData),
                    'warnings' => $warnings,
                ];
            }

            $mergedData = $this->mergeRewrittenContent($parsedData, $aiResult);

            return [
                'data' => $mergedData,
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            Log::warning('ExternalTourContentRewriter error, using fallback', [
                'error' => $e->getMessage(),
            ]);

            $warnings[] = "AI rewriting failed ({$e->getMessage()}); applied fallback light rewrite.";

            return [
                'data' => $this->fallbackRewrite($parsedData),
                'warnings' => $warnings,
            ];
        }
    }

    /**
     * Build the strict prompt with factual tour data.
     */
    protected function buildPrompt(array $data): string
    {
        $payload = [
            'title' => $data['title'] ?? '',
            'subtitle' => $data['subtitle'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'description' => $data['description'] ?? '',
            'duration' => $data['duration_text'] ?? '',
            'cities' => $data['cities'] ?? [],
            'route' => $data['route_text'] ?? '',
            'highlights' => array_map(fn($h) => is_array($h) ? ($h['title'] ?? '') : $h, $data['highlights'] ?? []),
            'itinerary' => array_map(function ($day) {
                return [
                    'day_number' => $day['day_number'] ?? 1,
                    'title' => $day['title'] ?? '',
                    'description' => $day['description'] ?? '',
                    'meals' => $day['meals'] ?? [],
                    'overnight' => $day['overnight_location'] ?? '',
                ];
            }, $data['itinerary'] ?? []),
            'faq' => $data['faq'] ?? [],
        ];

        return "Using ONLY the supplied structured tour facts, write original TravelNest tourism copy.\n\n"
            . "INPUT TOUR FACTS:\n" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n"
            . "Return a JSON object with this exact schema:\n"
            . "{\n"
            . "  \"title\": \"Original professional tour title\",\n"
            . "  \"subtitle\": \"Engaging subtitle or null\",\n"
            . "  \"short_description\": \"Compelling 2-3 sentence overview\",\n"
            . "  \"description\": \"Full polished multi-paragraph tour description\",\n"
            . "  \"highlights\": [{\"title\": \"...\", \"description\": \"...\"}],\n"
            . "  \"itinerary\": [{\"day_number\": 1, \"title\": \"...\", \"description\": \"...\"}],\n"
            . "  \"faq\": [{\"question\": \"...\", \"answer\": \"...\"}],\n"
            . "  \"seo_title\": \"SEO title under 65 chars without source brand\",\n"
            . "  \"seo_description\": \"SEO meta description under 155-160 chars\",\n"
            . "  \"breadcrumb_title\": \"Short breadcrumb title\"\n"
            . "}";
    }

    /**
     * System prompt with strict preservation instructions.
     */
    protected function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are an expert tourism copywriter for TravelNest.
Using ONLY the supplied structured tour facts, write original TravelNest tourism copy.

Rules:
- Preserve all factual information.
- Preserve every price exactly.
- Preserve hotel names.
- Preserve cruise names.
- Preserve city names.
- Preserve attraction names.
- Preserve duration.
- Preserve meals.
- Preserve included services.
- Preserve excluded services.
- Preserve transportation facts.
- Preserve itinerary day order.

Do NOT invent:
- attractions
- meals
- hotels
- services
- transport
- flights
- prices
- policies

Rewrite:
- description
- short description
- highlights
- FAQ wording
- itinerary narrative wording

Do not reuse the source sentence structure.
Do not include any source website branding or domains (e.g. Luxor and Aswan Travel).
Return valid JSON only.
PROMPT;
    }

    /**
     * Merge AI rewritten fields into the parsed data, guaranteeing facts remain unmodified.
     */
    protected function mergeRewrittenContent(array $original, array $rewritten): array
    {
        $merged = $original;

        if (!empty($rewritten['title']) && is_string($rewritten['title'])) {
            $merged['title'] = trim($rewritten['title']);
        }

        if (!empty($rewritten['subtitle']) && is_string($rewritten['subtitle'])) {
            $merged['subtitle'] = trim($rewritten['subtitle']);
        }

        if (!empty($rewritten['short_description']) && is_string($rewritten['short_description'])) {
            $merged['short_description'] = trim($rewritten['short_description']);
        }

        if (!empty($rewritten['description']) && is_string($rewritten['description'])) {
            $merged['description'] = trim($rewritten['description']);
        }

        // Merge highlights if array
        if (!empty($rewritten['highlights']) && is_array($rewritten['highlights'])) {
            $newHighlights = [];
            foreach ($rewritten['highlights'] as $index => $item) {
                if (is_array($item)) {
                    $newHighlights[] = [
                        'title' => trim($item['title'] ?? ($original['highlights'][$index]['title'] ?? '')),
                        'description' => trim($item['description'] ?? ($item['title'] ?? '')),
                        'sort_order' => $index + 1,
                    ];
                } elseif (is_string($item)) {
                    $newHighlights[] = [
                        'title' => trim($item),
                        'description' => trim($item),
                        'sort_order' => $index + 1,
                    ];
                }
            }
            if (!empty($newHighlights)) {
                $merged['highlights'] = $newHighlights;
            }
        }

        // Merge daily itinerary narratives without altering factual fields (day_number, meals, etc.)
        if (!empty($rewritten['itinerary']) && is_array($rewritten['itinerary'])) {
            $rewrittenMap = [];
            foreach ($rewritten['itinerary'] as $item) {
                if (isset($item['day_number'])) {
                    $rewrittenMap[(int) $item['day_number']] = $item;
                }
            }

            foreach ($merged['itinerary'] as $idx => $day) {
                $dayNum = (int) ($day['day_number'] ?? ($idx + 1));
                if (isset($rewrittenMap[$dayNum])) {
                    if (!empty($rewrittenMap[$dayNum]['title'])) {
                        $merged['itinerary'][$idx]['title'] = trim($rewrittenMap[$dayNum]['title']);
                    }
                    if (!empty($rewrittenMap[$dayNum]['description'])) {
                        $merged['itinerary'][$idx]['description'] = trim($rewrittenMap[$dayNum]['description']);
                    }
                }
            }
        }

        // Merge FAQ if provided
        if (!empty($rewritten['faq']) && is_array($rewritten['faq'])) {
            $merged['faq'] = array_values(array_filter($rewritten['faq'], function ($faq) {
                return !empty($faq['question']) && !empty($faq['answer']);
            }));
        }

        // SEO metadata
        $titleForSeo = $merged['title'] ?? $original['title'] ?? 'Tour Package';
        $merged['seo_title'] = !empty($rewritten['seo_title'])
            ? Str::limit(trim($rewritten['seo_title']), 65, '')
            : Str::limit($titleForSeo, 65, '');

        $descForSeo = $merged['short_description'] ?? $merged['description'] ?? '';
        $merged['seo_description'] = !empty($rewritten['seo_description'])
            ? Str::limit(trim($rewritten['seo_description']), 160, '')
            : Str::limit(strip_tags($descForSeo), 160, '');

        $merged['breadcrumb_title'] = !empty($rewritten['breadcrumb_title'])
            ? Str::limit(trim($rewritten['breadcrumb_title']), 50, '')
            : Str::limit($titleForSeo, 50, '');

        return $merged;
    }

    /**
     * Fallback light rewriting: cleans text, formats SEO, and preserves 100% of facts.
     */
    public function fallbackRewrite(array $data): array
    {
        $data['title'] = trim(preg_replace('/\s+/', ' ', strip_tags($data['title'] ?? '')));
        $data['subtitle'] = !empty($data['subtitle']) ? trim(strip_tags($data['subtitle'])) : null;
        $data['short_description'] = trim(strip_tags($data['short_description'] ?? ''));
        $data['description'] = trim($data['description'] ?? '');

        $title = $data['title'] ?: 'Tour Package';
        $desc = $data['short_description'] ?: strip_tags($data['description']);

        $data['seo_title'] = Str::limit($title, 65, '');
        $data['seo_description'] = Str::limit($desc, 160, '');
        $data['breadcrumb_title'] = Str::limit($title, 50, '');

        return $data;
    }
}
