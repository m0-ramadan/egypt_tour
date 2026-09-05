<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StaticPageCorrectorSeeder extends Seeder
{
    private $apiKey;
    private $model = 'deepseek-chat';
    private $baseUrl = 'https://api.deepseek.com/v1/chat/completions';

    /**
     * تشغيل سيدي (Seeder) قاعدة البيانات.
     */
    public function run()
    {
        $this->apiKey = env('DEEPSEEK_API_KEY');

        if (!$this->apiKey) {
            $this->command->error('❌ DEEPSEEK_API_KEY is missing in .env file');
            return;
        }

        $pages = StaticPage::all();

        $this->command->info("🎯 Starting enhancement for {$pages->count()} static pages...");
        
        $updatedCount = 0;
        $failedCount = 0;

        foreach ($pages as $index => $page) {
            $this->command->info("\n📄 [" . ($index + 1) . "/{$pages->count()}] Processing: {$page->title} (ID: {$page->id})");
            
            try {
                $pageData = $this->getPageContentFromAPI($page);
                
                // تنظيف البيانات قبل الحفظ
                $pageData = $this->sanitizeDataForDatabase($pageData);
                
                $page->update($pageData);
                $updatedCount++;
                $this->command->info("   ✅ Updated successfully");
                $this->command->info("   📝 Content length: " . strlen($pageData['content']) . " characters");
                
            } catch (\Exception $e) {
                $failedCount++;
                $this->command->error("   ❌ FAILED: " . $e->getMessage());
                continue;
            }

            if (($index + 1) < $pages->count()) {
                sleep(5);
            }
        }

        $this->command->info("\n" . str_repeat("=", 50));
        $this->command->info("📊 FINAL SUMMARY");
        $this->command->info("✅ Successfully updated: {$updatedCount} pages");
        $this->command->info("❌ Failed to update: {$failedCount} pages");
    }

    /**
     * تنظيف البيانات لقاعدة البيانات
     */
    private function sanitizeDataForDatabase(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->forceUtf8($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * إجبار النص على UTF-8 صالح
     */
    private function forceUtf8(string $text): string
    {
        // محاولة تحويل التشفير
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // إزالة أي أحرف غير صالحة في UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // إزالة الأحرف غير القابلة للطباعة
        $text = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u', ' ', $text);
        
        // إزالة علامات BOM
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        
        // إزالة الأحرف التحكم
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // إزالة المسافات الزائدة
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * الحصول على محتوى الصفحة من API
     */
    private function getPageContentFromAPI(StaticPage $page): array
    {
        $pageTitle = $this->cleanText($page->title ?? '');
        $slug = $page->slug ?? '';
        $phoneNumber = '+966 53 554 9535';
        
        $prompt = $this->buildEffectivePrompt($pageTitle, $slug, $phoneNumber);
        
        $this->command->info("   ↳ Sending request to DeepSeek API...");
        
        $response = Http::timeout(45)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.5,
            'max_tokens' => 3000
        ]);

        if (!$response->successful()) {
            throw new \Exception("HTTP " . $response->status());
        }

        $data = $response->json();
        
        if (isset($data['error'])) {
            throw new \Exception("API Error: " . ($data['error']['message'] ?? 'Unknown'));
        }

        $content = $data['choices'][0]['message']['content'] ?? null;
        
        if (!$content) {
            throw new \Exception('Empty response from API');
        }

        return $this->processApiResponse($content, $pageTitle, $phoneNumber, $slug);
    }

    /**
     * بناء Prompt فعال
     */
    private function buildEffectivePrompt(string $pageTitle, string $slug, string $phoneNumber): string
    {
        $contentTypes = [
            'syas-alkhsosy' => [
                'title' => 'سياسة الخصوصية',
                'desc' => 'سياسة خصوصية مفصلة لمطبعة. تحدث عن: جمع البيانات، حماية المعلومات، ملفات التصميم، حقوق المستخدم.'
            ],
            'syas-alastrgaaa' => [
                'title' => 'سياسة الاسترجاع',
                'desc' => 'سياسة استرجاع واستبدال شاملة. تحدث عن: الشروط، الإجراءات، المدة، حالات القبول والرفض.'
            ],
            'aldman' => [
                'title' => 'الضمان والجودة',
                'desc' => 'سياسة ضمان وجودة كاملة. تحدث عن: معايير الجودة، مدة الضمان، إجراءات المطالبة، الصيانة.'
            ],
            'mn-nhn' => [
                'title' => 'من نحن',
                'desc' => 'صفحة تعريفية موسعة. تحدث عن: تاريخ التأسيس، الرؤية، الرسالة، الفريق، المعدات، الإنجازات.'
            ],
            'alshrot-oalahkam' => [
                'title' => 'الشروط والأحكام',
                'desc' => 'شروط وأحكام قانونية. تحدث عن: شروط الاستخدام، الحقوق، المسؤوليات، التسعير، التسليم.'
            ],
            'alshrkaaa' => [
                'title' => 'الشركاء',
                'desc' => 'صفحة الشركاء والتعاون. تحدث عن: أنواع الشراكات، الفوائد، الشروط، آلية التقديم.'
            ],
            'alfryk' => [
                'title' => 'الفريق',
                'desc' => 'صفحة فريق العمل. تحدث عن: الهيكل التنظيمي، التخصصات، الخبرات، التدريب، الإنجازات.'
            ]
        ];

        $type = $contentTypes[$slug] ?? ['title' => $pageTitle, 'desc' => 'محتوى عالي الجودة'];

        return "أنت مساعد متخصص في كتابة محتوى عربي لمواقع الشركات الصناعية.

**الشركة:** مطبعة تلا الجزيرة
**التخصص:** الطباعة الرقمية، الدعاية والإعلان، التغليف
**العنوان:** الرياض، المملكة العربية السعودية
**الهاتف:** {$phoneNumber}
**الخبرة:** أكثر من 10 سنوات

**المطلوب:** كتابة محتوى لصفحة: {$type['title']}

**تفاصيل المحتوى المطلوب:**
{$type['desc']}

**نصائح مهمة:**
1. استخدم لغة عربية فصيحة وسليمة
2. المحتوى يجب أن يكون مفيداً وجذاباً
3. أضف معلومات واقعية عن مطبعة تلا الجزيرة
4. تأكد من ذكر رقم الهاتف {$phoneNumber}
5. استخدم تنسيق HTML بسيط (العناوين والفقرات والقوائم)

**مثال على تنسيق الرد المطلوب:**
{
  \"content\": \"<h1>عنوان الصفحة</h1><p>محتوى عالي الجودة...</p>\",
  \"meta_title\": \"عنوان SEO قصير وجذاب\",
  \"meta_description\": \"وصف مختصر للصفحة\",
  \"meta_keywords\": \"كلمة1, كلمة2, كلمة3\"
}

**ملاحظة:** أرجو الرد بتنسيق JSON فقط بدون أي نص إضافي.";
    }

    /**
     * معالجة رد API
     */
    private function processApiResponse(string $response, string $pageTitle, string $phoneNumber, string $slug): array
    {
        $response = $this->forceUtf8($response);
        $this->command->info("   ↳ Response received: " . strlen($response) . " chars");

        // محاولة استخراج JSON
        $jsonData = $this->extractJsonFromResponse($response);
        
        if ($jsonData) {
            $this->command->info("   ↳ JSON extracted successfully");
            return $this->validateAndEnhanceData($jsonData, $pageTitle, $phoneNumber, $slug);
        }

        // إذا لم يتم العثور على JSON، أنشئ البيانات من النص
        $this->command->info("   ↳ No JSON found, creating from text");
        return $this->createDataFromText($response, $pageTitle, $phoneNumber, $slug);
    }

    /**
     * استخراج JSON من الرد
     */
    private function extractJsonFromResponse(string $response): ?array
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart === false || $jsonEnd === false || $jsonEnd <= $jsonStart) {
            return null;
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $jsonString = str_replace(["\n", "\r", "\t"], ' ', $jsonString);
        $jsonString = preg_replace('/,\s*}/', '}', $jsonString);
        $jsonString = preg_replace('/,\s*]/', ']', $jsonString);

        $data = json_decode($jsonString, true);
        
        if (json_last_error() === JSON_ERROR_NONE && !empty($data)) {
            return $data;
        }

        return null;
    }

    /**
     * إنشاء البيانات من النص العادي
     */
    private function createDataFromText(string $text, string $pageTitle, string $phoneNumber, string $slug): array
    {
        $cleanText = $this->forceUtf8($text);
        
        if (!Str::contains($cleanText, '<')) {
            $paragraphs = explode("\n", $cleanText);
            $htmlContent = "<h1>{$pageTitle}</h1>\n";
            
            foreach ($paragraphs as $para) {
                $para = trim($para);
                if (!empty($para)) {
                    $htmlContent .= "<p>{$para}</p>\n";
                }
            }
            $cleanText = $htmlContent;
        }

        $enhancedText = $this->enhanceContent($cleanText, $pageTitle, $phoneNumber, $slug);
        
        return [
            'content' => $enhancedText,
            'meta_title' => $this->generateMetaTitle($pageTitle),
            'meta_description' => $this->generateMetaDescription($pageTitle, $phoneNumber),
            'meta_keywords' => $this->generateKeywords($pageTitle)
        ];
    }

    /**
     * تحقق وتعزيز البيانات
     */
    private function validateAndEnhanceData(array $data, string $pageTitle, string $phoneNumber, string $slug): array
    {
        $content = $data['content'] ?? '';
        $metaTitle = $data['meta_title'] ?? $this->generateMetaTitle($pageTitle);
        $metaDescription = $data['meta_description'] ?? $this->generateMetaDescription($pageTitle, $phoneNumber);
        $metaKeywords = $data['meta_keywords'] ?? $this->generateKeywords($pageTitle);

        if (strlen($content) < 300) {
            throw new \Exception('Content too short (less than 300 characters)');
        }

        $enhancedContent = $this->enhanceContent($content, $pageTitle, $phoneNumber, $slug);
        
        // تنظيف النهائي
        $cleanContent = $this->cleanHtmlContent($enhancedContent);
        $cleanContent = $this->forceUtf8($cleanContent);
        
        $cleanMetaTitle = $this->forceUtf8($metaTitle);
        $cleanMetaDesc = $this->forceUtf8($metaDescription);
        $cleanKeywords = $this->forceUtf8($metaKeywords);

        if (strlen($cleanMetaTitle) > 60) {
            $cleanMetaTitle = substr($cleanMetaTitle, 0, 57) . '...';
        }
        
        if (strlen($cleanMetaDesc) > 155) {
            $cleanMetaDesc = substr($cleanMetaDesc, 0, 152) . '...';
        }

        return [
            'content' => $cleanContent,
            'meta_title' => $cleanMetaTitle,
            'meta_description' => $cleanMetaDesc,
            'meta_keywords' => $cleanKeywords
        ];
    }

    /**
     * تعزيز المحتوى
     */
    private function enhanceContent(string $content, string $pageTitle, string $phoneNumber, string $slug): string
    {
        $enhanced = $content;
        
        if (!Str::contains($content, 'تلا الجزيرة')) {
            $companyInfo = "\n\n<div class=\"company-info\">\n<h3>مطبعة تلا الجزيرة</h3>\n";
            $companyInfo .= "<p>نحن مطبعة تلا الجزيرة، نعمل في مجال الطباعة والدعاية والإعلان منذ أكثر من 10 سنوات.</p>\n";
            $companyInfo .= "</div>\n";
            $enhanced .= $companyInfo;
        }

        if (!Str::contains($content, $phoneNumber)) {
            $contactInfo = "\n\n<div class=\"contact-info\">\n<h3>للتواصل معنا</h3>\n";
            $contactInfo .= "<p><strong>الهاتف:</strong> {$phoneNumber}</p>\n";
            $contactInfo .= "<p><strong>ساعات العمل:</strong> السبت إلى الخميس، 8:00 ص - 8:00 م</p>\n";
            $contactInfo .= "</div>\n";
            $enhanced .= $contactInfo;
        }

        return $this->forceUtf8($enhanced);
    }

    /**
     * توليد عنوان ميتا
     */
    private function generateMetaTitle(string $pageTitle): string
    {
        return $this->forceUtf8("{$pageTitle} | مطبعة تلا الجزيرة - الطباعة والدعاية والإعلان");
    }

    /**
     * توليد وصف ميتا
     */
    private function generateMetaDescription(string $pageTitle, string $phoneNumber): string
    {
        return $this->forceUtf8("{$pageTitle} من مطبعة تلا الجزيرة. خبرة أكثر من 10 سنوات في الطباعة الرقمية، الدعاية والإعلان، التغليف والتعبئة. تواصل: {$phoneNumber}");
    }

    /**
     * توليد كلمات مفتاحية
     */
    private function generateKeywords(string $pageTitle): string
    {
        $baseKeywords = ['تلا الجزيرة', 'مطبعة', 'طباعة', 'دعاية', 'إعلان', '+966 53 554 9535'];
        $pageKeywords = explode(' ', $pageTitle);
        
        $allKeywords = array_merge($pageKeywords, $baseKeywords);
        $uniqueKeywords = array_unique(array_filter($allKeywords));
        
        return $this->forceUtf8(implode(', ', $uniqueKeywords));
    }

    /**
     * تنظيف النص
     */
    private function cleanText(string $text): string
    {
        return $this->forceUtf8($text);
    }

    /**
     * تنظيف محتوى HTML
     */
    private function cleanHtmlContent(string $html): string
    {
        $html = $this->forceUtf8($html);
        
        $allowedTags = '<h1><h2><h3><h4><h5><h6><p><br><div><span><strong><b><em><i><u><ul><ol><li><a><table><tr><td><th>';
        $html = strip_tags($html, $allowedTags);
        
        $html = preg_replace('/<(\w+)[^>]*>/', '<$1>', $html);
        $html = preg_replace('/<(\w+)>\s*<\/\1>/', '', $html);
        
        return trim($html);
    }

    /**
     * تشغيل صفحة واحدة فقط للتجربة
     */
    public function runSinglePage($pageId)
    {
        $this->apiKey = env('DEEPSEEK_API_KEY');

        if (!$this->apiKey) {
            $this->command->error('❌ DEEPSEEK_API_KEY is missing');
            return;
        }

        $page = StaticPage::findOrFail($pageId);
        
        $this->command->info("\n🎯 TESTING SINGLE PAGE: {$page->title} (ID: {$page->id})");
        $this->command->info(str_repeat("-", 50));

        try {
            $pageData = $this->getPageContentFromAPI($page);
            $pageData = $this->sanitizeDataForDatabase($pageData);
            
            $this->command->info("   ✅ Data sanitized for database");
            $this->command->info("   📊 Content length: " . strlen($pageData['content']) . " chars");
            
            $page->update($pageData);
            $this->command->info("\n   🎉 PAGE UPDATED SUCCESSFULLY!");
            
        } catch (\Exception $e) {
            $this->command->error("   ❌ FAILED: " . $e->getMessage());
        }
    }
}