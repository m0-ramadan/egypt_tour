<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleCommentSeeder extends Seeder
{
    public function run(): void
    {
        $articles = Article::all();
        $users = User::take(5)->get();

        $comments = [
            [
                'content' => 'مقال رائع ومفيد جدًا! كنت أبحث عن هذه المعلومات منذ فترة طويلة.',
                'is_approved' => true
            ],
            [
                'content' => 'هل يمكنك توضيح المزيد عن تكلفة التطريز الآلي مقارنة باليدوي؟',
                'is_approved' => true
            ],
            [
                'content' => 'أحببت جدول المقارنة بين التقنيات، كان واضحًا وشاملاً.',
                'is_approved' => true
            ],
            [
                'content' => 'هل لديكم نصائح إضافية للمبتدئين في مجال الطباعة الرقمية؟',
                'is_approved' => true
            ],
            [
                'content' => 'المعلومات المقدمة دقيقة وموثوقة، شكرًا على المجهود الرائع.',
                'is_approved' => true
            ],
            [
                'content' => 'أتمنى أن تقدموا مقالًا عن أحدث تقنيات الطباعة ثلاثية الأبعاد.',
                'is_approved' => true
            ],
            [
                'content' => 'كم تستغرق فترة التدريب لإتقان التطريز اليدوي للمبتدئين؟',
                'is_approved' => true
            ],
            [
                'content' => 'الأسئلة الشائعة كانت مفيدة جدًا، أجابت على معظم استفساراتي.',
                'is_approved' => true
            ],
            [
                'content' => 'هل تقدمون دورات تدريبية في مجال التصميم؟',
                'is_approved' => true
            ],
            [
                'content' => 'ممتاز! المقال شامل ويغطي جميع الجوانب المهمة.',
                'is_approved' => true
            ]
        ];

        foreach ($articles as $article) {
            // إضافة تعليقات رئيسية
            for ($i = 0; $i < rand(3, 8); $i++) {
                $commentData = $comments[array_rand($comments)];
                $user = $users->random();

                $comment = ArticleComment::create([
                    'article_id' => $article->id,
                    'user_id' => $user->id,
                    'content' => $commentData['content'],
                    'is_approved' => $commentData['is_approved'],
                    'created_at' => now()->subDays(rand(1, 30))
                ]);

                // إضافة ردود على بعض التعليقات
                if (rand(0, 1)) {
                    ArticleComment::create([
                        'article_id' => $article->id,
                        'user_id' => $users->random()->id,
                        'parent_id' => $comment->id,
                        'content' => 'شكرًا على تعليقك! سنحاول تلبية طلبك في المقالات القادمة.',
                        'is_approved' => true,
                        'created_at' => $comment->created_at->addHours(rand(1, 48))
                    ]);
                }

                // إضافة رد من إدارة الموقع
                if (rand(0, 1)) {
                    $admin = User::where('email', 'admin@example.com')->first();
                    if ($admin) {
                        ArticleComment::create([
                            'article_id' => $article->id,
                            'user_id' => $admin->id,
                            'parent_id' => $comment->id,
                            'content' => 'شكرًا لك على تفاعلك! نحن سعداء بأن المقالة كانت مفيدة لك.',
                            'is_approved' => true,
                            'created_at' => $comment->created_at->addHours(rand(2, 72))
                        ]);
                    }
                }
            }

            // إضافة تعليقات من زوار بدون حساب
            for ($i = 0; $i < rand(2, 5); $i++) {
                $commentData = $comments[array_rand($comments)];

                ArticleComment::create([
                    'article_id' => $article->id,
                    'name' => ['أحمد', 'سارة', 'محمد', 'فاطمة', 'خالد'][array_rand([0, 1, 2, 3, 4])],
                    'email' => 'visitor' . rand(1, 100) . '@example.com',
                    'content' => $commentData['content'],
                    'is_approved' => true,
                    'created_at' => now()->subDays(rand(1, 15))
                ]);
            }
        }
    }
}
