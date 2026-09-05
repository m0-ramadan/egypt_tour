<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        // قراءة الهيدر Accept-Language
        $lang = $request->header('Accept-Language');

        // بسخّر أول حرفين فقط (en-US -> en)
        if ($lang) {
            $lang = substr($lang, 0, 2);
        }

        // اللغات المدعومة فقط
        $supportedLocales = ['ar', 'en'];

        if (!in_array($lang, $supportedLocales)) {
            $lang = config('app.locale'); // fallback
        }

        App::setLocale($lang);

        return $next($request);
    }
}
