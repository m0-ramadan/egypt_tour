<?php

return [
    'enabled' => (bool) env('TRANSLATION_ENABLED', true),
    'ai_enabled' => (bool) env('TRANSLATION_AI_ENABLED', true),
    'runtime_auto_translate' => (bool) env('TRANSLATION_RUNTIME_AUTO_TRANSLATE', false),
    'provider' => env('TRANSLATION_AI_PROVIDER', env('TRANSLATION_PRIMARY_PROVIDER', 'gemini')),
    'fallback_provider' => env('TRANSLATION_AI_FALLBACK_PROVIDER', env('TRANSLATION_FALLBACK_PROVIDER', 'deepseek')),
    'batch_size' => max(1, (int) env('TRANSLATION_BATCH_SIZE', 30)),
    'max_chars_per_request' => max(500, (int) env('TRANSLATION_MAX_CHARS_PER_REQUEST', 8000)),
    'cache_ttl' => max(60, (int) env('TRANSLATION_CACHE_TTL', 2592000)),
    'failure_cooldown' => max(60, (int) env('TRANSLATION_FAILURE_COOLDOWN', env('DEEPSEEK_FAILURE_COOLDOWN', 3600))),
    'log_cooldown' => max(60, (int) env('TRANSLATION_LOG_COOLDOWN', 3600)),
    'source_locale' => env('TRANSLATION_SOURCE_LOCALE', 'en'),
    'supported_locales' => array_values(array_filter(array_map(
        static fn (string $locale): string => strtolower(trim($locale)),
        explode(',', (string) env('TRANSLATION_SUPPORTED_LOCALES', 'en,ar'))
    ))),
    'lang_path' => env('TRANSLATION_LANG_PATH', 'lang'),
    'legacy_admin_html_enabled' => (bool) env('TRANSLATION_LEGACY_ADMIN_HTML_ENABLED', true),
    'max_tag_count' => max(1, (int) env('PACKAGE_TAG_MAX_COUNT', 30)),
    'max_tag_length' => max(1, (int) env('PACKAGE_TAG_MAX_LENGTH', 500)),
];
