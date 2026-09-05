<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'ما هي سياسة الإرجاع؟',
                'answer' => 'يمكنك إرجاع المنتجات خلال 14 يومًا من تاريخ الاستلام بشرط أن تكون بحالتها الأصلية.',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'question' => 'كيف يمكنني تتبع الطلب؟',
                'answer' => 'يمكنك متابعة حالة طلبك من خلال صفحة "طلباتي" داخل حسابك.',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'question' => 'هل يوجد خدمة عملاء؟',
                'answer' => 'نعم، يمكنك التواصل معنا على مدار 24 ساعة عبر صفحة "اتصل بنا".',
                'sort_order' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
