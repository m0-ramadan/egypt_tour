<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ArticleAiService
{
    public function __construct(
        protected DeepSeekService $deepSeekService,
        protected TranslationService $translationService
    ) {}

    public function generateArticle(array $input): ?array
    {
        $topic = trim((string) ($input['topic'] ?? $input['prompt'] ?? ''));
        $category = trim((string) ($input['category'] ?? ''));
        $tone = trim((string) ($input['tone'] ?? 'professional'));
        $languageHint = trim((string) ($input['language_hint'] ?? 'Arabic and English'));

        if ($topic === '') {
            return null;
        }

        $prompt = <<<PROMPT
أنت كاتب محتوى محترف ومتخصص في كتابة المقالات.

المطلوب:
أنشئ مقالًا كاملًا بناءً على البيانات التالية:

الموضوع: {$topic}
التصنيف: {$category}
النبرة: {$tone}
اللغات المطلوبة: {$languageHint}

أعد النتيجة فقط بصيغة JSON صحيحة بهذا الشكل:
{
  "title": "",
  "excerpt": "",
  "content": "",
  "seo_title": "",
  "seo_description": "",
  "keywords": []
}

قواعد:
- title جذاب وواضح
- excerpt مختصر وقوي
- content منظم ومهني
- seo_title مناسب لمحركات البحث
- seo_description مختصر وجذاب
- keywords تكون array من الكلمات المفتاحية
- لا تضف أي شرح خارج JSON
PROMPT;

        $result = $this->deepSeekService->askJson(
            prompt: $prompt,
            systemPrompt: 'You are an expert article writer. Return ONLY valid JSON.',
            temperature: 0.5,
            maxTokens: 2500
        );

        if (!is_array($result)) {
            return null;
        }

        return $this->normalizeGeneratedArticle($result);
    }

    public function generateTitle(string $topic, ?string $tone = null): ?string
    {
        $tone = $tone ?: 'professional';

        $prompt = <<<PROMPT
اكتب عنوانًا احترافيًا وجذابًا لمقال عن:
{$topic}

النبرة المطلوبة:
{$tone}

أعد العنوان فقط كسطر نصي واحد بدون شرح.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional copywriter.',
            temperature: 0.6,
            maxTokens: 200
        );
    }

    public function generateExcerpt(string $title, ?string $content = null): ?string
    {
        $prompt = <<<PROMPT
اكتب ملخصًا قصيرًا وجذابًا لمقال بعنوان:
{$title}

المحتوى المرجعي:
{$content}

أعد الملخص فقط بدون شرح.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional editor.',
            temperature: 0.5,
            maxTokens: 250
        );
    }

    public function generateContent(string $title, ?string $excerpt = null, ?string $tone = null): ?string
    {
        $tone = $tone ?: 'professional';

        $prompt = <<<PROMPT
اكتب مقالًا احترافيًا كاملًا بعنوان:
{$title}

الملخص:
{$excerpt}

النبرة:
{$tone}

أعد المحتوى فقط بدون أي مقدمات خارجية.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional article writer.',
            temperature: 0.6,
            maxTokens: 2200
        );
    }

    public function enhanceContent(string $content, ?string $instruction = null): ?string
    {
        $instruction = $instruction ?: 'حسن الأسلوب والوضوح والتنظيم بدون تغيير المعنى الأساسي';

        $prompt = <<<PROMPT
قم بتحسين النص التالي بناءً على التعليمات:

التعليمات:
{$instruction}

النص:
{$content}

أعد النص المحسن فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional Arabic content editor.',
            temperature: 0.4,
            maxTokens: 2200
        );
    }

    public function generateMetaTitle(string $title, ?string $content = null): ?string
    {
        $prompt = <<<PROMPT
اكتب عنوان SEO احترافي ومختصر لمقال بعنوان:
{$title}

المحتوى المرجعي:
{$content}

أعد عنوان SEO فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are an SEO expert.',
            temperature: 0.4,
            maxTokens: 150
        );
    }

    public function generateMetaDescription(string $title, ?string $content = null): ?string
    {
        $prompt = <<<PROMPT
اكتب وصف SEO احترافي ومختصر لمقال بعنوان:
{$title}

المحتوى المرجعي:
{$content}

أعد الوصف فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are an SEO expert.',
            temperature: 0.4,
            maxTokens: 220
        );
    }

    public function generateKeywords(string $title, ?string $content = null): array
    {
        $prompt = <<<PROMPT
استخرج الكلمات المفتاحية SEO لمقال بعنوان:
{$title}

المحتوى المرجعي:
{$content}

أعد النتيجة فقط بصيغة JSON بهذا الشكل:
{
  "keywords": ["keyword 1", "keyword 2", "keyword 3"]
}
PROMPT;

        $result = $this->deepSeekService->askJson(
            prompt: $prompt,
            systemPrompt: 'You are an SEO keyword generator. Return ONLY valid JSON.',
            temperature: 0.3,
            maxTokens: 300
        );

        if (!is_array($result) || !isset($result['keywords']) || !is_array($result['keywords'])) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn($item) => is_string($item) ? trim($item) : '',
            $result['keywords']
        )));
    }

    public function translateFields(array $data, array $fields): array
    {
        return $this->translationService->translateFields($data, $fields);
    }

    public function improveAll(array $data): array
    {
        $improved = $data;

        if (!empty($data['title'])) {
            $improved['title'] = $this->generateTitle($this->stringValue($data, 'title')) ?: $this->stringValue($data, 'title');
        }

        if (!empty($data['content'])) {
            $improved['content'] = $this->enhanceContent($this->stringValue($data, 'content')) ?: $this->stringValue($data, 'content');
        }

        if (!empty($improved['title']) || !empty($improved['content'])) {
            $improved['excerpt'] = $this->generateExcerpt(
                $this->stringValue($improved, 'title'),
                $this->stringValue($improved, 'content')
            ) ?: ($data['excerpt'] ?? '');
        }

        $improved['seo_title'] = $this->generateMetaTitle(
            $this->stringValue($improved, 'title'),
            $this->stringValue($improved, 'content')
        ) ?: ($data['seo_title'] ?? '');

        $improved['seo_description'] = $this->generateMetaDescription(
            $this->stringValue($improved, 'title'),
            $this->stringValue($improved, 'content')
        ) ?: ($data['seo_description'] ?? '');

        $improved['keywords'] = $this->generateKeywords(
            $this->stringValue($improved, 'title'),
            $this->stringValue($improved, 'content')
        );

        return $improved;
    }

    protected function normalizeGeneratedArticle(array $data): array
    {
        return [
            'title' => $this->stringValue($data, 'title'),
            'excerpt' => $this->stringValue($data, 'excerpt'),
            'content' => $this->stringValue($data, 'content'),
            'seo_title' => $this->stringValue($data, 'seo_title'),
            'seo_description' => $this->stringValue($data, 'seo_description'),
            'keywords' => Arr::get($data, 'keywords', []),
        ];
    }

    protected function stringValue(array $data, string $key): string
    {
        $value = Arr::get($data, $key, '');

        if (is_array($value)) {
            return (string) ($value['ar'] ?? $value['en'] ?? reset($value) ?? '');
        }

        return trim((string) $value);
    }

    public function makeSlugFromTitle(array|string $title): string
    {
        $text = is_array($title)
            ? ($title['en'] ?? $title['ar'] ?? reset($title) ?? '')
            : $title;

        return Str::slug((string) $text);
    }
}
