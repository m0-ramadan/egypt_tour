<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition()
    {
        $title = $this->faker->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->generateHtmlContent($title),
            'excerpt' => $this->faker->paragraph(2),
            'image' => 'articles/' . $this->faker->image('storage/app/public/articles', 800, 600, null, false),
            'image_alt' => $title,
            'views_count' => $this->faker->numberBetween(100, 5000),
            'reading_time' => $this->faker->numberBetween(3, 15),
            'author_id' => User::factory(),
            'category_id' => ArticleCategory::factory(),
            'meta_title' => $title . ' | مدونتنا',
            'meta_description' => $this->faker->paragraph(1),
            'meta_keywords' => implode(', ', $this->faker->words(5)),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_active' => $this->faker->boolean(90),
            'is_featured' => $this->faker->boolean(20),
            'order' => $this->faker->numberBetween(1, 100)
        ];
    }

    private function generateHtmlContent($title)
    {
        $paragraphs = [];

        for ($i = 0; $i < 8; $i++) {
            $paragraphs[] = '<p>' . $this->faker->paragraph(rand(3, 8)) . '</p>';
        }

        $content = '<h1>' . $title . '</h1>';
        $content .= '<div class="intro">' . $this->faker->paragraph(4) . '</div>';
        $content .= '<h2>مقدمة</h2>' . implode('', array_slice($paragraphs, 0, 2));
        $content .= '<h2>المحتوى الرئيسي</h2>' . implode('', array_slice($paragraphs, 2, 4));
        $content .= '<h2>الخاتمة</h2>' . implode('', array_slice($paragraphs, 6, 2));

        return $content;
    }
}
