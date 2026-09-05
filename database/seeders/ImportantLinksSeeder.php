<?php

namespace Database\Seeders;

use App\Models\ImportantLink;
use Illuminate\Database\Seeder;

class ImportantLinksSeeder extends Seeder
{
    public function run()
    {
        $links = [
            [
                'key' => 'privacy_policy',
                'name' => 'سياسة الخصوصية',
                'description' => '<h2>سياسة الخصوصية</h2><p>نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية. يتم استخدام البيانات لتحسين الخدمة وتقديم الدعم.</p>',
                'url' => '/privacy-policy'
            ],
            [
                'key' => 'return_policy',
                'name' => 'سياسة الاسترجاع',
                'description' => '<h2>سياسة الاسترجاع</h2><p>يمكنك استرجاع المنتجات خلال المدة المعلنة وضمن الشروط الموضحة هنا.</p>',
                'url' => '/return-policy'
            ],
            [
                'key' => 'warranty',
                'name' => 'الضمان',
                'description' => '<h2>الضمان</h2><p>تفاصيل الضمان، المدة، والحالات المشمولة وغير المشمولة بالضمان.</p>',
                'url' => '/warranty'
            ],
            [
                'key' => 'about_us',
                'name' => 'من نحن',
                'description' => '<h2>من نحن</h2><p>معلومات عن المتجر، رؤيتنا، ورسالتنا.</p>',
                'url' => '/about-us'
            ],
            [
                'key' => 'faq',
                'name' => 'الأسئلة الشائعة',
                'description' => '<h2>الأسئلة الشائعة</h2><p>جمع للأسئلة المتكررة وإجاباتها لراحة العملاء.</p>',
                'url' => '/faq'
            ],
            [
                'key' => 'join_us',
                'name' => 'انضم إلينا',
                'description' => '<h2>انضم إلينا</h2><p>فرص العمل والتطوع وكيفية التقديم للانضمام لفريقنا.</p>',
                'url' => '/join-us'
            ],
            [
                'key' => 'partners',
                'name' => 'الشركاء',
                'description' => '<h2>الشركاء</h2><p>قائمة بالشركاء والموردين مع نبذة عن التعاون.</p>',
                'url' => '/partners'
            ],
            [
                'key' => 'team',
                'name' => 'الفريق',
                'description' => '<h2>الفريق</h2><p>التعريف بأعضاء الفريق ووظائفهم وخبراتهم.</p>',
                'url' => '/team'
            ],
            // الروابط الأساسية السابقة إن أردت تضمينها أيضاً:
            [
                'key' => 'terms_conditions',
                'name' => 'الشروط والأحكام',
                'description' => '<h2>الشروط والأحكام</h2><p>الشروط التي تحكم استخدام الموقع والخدمات.</p>',
                'url' => '/terms-conditions'
            ],
            [
                'key' => 'contact_us',
                'name' => 'اتصل بنا',
                'description' => '<h2>اتصل بنا</h2><p>طرق التواصل معنا: الهاتف، البريد الإلكتروني، ونموذج التواصل.</p>',
                'url' => '/contact-us'
            ],
            [
                'key' => 'shipping_policy',
                'name' => 'سياسة الشحن',
                'description' => '<h2>سياسة الشحن</h2><p>تفاصيل الشحن، التكاليف، والمناطق المغطاة.</p>',
                'url' => '/shipping-policy'
            ],
        ];

        foreach ($links as $link) {
            ImportantLink::updateOrCreate(
                ['key' => $link['key']],
                $link
            );
        }
    }
}
