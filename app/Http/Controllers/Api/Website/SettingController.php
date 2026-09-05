<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\ImportantModel;
use App\Models\Language;
use App\Models\Setting;
use App\Services\IpInfoService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponseTrait;

public function index(Request $request, IpInfoService $ipInfoService)
{
    $ip = $request->ip();
    $ipInfo = $ipInfoService->getCountryAndCurrency($ip);
    $country = $ipInfo['country'] ?? null;

    $countryLocales = [
        // Arabic (RTL)
        'Egypt' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Saudi Arabia' => ['lang' => 'ar', 'dir' => 'rtl'],
        'United Arab Emirates' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Kuwait' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Qatar' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Bahrain' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Oman' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Jordan' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Palestine' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Iraq' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Syria' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Lebanon' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Yemen' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Sudan' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Libya' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Morocco' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Algeria' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Tunisia' => ['lang' => 'ar', 'dir' => 'rtl'],
        'Mauritania' => ['lang' => 'ar', 'dir' => 'rtl'],

        // English (LTR)
        'United States' => ['lang' => 'en', 'dir' => 'ltr'],
        'United Kingdom' => ['lang' => 'en', 'dir' => 'ltr'],
        'Canada' => ['lang' => 'en', 'dir' => 'ltr'],
        'Australia' => ['lang' => 'en', 'dir' => 'ltr'],

        // French
        'France' => ['lang' => 'fr', 'dir' => 'ltr'],
        'Belgium' => ['lang' => 'fr', 'dir' => 'ltr'],
        'Switzerland' => ['lang' => 'fr', 'dir' => 'ltr'],

        // German
        'Germany' => ['lang' => 'de', 'dir' => 'ltr'],

        // Spanish
        'Spain' => ['lang' => 'es', 'dir' => 'ltr'],
        'Mexico' => ['lang' => 'es', 'dir' => 'ltr'],

        // Turkish
        'Turkey' => ['lang' => 'tr', 'dir' => 'ltr'],
    ];

    // ✅ اللغات المدعومة عندك من DB
    $supported = Language::pluck('code')->map(fn($c) => strtolower($c))->toArray();

    // 1) lang param (query/body/header custom)
    $requestedLang = strtolower(
        $request->input('lang')
        ?? $request->query('lang')
        ?? $request->header('lang')
        ?? ''
    );

    // 2) Accept-Language (أول لغة مدعومة)
    $acceptLang = strtolower($request->header('Accept-Language', ''));
    $preferredFromHeader = null;

    if ($acceptLang) {
        // مثال: ar,en;q=0.9,fr;q=0.8
        $parts = explode(',', $acceptLang);
        foreach ($parts as $p) {
            $code = trim(explode(';', $p)[0]); // ar / en-US
            $primary = strtolower(explode('-', $code)[0]); // ar
            if (in_array($primary, $supported, true)) {
                $preferredFromHeader = $primary;
                break;
            }
        }
    }

    // 3) IP fallback
    $ipLocale = $countryLocales[$country]['lang'] ?? null;

    // ✅ اختيار اللغة النهائية
    $finalLang =
        (in_array($requestedLang, $supported, true) ? $requestedLang : null)
        ?? $preferredFromHeader
        ?? (in_array(strtolower((string)$ipLocale), $supported, true) ? strtolower((string)$ipLocale) : null)
        ?? setting('site_language', 'en');

    // ✅ الاتجاه (rtl/ltr)
    $rtlLangs = ['ar', 'fa', 'ur', 'he'];
    $finalDir = in_array($finalLang, $rtlLangs, true) ? 'rtl' : 'ltr';

    // ✅ settings العامة
    $setting = Setting::all();
    $allSettings = $setting->pluck('value', 'key')->toArray();

    // ✅ النصوص المترجمة من important_models حسب اللغة المختارة
    // هنجيب مفاتيح الموقع اللي انت عاملها
    $keys = [
        "site_name_{$finalLang}",
        "site_title_{$finalLang}",
        "site_description_{$finalLang}",
        "site_keywords_{$finalLang}",
    ];

    $translated = ImportantModel::whereIn('model', $keys)
        ->pluck('important_text', 'model')
        ->toArray();

    // نرجّعها بشكل مرتب (بدون suffix لو تحب)
    $translatedSettings = [
        'site_name' => $translated["site_name_{$finalLang}"] ?? '',
        'site_title' => $translated["site_title_{$finalLang}"] ?? '',
        'site_description' => $translated["site_description_{$finalLang}"] ?? '',
        'site_keywords' => $translated["site_keywords_{$finalLang}"] ?? '',
    ];

    return $this->success([
        'settings' => [
            'language'  => $finalLang,
            'direction' => $finalDir,

            // settings العامة القديمة
            'all_settings' => $allSettings,

            // ✅ النصوص المترجمة للغة الحالية
            'translated_settings' => $translatedSettings,
        ],
        'location' => $ipInfo,
        'detected' => [
            'requested_lang' => $requestedLang ?: null,
            'accept_language' => $acceptLang ?: null,
            'country' => $country,
        ],
    ]);
}
}
