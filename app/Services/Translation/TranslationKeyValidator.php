<?php

namespace App\Services\Translation;

class TranslationKeyValidator
{
    private const BLOCKED_IDENTIFIERS = [
        'desktop',
        'mobile',
        'forcedelete',
        'uploads',
        'users',
    ];

    public function isValid(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $value = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($value === '' || mb_strlen($value) > 5000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value)) {
            return false;
        }

        $lower = mb_strtolower(trim($value, " \t\n\r\0\x0B/#"));
        if (in_array($lower, self::BLOCKED_IDENTIFIERS, true)) {
            return false;
        }

        if (preg_match('~^(?:https?://|//|mailto:|tel:|javascript:|[./\\\\]|[a-z]:[\\\\/])~i', $value)) {
            return false;
        }

        if (str_contains($value, '])')
            || str_contains($value, '=>')
            || str_contains($value, '<?')
            || str_contains($value, '?>')
            || str_contains($value, '{{')
            || str_contains($value, '}}')
            || preg_match('/\$[A-Za-z_]/', $value) === 1) {
            return false;
        }

        if (preg_match('/\.(?:php|blade\.php|js|css|json|svg|png|jpe?g|webp|pdf)$/i', $value)
            || preg_match('~^[\w.-]+(?:[/\\\\][\w.-]+)+[/\\\\]?$~u', $value)) {
            return false;
        }

        if (preg_match('/^[a-z]+(?:[A-Z][A-Za-z0-9]*)+$/', $value)
            || preg_match('/^[a-z][a-z0-9_\-.]*$/', $value)) {
            return false;
        }

        if (preg_match('/^[0-9\s\p{P}\p{S}]+$/u', $value)) {
            return false;
        }

        return preg_match('/[\p{L}]/u', $value) === 1;
    }
}
