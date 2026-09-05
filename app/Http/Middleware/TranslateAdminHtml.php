<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TranslateAdminHtml
{
    protected static array $translationCache = [];

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if (!$this->shouldTranslate($request, $response)) {
            return $response;
        }

        $translations = $this->translationsFor(app()->getLocale());

        if (empty($translations)) {
            return $response;
        }

        $content = $response->getContent();

        if (!is_string($content) || trim($content) === '') {
            return $response;
        }

        $translated = $this->translateHtml($content, $translations);

        if ($translated !== '') {
            $response->setContent($translated);
        }

        return $response;
    }

    protected function shouldTranslate(Request $request, SymfonyResponse $response): bool
    {
        if (!(bool) config('translation.legacy_admin_html_enabled', true)
            || app()->getLocale() === config('translation.source_locale', 'en')) {
            return false;
        }

        if (!str_starts_with($request->path(), 'admin')) {
            return false;
        }

        if (!$response instanceof Response) {
            return false;
        }

        if ($response->isRedirection() || $response->isEmpty()) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html');
    }

    protected function translationsFor(string $locale): array
    {
        if (isset(static::$translationCache[$locale])) {
            return static::$translationCache[$locale];
        }

        $path = lang_path($locale . '.json');

        if (!is_file($path)) {
            return static::$translationCache[$locale] = [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (!is_array($decoded)) {
            return static::$translationCache[$locale] = [];
        }

        $translations = [];

        foreach ($decoded as $source => $target) {
            if (!is_string($source) || !is_string($target)) {
                continue;
            }

            $source = trim($source);
            $target = trim($target);

            if ($source === '' || $target === '' || $source === $target) {
                continue;
            }

            $translations[$source] = $target;
        }

        uksort($translations, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));

        return static::$translationCache[$locale] = $translations;
    }

    protected function translateHtml(string $html, array $translations): string
    {
        [$protectedHtml, $rawTextBlocks] = $this->protectRawTextElements($html);

        libxml_use_internal_errors(true);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $encodedHtml = mb_encode_numericentity(
            $protectedHtml,
            [0x80, 0x10FFFF, 0, ~0],
            'UTF-8'
        );
        $loaded = $document->loadHTML(
            $encodedHtml,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        if (!$loaded) {
            libxml_clear_errors();
            return $html;
        }

        $xpath = new \DOMXPath($document);

        foreach ($xpath->query('//text()[normalize-space() and not(ancestor::script) and not(ancestor::style) and not(ancestor::textarea)]') as $node) {
            $original = $node->nodeValue;
            $trimmed = trim($original);

            if ($trimmed === '') {
                continue;
            }

            $translated = $this->translatedValue($trimmed, $translations);

            if ($translated === $trimmed) {
                continue;
            }

            $node->nodeValue = preg_replace(
                '/^(\s*).*?(\s*)$/us',
                '$1' . $translated . '$2',
                $original
            ) ?? $translated;
        }

        $attributes = ['placeholder', 'title', 'aria-label', 'alt', 'data-bs-original-title'];

        foreach ($xpath->query('//*[@placeholder or @title or @aria-label or @alt or @data-bs-original-title]') as $element) {
            foreach ($attributes as $attribute) {
                if (!$element->hasAttribute($attribute)) {
                    continue;
                }

                $original = trim((string) $element->getAttribute($attribute));

                if ($original === '') {
                    continue;
                }

                $translated = $this->translatedValue($original, $translations);

                if ($translated !== $original) {
                    $element->setAttribute($attribute, $translated);
                }
            }
        }

        libxml_clear_errors();

        $renderedHtml = (string) $document->saveHTML();
        $decodedHtml = html_entity_decode($renderedHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return strtr($decodedHtml, $rawTextBlocks);
    }

    /**
     * DOMDocument rewrites closing tags found inside JavaScript template literals.
     * Keep raw-text element contents byte-for-byte intact while translating the page.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    protected function protectRawTextElements(string $html): array
    {
        $rawTextBlocks = [];
        $index = 0;

        $protectedHtml = preg_replace_callback(
            '~(<(script|style|textarea)\b[^>]*>)(.*?)(</\2\s*>)~is',
            function (array $matches) use (&$rawTextBlocks, &$index): string {
                $token = '__ADMIN_RAW_TEXT_' . hash('sha256', $index . ':' . $matches[3]) . '__';
                $rawTextBlocks[$token] = $matches[3];
                $index++;

                return $matches[1] . $token . $matches[4];
            },
            $html
        );

        return [$protectedHtml ?? $html, $rawTextBlocks];
    }

    protected function translatedValue(string $value, array $translations): string
    {
        return $translations[$value] ?? $value;
    }
}
