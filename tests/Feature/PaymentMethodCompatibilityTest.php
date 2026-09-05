<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentMethodCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_model_matches_real_schema_and_legacy_aliases(): void
    {
        $method = PaymentMethod::create([
            'name' => 'Card',
            'code' => 'card',
            'provider' => 'manual',
            'currency_code' => 'EGP',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 1,
        ]);

        $this->assertSame('manual', $method->type);
        $this->assertSame('card', $method->key);
        $this->assertTrue($method->is_payment);
        $this->assertFalse($method->is_wallet);

        $this->assertTrue(Schema::hasColumns('payment_methods', [
            'name',
            'code',
            'provider',
            'description',
            'config',
            'currency_code',
            'is_active',
            'is_default',
            'sort_order',
        ]));
    }
}
