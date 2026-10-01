<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml
{
    /**
     * Handle an incoming request and minify HTML response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldMinify($request, $response)) {
            $content = $response->getContent();
            if ($content !== false && is_string($content)) {
                $response->setContent($this->minify($content));
            }
        }

        return $response;
    }

    protected function shouldMinify(Request $request, Response $response): bool
    {
        if ($request->is('admin*') || $request->ajax()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        return $response->isSuccessful()
            && is_string($contentType)
            && str_contains(strtolower($contentType), 'text/html');
    }

    protected function minify(string $html): string
    {
        // Preserve pre, textarea, script and style tags
        $placeholders = [];
        $html = preg_replace_callback(
            '#<(pre|textarea|script)(?:[^>]*?)>.*?</\1>#is',
            function ($matches) use (&$placeholders) {
                $key = '___MINIFY_PLACEHOLDER_' . count($placeholders) . '___';
                $placeholders[$key] = $matches[0];
                return $key;
            },
            $html
        );

        // Remove HTML comments (except IE conditional comments)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Collapse multiple whitespace sequences between tags
        $html = preg_replace('/>\s+</', '> <', $html);
        $html = preg_replace('/\s{2,}/', ' ', $html);

        // Restore placeholders
        if (! empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        return trim($html);
    }
}
