<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        // الحصول على المستخدم الأول ككاتب
        $author = User::first() ?? User::factory()->create();

        // الحصول على الأقسام
        $embroideryCategory = ArticleCategory::where('slug', 'ttryz')->first();
        $printingCategory = ArticleCategory::where('slug', 'tbaaa')->first();
        $designCategory = ArticleCategory::where('slug', 'tsmym')->first();

        $articles = [
            [
                'title' => 'دليل شامل لأنواع التطريز الحديثة',
                'excerpt' => 'استكشف معنا أحدث تقنيات التطريز التي تمنح ملابسك لمسة فريدة من الأناقة والتميز.',
                'category_id' => $embroideryCategory->id,
                'is_featured' => true,
                'reading_time' => 8
            ],
            [
                'title' => 'كيف تختار طريقة الطباعة المناسبة لعملك؟',
                'excerpt' => 'دليل مفصل يساعدك في اختيار تقنية الطباعة الأمثل لمشروعك التجاري أو الشخصي.',
                'category_id' => $printingCategory->id,
                'is_featured' => true,
                'reading_time' => 6
            ],
            [
                'title' => '10 نصائح ذهبية للعناية بالملابس المطبوعة',
                'excerpt' => 'احفظ جودة الطباعة على ملابسك لأطول فترة ممكنة مع هذه النصائح العملية.',
                'category_id' => $designCategory->id,
                'is_featured' => false,
                'reading_time' => 5
            ],
            [
                'title' => 'أفكار إبداعية لتصميم شعارات الشركات',
                'excerpt' => 'استلهم أفكارًا مبتكرة لتصميم شعارات مميزة تعبر عن هوية علامتك التجارية.',
                'category_id' => $designCategory->id,
                'is_featured' => true,
                'reading_time' => 7
            ],
            [
                'title' => 'تقنيات الطباعة ثلاثية الأبعاد على الملابس',
                'excerpt' => 'تعرف على مستقبل الطباعة في عالم الأزياء مع تقنية الطباعة ثلاثية الأبعاد.',
                'category_id' => $printingCategory->id,
                'is_featured' => false,
                'reading_time' => 9
            ],
            [
                'title' => 'فن التطريز التقليدي: تراث يتجدد',
                'excerpt' => 'رحلة في عالم التطريز التقليدي وكيفية دمجه مع التصاميم المعاصرة.',
                'category_id' => $embroideryCategory->id,
                'is_featured' => false,
                'reading_time' => 10
            ],
            [
                'title' => 'دليل ألوان الطباعة: كيف تختار الألوان المناسبة؟',
                'excerpt' => 'تعلم أسرار اختيار ألوان الطباعة التي تعبر عن شخصية علامتك التجارية.',
                'category_id' => $designCategory->id,
                'is_featured' => true,
                'reading_time' => 6
            ],
            [
                'title' => 'أحدث صيحات الملابس المطرزة لعام 2024',
                'excerpt' => 'اكتشف أحدث اتجاهات الموضة في عالم الملابس المطرزة لهذا العام.',
                'category_id' => $embroideryCategory->id,
                'is_featured' => false,
                'reading_time' => 5
            ]
        ];

        foreach ($articles as $index => $article) {
            Article::create([
                'title' => $article['title'],
                'slug' => Str::slug($article['title']),
                'content' => $this->generateArticleContent($article['title'], $article['category_id']),
                'excerpt' => $article['excerpt'],
                'image' => 'articles/article-' . ($index + 1) . '.jpg',
                'image_alt' => $article['title'],
                'views_count' => rand(150, 2500),
                'reading_time' => $article['reading_time'],
                'author_id' => $author->id,
                'category_id' => $article['category_id'],
                'meta_title' => $article['title'] . ' | مدونة متجر التطريز والطباعة',
                'meta_description' => $article['excerpt'],
                'meta_keywords' => $this->generateKeywords($article['title']),
                'published_at' => Carbon::now()->subDays(rand(1, 90)),
                'is_active' => true,
                'is_featured' => $article['is_featured'],
                'order' => $index + 1
            ]);
        }
    }

    private function generateArticleContent(string $title, int $categoryId): string
    {
        $category = ArticleCategory::find($categoryId);
        $categoryName = $category ? $category->name : 'التطريز والطباعة';

        $content = <<<HTML
<div class="article-content">
    <h1>{$title}</h1>
    
    <div class="intro">
        <p>في عالم <strong>{$categoryName}</strong> المتطور باستمرار، تظهر تقنيات وأساليب جديدة تمنح المصممين والمبدعين أدوات أكثر تنوعًا للتعبير عن أفكارهم. في هذه المقالة، سنأخذك في رحلة استكشافية لأحدث ما توصل إليه هذا المجال المثير.</p>
    </div>

    <div class="featured-image">
        <img src="/images/articles/featured.jpg" alt="{$title}" class="img-fluid rounded">
        <p class="image-caption text-muted mt-2">صورة توضيحية لعملية {$categoryName} الاحترافية</p>
    </div>

    <h2>مقدمة في عالم {$categoryName}</h2>
    
    <p>بدأت رحلة <strong>{$categoryName}</strong> منذ عقود طويلة، حيث كانت تمارس بطرق بدائية تعتمد على المهارة اليدوية والخبرة المتراكمة. ومع تطور التكنولوجيا، تحولت هذه الحرفة إلى صناعة متكاملة تستخدم أحدث الأجهزة والبرامج.</p>

    <blockquote class="blockquote">
        <p class="mb-0">"الجودة في {$categoryName} لا تعني فقط الدقة في التنفيذ، بل تعني أيضًا الإبداع في التصميم والابتكار في التنفيذ."</p>
    </blockquote>

    <h2>أنواع وأساليب {$categoryName}</h2>
    
    <p>تتنوع أساليب <strong>{$categoryName}</strong> حسب الغرض منها والمواد المستخدمة. إليك بعض الأنواع الرئيسية:</p>

    <ul>
        <li><strong>التقليدي:</strong> يعتمد على الأساليب اليدوية والحرفية المتوارثة عبر الأجيال</li>
        <li><strong>الحديث:</strong> يستخدم التقنيات الرقمية والآلات المتطورة</li>
        <li><strong>المختلط:</strong> يجمع بين الأصالة التقليدية والتكنولوجيا الحديثة</li>
        <li><strong>التجريبي:</strong> يبحث عن طرق جديدة وغير تقليدية للتعبير</li>
    </ul>

    <h2>خطوات العمل الأساسية</h2>
    
    <p>يتم تنفيذ مشروع <strong>{$categoryName}</strong> عبر عدة مراحل متتالية:</p>

    <ol>
        <li>التخطيط والتصميم المبدئي</li>
        <li>اختيار المواد والخامات المناسبة</li>
        <li>التحضير والتجهيز للتنفيذ</li>
        <li>التنفيذ الفعلي</li>
        <li>المراجعة والتحسين</li>
        <li>التسليم والضمان</li>
    </ol>

    <div class="tips">
        <h3>نصائح عملية للمبتدئين</h3>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">ابدأ بسيطًا</h5>
                        <p class="card-text">لا تحاول تنفيذ تصميمات معقدة في البداية. ابدأ بمشاريع بسيطة وتدرب عليها.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">اختر المواد المناسبة</h5>
                        <p class="card-text">جودة المواد المستخدمة تؤثر بشكل مباشر على النتيجة النهائية.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2>أدوات ومواد أساسية</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>الأداة</th>
                <th>الوصف</th>
                <th>الاستخدام</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ماكينة {$categoryName} الرقمية</td>
                <td>أحدث ماكينات {$categoryName} التي تعمل بالتحكم الرقمي</td>
                <td>للمشاريع التجارية والدقيقة</td>
            </tr>
            <tr>
                <td>برامج التصميم المتخصصة</td>
                <td>برامج مثل CorelDRAW، Adobe Illustrator</td>
                <td>لتصميم النماذج والرسومات</td>
            </tr>
            <tr>
                <td>الخيوط والألوان</td>
                <td>خيوط عالية الجودة وأحبار متخصصة</td>
                <td>لتنفيذ التصاميم</td>
            </tr>
            <tr>
                <td>مواد التشطيب</td>
                <td>مواد حماية وتلميع</td>
                <td>للحفاظ على جودة العمل</td>
            </tr>
        </tbody>
    </table>

    <h2>أهمية الجودة في {$categoryName}</h2>
    
    <p>تعتبر الجودة عاملاً حاسماً في نجاح أي مشروع <strong>{$categoryName}</strong>. الجودة العالية لا تؤثر فقط على المظهر النهائي، بل أيضًا على:</p>

    <ul>
        <li><strong>المتانة:</strong> عمر المنتج الافتراضي</li>
        <li><strong>المظهر:</strong> الجاذبية البصرية</li>
        <li><strong>السمعة:</strong> انطباع العملاء عن العلامة التجارية</li>
        <li><strong>التكلفة:</strong> التوفير على المدى الطويل</li>
    </ul>

    <div class="comparison">
        <h3>مقارنة بين التقنيات المختلفة</h3>
        
        <p>تختلف التقنيات المستخدمة في <strong>{$categoryName}</strong> من حيث التكلفة والجودة والوقت. إليك مقارنة سريعة:</p>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>التقنية</th>
                        <th>المميزات</th>
                        <th>العيوب</th>
                        <th>السعر</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>التقليدية</td>
                        <td>جودة عالية، فريدة</td>
                        <td>مكلفة، تستغرق وقتًا</td>
                        <td>$$$</td>
                    </tr>
                    <tr>
                        <td>الرقمية</td>
                        <td>سريعة، دقيقة</td>
                        <td>تتطلب خبرة تقنية</td>
                        <td>$$</td>
                    </tr>
                    <tr>
                        <td>الهجينة</td>
                        <td>تجمع المميزات</td>
                        <td>معقدة التنفيذ</td>
                        <td>$$-$$$</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <h2>الاستدامة في {$categoryName}</h2>
    
    <p>أصبحت الاستدامة من الأولويات في صناعة <strong>{$categoryName}</strong>. تشمل الممارسات المستدامة:</p>

    <ul>
        <li>استخدام مواد صديقة للبيئة</li>
        <li>تقليل استهلاك الطاقة</li>
        <li>إعادة تدوير المخلفات</li>
        <li>تقليل استخدام المياه</li>
        <li>الاعتماد على مصادر طاقة متجددة</li>
    </ul>

    <div class="faq">
        <h3>الأسئلة الشائعة</h3>
        
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h4 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        كم يستغرق تنفيذ مشروع {$categoryName}؟
                    </button>
                </h4>
                <div id="faq1" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <p>يختلف الوقت حسب حجم المشروع وتعقيده. قد يستغرق المشروع البسيط من 2-3 أيام، بينما قد يحتاج المشروع المعقد لعدة أسابيع.</p>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h4 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        ما هي تكلفة مشروع {$categoryName}؟
                    </button>
                </h4>
                <div id="faq2" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <p>تعتمد التكلفة على عدة عوامل: حجم العمل، المواد المستخدمة، التعقيد، والوقت. ننصح بالحصول على عرض سعر مفصل حسب متطلباتك.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="conclusion">
        <h3>خاتمة</h3>
        
        <p>يعد مجال <strong>{$categoryName}</strong> من المجالات الحيوية والمتطورة باستمرار. مع التقدم التكنولوجي، أصبح بإمكاننا تحقيق نتائج مذهلة كانت تعتبر مستحيلة في الماضي. المفتاح للنجاح في هذا المجال هو الجمع بين الإبداع الفني والخبرة التقنية والاهتمام بالجودة.</p>
        
        <p>نتمنى أن تكون هذه المقالة قد قدمت لك نظرة شاملة عن عالم <strong>{$categoryName}</strong>. إذا كان لديك أي أسئلة أو استفسارات، لا تتردد في التواصل معنا.</p>
    </div>

    <div class="call-to-action bg-light p-4 rounded mt-4">
        <h4>جاهز لبدء مشروعك في {$categoryName}؟</h4>
        <p>تواصل مع فريقنا من الخبراء للحصول على استشارة مجانية وتصميم مخصص يناسب احتياجاتك.</p>
        <a href="/contact" class="btn btn-primary">اتصل بنا الآن</a>
    </div>
</div>
HTML;

        return $content;
    }

    private function generateKeywords(string $title): string
    {
        $baseKeywords = 'تطريز, طباعة, تصميم, ملابس, ازياء, موضة, شركات, شعارات';

        $titleWords = explode(' ', $title);
        $titleKeywords = array_slice($titleWords, 0, 5);

        return implode(', ', $titleKeywords) . ', ' . $baseKeywords;
    }
}
