<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        $methods = [
            ['name' => 'الدفع عند الاستلام', 'icon' => 'fas fa-money-bill-wave', 'is_active' => true],
            ['name' => 'فيزا', 'icon' => 'fab fa-cc-visa', 'is_active' => true],
            ['name' => 'ماستركارد', 'icon' => 'fab fa-cc-mastercard', 'is_active' => true],
            ['name' => 'فوري', 'icon' => 'fas fa-mobile-alt', 'is_active' => true],
            ['name' => 'فودافون كاش', 'icon' => 'fas fa-wallet', 'is_active' => true],
            ['name' => 'إنستا باي', 'icon' => 'fas fa-credit-card', 'is_active' => true],
            ['name' => 'PayPal', 'icon' => 'fab fa-paypal', 'is_active' => true],
            ['name' => 'التحويل البنكي', 'icon' => 'fas fa-university', 'is_active' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}