<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PageAiService
{
    public function __construct(
        protected DeepSeekService $deepSeekService,
        protected TranslationService $translationService
    ) {}

    public function generatePage(array $input): ?array
    {
        $prompt = trim((string) ($input['prompt'] ?? ''));
        $template = trim((string) ($input['template'] ?? 'default'));

        if ($prompt === '') {
            return null;
        }

        $fullPrompt = <<<PROMPT
أنت كاتب محتوى محترف ومتخصص في إنشاء صفحات المواقع.

أنشئ محتوى صفحة كاملة بناءً على الوصف التالي:
{$prompt}

القالب المطلوب:
{$template}

أعد النتيجة فقط بصيغة JSON صحيحة بهذا الشكل:
{
  "title": "",
  "body": "",
  "seo_title": "",
  "seo_description": ""
}

القواعد:
- title واضح وجذاب
- body منظم واحترافي ومناسب للنشر
- seo_title مختصر وقوي
- seo_description مناسب لمحركات البحث
- لا تضف أي شرح خارج JSON
PROMPT;

        $result = $this->deepSeekService->askJson(
            prompt: $fullPrompt,
            systemPrompt: 'You are a professional website page generator. Return ONLY valid JSON.',
            temperature: 0.5,
            maxTokens: 2500
        );

        if (!is_array($result)) {
            return null;
        }

        return $this->normalizePageData($result);
    }

    public function generateTitle(string $topic): ?string
    {
        $prompt = <<<PROMPT
اكتب عنوانًا احترافيًا وجذابًا لصفحة موقع عن:
{$topic}

أعد العنوان فقط بدون شرح.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional Arabic website copywriter.',
            temperature: 0.5,
            maxTokens: 150
        );
    }

    public function generateBody(string $title, ?string $template = null): ?string
    {
        $template = $template ?: 'default';

        $prompt = <<<PROMPT
اكتب محتوى صفحة موقع احترافي بعنوان:
{$title}

القالب:
{$template}

أعد المحتوى فقط بدون شرح.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional website content writer.',
            temperature: 0.6,
            maxTokens: 2500
        );
    }

    public function enhanceText(string $content, ?string $instruction = null): ?string
    {
        $instruction = $instruction ?: 'حسن الأسلوب والوضوح والتنظيم مع الحفاظ على المعنى';

        $prompt = <<<PROMPT
حسن النص التالي بناءً على التعليمات:

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
            maxTokens: 2500
        );
    }

    public function expandContent(string $content): ?string
    {
        return $this->enhanceText($content, 'وسّع المحتوى وأضف تفاصيل مفيدة مع الحفاظ على الأسلوب الاحترافي');
    }

    public function simplifyContent(string $content): ?string
    {
        return $this->enhanceText($content, 'بسّط المحتوى واجعله أوضح وأسهل للقراءة');
    }

    public function formatContent(string $content): ?string
    {
        return $this->enhanceText($content, 'نسّق المحتوى بعناوين فرعية وفقرات واضحة وقابلة للنشر');
    }

    public function checkGrammar(string $content): ?string
    {
        return $this->enhanceText($content, 'صحح الأخطاء الإملائية والنحوية فقط بدون تغيير المعنى');
    }

    public function addSection(string $content, string $section): ?string
    {
        $prompt = <<<PROMPT
أضف قسمًا جديدًا إلى المحتوى التالي.

اسم القسم الجديد:
{$section}

المحتوى الحالي:
{$content}

أعد المحتوى كاملًا بعد إضافة القسم فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional content editor.',
            temperature: 0.5,
            maxTokens: 2500
        );
    }

    public function generateMetaTitle(string $title, ?string $body = null): ?string
    {
        $prompt = <<<PROMPT
اكتب عنوان SEO احترافي لصفحة بعنوان:
{$title}

المحتوى المرجعي:
{$body}

أعد عنوان SEO فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are an SEO expert.',
            temperature: 0.4,
            maxTokens: 150
        );
    }

    public function generateMetaDescription(string $title, ?string $body = null): ?string
    {
        $prompt = <<<PROMPT
اكتب وصف SEO احترافي ومختصر لصفحة بعنوان:
{$title}

المحتوى المرجعي:
{$body}

أعد الوصف فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are an SEO expert.',
            temperature: 0.4,
            maxTokens: 220
        );
    }

    public function generateKeywords(string $title, ?string $body = null): array
    {
        $prompt = <<<PROMPT
استخرج الكلمات المفتاحية SEO لصفحة بعنوان:
{$title}

المحتوى المرجعي:
{$body}

أعد النتيجة فقط بصيغة JSON:
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

    public function loadTemplate(string $template): ?string
    {
        $prompt = <<<PROMPT
اكتب محتوى أولي احترافي لصفحة باستخدام القالب التالي:
{$template}

أعد المحتوى فقط.
PROMPT;

        return $this->deepSeekService->ask(
            prompt: $prompt,
            systemPrompt: 'You are a professional website content writer.',
            temperature: 0.5,
            maxTokens: 1800
        );
    }

    public function makeSlug(array|string $title): string
    {
        $text = is_array($title)
            ? ($title['en'] ?? $title['ar'] ?? reset($title) ?? '')
            : $title;

        return Str::slug((string) $text);
    }

    protected function normalizePageData(array $data): array
    {
        return [
            'title' => $this->stringValue($data, 'title'),
            'body' => $this->stringValue($data, 'body'),
            'seo_title' => $this->stringValue($data, 'seo_title'),
            'seo_description' => $this->stringValue($data, 'seo_description'),
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
}
