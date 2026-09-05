<?php

namespace App\Http\Middleware;

use App\Support\LocaleNormalizer;
use Closure;
use Illuminate\Http\Request;

class LanguageMiddleware
{
    public function __construct(private readonly LocaleNormalizer $localeNormalizer) {}

    public function handle(Request $request, Closure $next)
    {
        // تحقق إذا كانت اللغة تأتي من الهيدر
        if ($request->hasHeader('Accept-Language')) {
            app()->setLocale($this->localeNormalizer->fromAcceptLanguage($request->header('Accept-Language')));
        } elseif (session()->has('locale')) {
            // أو من الجلسة
            app()->setLocale($this->localeNormalizer->normalize((string) session('locale')));
        }
        
        return $next($request);
    }
}
