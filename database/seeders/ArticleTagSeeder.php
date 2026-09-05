<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ArticleTagSeeder extends Seeder
{
    public function run(): void
    {
        $articles = Article::all();
        $tags = Tag::all();

        foreach ($articles as $article) {
            // ربط كل مقالة بـ 3-5 وسوم عشوائية
            $randomTags = $tags->random(rand(3, 5));
            $article->tags()->attach($randomTags->pluck('id'));
        }
    }
}
