<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'name' => 'عروض الصيف الكبرى',
                'products_count' => 10,
            ],
            [
                'name' => 'تخفيضات نهاية الموسم',
                'products_count' => 15,
            ],
            [
                'name' => 'عروض الجمعة البيضاء',
                'products_count' => 20,
            ],
            [
                'name' => 'عروض رمضان',
                'products_count' => 12,
            ],
            [
                'name' => 'عروض العودة للمدارس',
                'products_count' => 8,
            ],
        ];

        foreach ($offers as $offerData) {
            // إنشاء العرض
            $offer = Offer::create([
                'name' => $offerData['name'],
                // نضيف اسم عشوائي فريد للصورة لتفادي التكرار
                'image' => 'offers/' . Str::random(10) . '.jpg',
            ]);

            // اختيار منتجات فيها خصم فقط
            $products = Product::query()
                ->where('has_discount', true)
                ->inRandomOrder()
                ->limit($offerData['products_count'])
                ->pluck('id');

            // لو مفيش منتجات خصم، نختار أي منتجات عشوائية بدلها
            if ($products->isEmpty()) {
                $products = Product::inRandomOrder()
                    ->limit($offerData['products_count'])
                    ->pluck('id');
            }

            // ربط المنتجات بالعرض
            $offer->products()->attach($products);
        }
    }
}
