<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait HasAiTranslation
{
    private $apiKey;
    private $model = 'deepseek-chat';
    private $baseUrl = 'https://api.deepseek.com/v1/chat/completions';
    private $maxRetries = 3;
    private $retryDelay = 3;
    /**
     * تحسين النص بالذكاء الاصطناعي.
     */
    public function aiEnhance(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'type' => 'required|string|in:title,description,meta_title,meta_description,meta_keywords',
            'action' => 'required|string|in:enhance,complete,seo',
            'tone' => 'nullable|string',
            'style' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        try {
            // تنظيف النص أولاً
            $text = $this->cleanUtf8($request->text);

            // كشف لغة النص
            $lang = $this->detectLang($text);

            // بناء prompt حسب نوع التحسين
            $prompt = $this->buildEnhancePrompt($text, $request->action, $request->type, $lang, $request->tone, $request->style);

            // استخدام نفس دالة الترجمة للتحسين (مع تعديل الـ prompt)
            $enhancedText = $this->translateWithAI($prompt, $lang === 'ar' ? 'ar' : 'en');

            // إزالة أي تعليمات من النتيجة
            $enhancedText = $this->cleanResponse($enhancedText);

            return response()->json([
                'success' => true,
                'enhanced_text' => $enhancedText
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تحسين النص: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحسين النص: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * بناء prompt للتحسين
     */
    private function buildEnhancePrompt($text, $action, $type, $lang, $tone, $style)
    {
        $toneMap = [
            'neutral' => 'محايد',
            'friendly' => 'ودود',
            'professional' => 'مهني',
            'enthusiastic' => 'متحمس',
            'persuasive' => 'مقنع'
        ];

        $arabicTone = $toneMap[$tone] ?? 'محايد';

        $prompt = "قم بتحسين النص التالي لقسم في متجر إلكتروني:\n\n";
        $prompt .= "النص: \"{$text}\"\n\n";
        $prompt .= "نوع النص: " . ($type === 'title' ? 'عنوان' : ($type === 'description' ? 'وصف' : 'Meta SEO')) . "\n";
        $prompt .= "الإجراء: " . ($action === 'enhance' ? 'تحسين' : ($action === 'complete' ? 'إكمال' : 'تحسين SEO')) . "\n";
        $prompt .= "النبرة: {$arabicTone}\n";
        $prompt .= "الأسلوب: " . ($style === 'formal' ? 'رسمي' : ($style === 'simplified' ? 'مبسط' : ($style === 'seo' ? 'محسن SEO' : 'إبداعي'))) . "\n\n";

        if ($action === 'complete') {
            $prompt .= "قم بإكمال النص ليكون أكثر شمولاً وجاذبية مع الحفاظ على المعنى الأصلي.\n";
        } elseif ($action === 'seo') {
            if ($type === 'meta_title') {
                $prompt .= "قم بتحسين العنوان لمحركات البحث (50-60 حرف) مع إضافة كلمات مفتاحية مناسبة.\n";
            } elseif ($type === 'meta_description') {
                $prompt .= "قم بتحسين الوصف لمحركات البحث (150-160 حرف) ليكون جذاباً ومحسناً SEO.\n";
            }
        } else {
            $prompt .= "قم بتحسين النص ليكون أكثر جاذبية واحترافية مع الحفاظ على المعنى.\n";
        }

        $prompt .= "\nأعد فقط النص المحسن بدون أي تعليمات إضافية.";

        return $prompt;
    }
    /**
     * إنشاء SEO بالذكاء الاصطناعي.
     */
    public function aiGenerateSeo(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'tone' => 'nullable|string',
            'style' => 'nullable|string'
        ]);

        try {
            $name = $this->cleanUtf8($request->name);
            $description = $request->description ? $this->cleanUtf8($request->description) : null;

            $lang = $this->detectLang($name);

            $prompt = "قم بإنشاء محتوى SEO محسن لقسم في متجر إلكتروني:\n\n";
            $prompt .= "اسم القسم: \"{$name}\"\n";
            if ($description) {
                $prompt .= "الوصف الحالي: \"{$description}\"\n\n";
            }
            $prompt .= "المطلوب:\n";
            $prompt .= "1. Meta Title (عنوان محسن لمحركات البحث - 50-60 حرف)\n";
            $prompt .= "2. Meta Description (وصف محسن لمحركات البحث - 150-160 حرف)\n";
            $prompt .= "3. Meta Keywords (كلمات مفتاحية مفصولة بفواصل)\n\n";
            $prompt .= "أعد النتيجة بالصيغة التالية:\n";
            $prompt .= "META_TITLE: [العنوان]\n";
            $prompt .= "META_DESCRIPTION: [الوصف]\n";
            $prompt .= "META_KEYWORDS: [الكلمات المفتاحية]";

            $response = $this->translateWithAI($prompt, $lang === 'ar' ? 'ar' : 'en');

            // استخراج البيانات من الرد
            $metaTitle = '';
            $metaDescription = '';
            $metaKeywords = '';

            if (preg_match('/META_TITLE:\s*(.+?)(?=\nMETA_DESCRIPTION|$)/s', $response, $matches)) {
                $metaTitle = trim($matches[1]);
            }
            if (preg_match('/META_DESCRIPTION:\s*(.+?)(?=\nMETA_KEYWORDS|$)/s', $response, $matches)) {
                $metaDescription = trim($matches[1]);
            }
            if (preg_match('/META_KEYWORDS:\s*(.+?)$/s', $response, $matches)) {
                $metaKeywords = trim($matches[1]);
            }

            // إذا فشل الاستخراج، استخدم الرد كامل كوصف
            if (empty($metaTitle) && empty($metaDescription)) {
                $metaTitle = $name . ' - أفضل الأسعار والجودة';
                $metaDescription = Str::limit($response, 150);
                $metaKeywords = str_replace(' ', ', ', $name);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'meta_keywords' => $metaKeywords
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء SEO: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء SEO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحسين كامل للقسم.
     */
    public function aiEnhanceFull(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'tone' => 'nullable|string',
            'style' => 'nullable|string'
        ]);

        try {
            $name = $this->cleanUtf8($request->name);
            $description = $request->description ? $this->cleanUtf8($request->description) : null;

            $lang = $this->detectLang($name);

            $prompt = "قم بتحسين وتطوير البيانات التالية لقسم في متجر إلكتروني:\n\n";
            $prompt .= "الاسم الحالي: \"{$name}\"\n";
            if ($description) {
                $prompt .= "الوصف الحالي: \"{$description}\"\n\n";
            }
            $prompt .= "المطلوب إنشاء:\n";
            $prompt .= "1. اسم محسن للقسم (جذاب ومعبر)\n";
            $prompt .= "2. وصف محسن للقسم (جذاب واحترافي)\n";
            $prompt .= "3. Slug مناسب للرابط\n";
            $prompt .= "4. Meta Title محسن SEO\n";
            $prompt .= "5. Meta Description محسن SEO\n";
            $prompt .= "6. Meta Keywords مناسبة\n\n";
            $prompt .= "أعد النتيجة بالصيغة التالية:\n";
            $prompt .= "NAME: [الاسم المحسن]\n";
            $prompt .= "DESCRIPTION: [الوصف المحسن]\n";
            $prompt .= "SLUG: [الرابط]\n";
            $prompt .= "META_TITLE: [عنوان SEO]\n";
            $prompt .= "META_DESCRIPTION: [وصف SEO]\n";
            $prompt .= "META_KEYWORDS: [الكلمات المفتاحية]";

            $response = $this->translateWithAI($prompt, $lang === 'ar' ? 'ar' : 'en');

            // استخراج البيانات من الرد
            $data = [
                'name' => $name,
                'description' => $description,
                'slug' => Str::slug($name),
                'meta_title' => $name . ' - أفضل العروض',
                'meta_description' => $description ?? 'اكتشف ' . $name . ' في متجرنا',
                'meta_keywords' => str_replace(' ', ', ', $name)
            ];

            if (preg_match('/NAME:\s*(.+?)(?=\nDESCRIPTION|$)/s', $response, $matches)) {
                $data['name'] = trim($matches[1]);
            }
            if (preg_match('/DESCRIPTION:\s*(.+?)(?=\nSLUG|$)/s', $response, $matches)) {
                $data['description'] = trim($matches[1]);
            }
            if (preg_match('/SLUG:\s*(.+?)(?=\nMETA_TITLE|$)/s', $response, $matches)) {
                $data['slug'] = Str::slug(trim($matches[1]));
            }
            if (preg_match('/META_TITLE:\s*(.+?)(?=\nMETA_DESCRIPTION|$)/s', $response, $matches)) {
                $data['meta_title'] = trim($matches[1]);
            }
            if (preg_match('/META_DESCRIPTION:\s*(.+?)(?=\nMETA_KEYWORDS|$)/s', $response, $matches)) {
                $data['meta_description'] = trim($matches[1]);
            }
            if (preg_match('/META_KEYWORDS:\s*(.+?)$/s', $response, $matches)) {
                $data['meta_keywords'] = trim($matches[1]);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تحسين القسم: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحسين القسم: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * تنظيف الرد من أي تعليمات
     */
    private function cleanResponse($text)
    {
        // إزالة علامات التنصيص إذا وجدت
        $text = trim($text, '"\'');

        // إزالة كلمات مثل "الترجمة:" أو "النص المحسن:"
        $text = preg_replace('/^(الترجمة|النص المحسن|النتيجة|Response|Translation|Result):\s*/i', '', $text);

        return $text;
    }
    /**
     * تحديد لغة النص تلقائياً
     */
    // private function detectLang(string $text): string
    // {
    //     // تنظيف النص أولاً
    //     $text = $this->cleanUtf8($text);

    //     // إذا النص فارغ
    //     if (empty(trim($text))) {
    //         return 'ar';
    //     }

    //     // كشف اللغة العربية
    //     if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text)) {
    //         return 'ar';
    //     }

    //     // كشف الإنجليزية
    //     if (preg_match('/[A-Za-z]/', $text)) {
    //         return 'en';
    //     }

    //     // الافتراضي عربي
    //     return 'ar';
    // }

    /**
     * توليد اللغة الثانية باستخدام DeepSeek
     */
    private function translateWithAI(string $text, string $targetLang): string
    {
        // تنظيف النص أولاً
        $text = $this->cleanUtf8($text);

        if (empty(trim($text))) {
            return $targetLang === 'ar' ? 'منتج مميز' : 'Featured Product';
        }

        // Cache الترجمة لتجنب التكرار
        $cacheKey = 'ai_translate_' . md5($text . $targetLang);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($text, $targetLang) {
            $this->apiKey = config('services.deepseek.api_key');

            if (!$this->apiKey) {
                return $targetLang === 'ar' ? 'منتج مميز' : 'Featured Product';
            }

            $sourceLang = $targetLang === 'ar' ? 'الإنجليزية' : 'العربية';
            $targetLangName = $targetLang === 'ar' ? 'العربية' : 'الإنجليزية';

            $prompt = "Translate the following commercial product text from {$sourceLang} to {$targetLangName} with professional tone suitable for e-commerce.\n\n";
            $prompt .= "Text: \"{$text}\"\n\n";
            $prompt .= "Important:\n";
            $prompt .= "- Keep the same meaning\n";
            $prompt .= "- Use commercial/SEO friendly language\n";
            $prompt .= "- Maintain proper capitalization\n";
            $prompt .= "- Do not add explanations\n";
            $prompt .= "- Just return the translation\n";

            for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
                try {
                    $response = Http::timeout(30)->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ])->post($this->baseUrl, [
                        'model' => $this->model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.3,
                        'max_tokens' => 1000,
                    ]);

                    if ($response->successful()) {
                        $translated = trim($response->json('choices.0.message.content'));
                        return $this->cleanUtf8($translated);
                    }

                    if ($attempt < $this->maxRetries) {
                        sleep($this->retryDelay);
                    }
                } catch (\Exception $e) {
                    if ($attempt === $this->maxRetries) {
                        return $targetLang === 'ar' ? 'منتج مميز' : 'Featured Product';
                    }
                    sleep($this->retryDelay);
                }
            }

            return $targetLang === 'ar' ? 'منتج مميز' : 'Featured Product';
        });
    }

    /**
     * إنشاء نص ثنائي اللغة {ar, en}
     */
    // protected function makeBiLang(string $text): array
    // {
    //     $text = $this->cleanUtf8($text);

    //     if (empty(trim($text))) {
    //         return [
    //             'ar' => 'منتج مميز',
    //             'en' => 'Featured Product'
    //         ];
    //     }

    //     $lang = $this->detectLang($text);

    //     if ($lang === 'ar') {
    //         return [
    //             'ar' => $text,
    //             'en' => $this->translateWithAI($text, 'en'),
    //         ];
    //     } else {
    //         return [
    //             'en' => $text,
    //             'ar' => $this->translateWithAI($text, 'ar'),
    //         ];
    //     }
    // }

    /**
     * تنظيف UTF-8
     */
    private function cleanUtf8(string $text): string
    {
        // التحويل إلى UTF-8 إذا لم يكن
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // إزالة الأحرف غير الصالحة
        $text = preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $text);

        // إزالة أحرف التحكم
        $text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // إزالة BOM
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        // تقليم المسافات
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * تنظيف HTML
     */
    protected function cleanHtml(string $html): string
    {
        $html = $this->cleanUtf8($html);

        $allowedTags = '<h1><h2><h3><h4><p><br><strong><b><em><i><u><ul><ol><li><span><div>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s+/', ' ', $html);

        return trim($html);
    }
    /**
     * إنشاء نص ثنائي اللغة {ar, en}
     */
    protected function makeBiLang(string $text): array
    {
        $text = $this->cleanUtf8($text);

        if (empty(trim($text))) {
            return [
                'ar' => 'منتج مميز',
                'en' => 'Featured Product'
            ];
        }

        $lang = $this->detectLang($text);

        if ($lang === 'ar') {
            return [
                'ar' => $text,
                'en' => $this->translateWithAI($text, 'en'),
            ];
        } else {
            return [
                'en' => $text,
                'ar' => $this->translateWithAI($text, 'ar'),
            ];
        }
    }

    /**
     * تحديد لغة النص تلقائياً
     */
    private function detectLang(string $text): string
    {
        // تنظيف النص أولاً
        $text = $this->cleanUtf8($text);

        if (empty(trim($text))) {
            return 'ar';
        }

        // كشف اللغة العربية
        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text)) {
            return 'ar';
        }

        // كشف الإنجليزية
        if (preg_match('/[A-Za-z]/', $text)) {
            return 'en';
        }

        // الافتراضي عربي
        return 'ar';
    }
}
