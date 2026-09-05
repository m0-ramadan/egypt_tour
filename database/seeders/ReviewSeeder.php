<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class ReviewSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();
        $users = User::all();

        $comments = [
            'منتج ممتاز وجودة عالية جداً',
            'راضي جداً عن المنتج والتوصيل كان سريع',
            'جودة ممتازة وسعر مناسب',
            'المنتج أفضل من المتوقع',
            'تعامل راقي وتوصيل سريع',
            'جودة عالية ننصح بالشراء',
            'منتج رائع يستحق الشراء',
            'ممتاز جداً وبسعر مناسب',
            'تجربة رائعة مع هذا المنتج',
            'الجودة فوق التوقعات بكثير',
        ];

        foreach ($products->random(min(50, $products->count())) as $product) {
            $reviewCount = rand(2, 8);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id,
                    'rating' => rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                ]);
            }
        }
    }
}
