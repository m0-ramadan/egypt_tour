<?php

namespace App\Http\Middleware;

use App\Support\LocaleNormalizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        protected LocaleNormalizer $localeNormalizer
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale', 'en');
        $isAdminRequest = $request->is('admin') || $request->is('admin/*');

        if ($isAdminRequest) {
            if ($request->hasSession()) {
                $request->session()->put('admin_locale', 'en');
            }
            app()->setLocale('en');

            return $next($request);
        }

        if ($request->hasSession() && $request->session()->has('locale')) {
            $locale = (string) $request->session()->get('locale');
        } elseif ($request->headers->has('Accept-Language')) {
            $locale = $this->localeNormalizer->fromAcceptLanguage(
                $request->header('Accept-Language'),
                $defaultLocale
            );
        } else {
            $locale = $defaultLocale;
        }

        $locale = $this->localeNormalizer->normalize($locale, $defaultLocale);

        try {
            $supportedLocales = \Illuminate\Support\Facades\Cache::remember('supported_locales', 3600, function () {
                return \App\Models\Language::where('is_active', true)->pluck('code')->toArray();
            });
        } catch (\Throwable) {
            $supportedLocales = (array) config('translation.supported_locales', ['en', 'ar']);
        }

        $supportedLocales = $this->localeNormalizer->normalizeList(array_merge(
            $supportedLocales,
            (array) config('translation.supported_locales', ['en', 'ar'])
        ));

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = $this->localeNormalizer->normalize((string) config('app.fallback_locale', 'en'));
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
