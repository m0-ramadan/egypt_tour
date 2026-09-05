<?php

namespace Tests\Unit;

use App\Models\Package;
use Tests\TestCase;

class GroupPricingTiersTest extends TestCase
{
    public function test_package_returns_default_6_group_pricing_tiers()
    {
        $package = new Package([
            'price_from' => 100,
            'price_1_person' => 200,
            'price_2_persons' => 150,
            'price_3_persons' => 140,
            'price_4_persons' => 130,
            'price_5_persons' => 120,
            'price_6_plus_persons' => 110,
        ]);

        $tiers = $package->group_pricing_tiers;

        $this->assertCount(6, $tiers);
        $this->assertEquals(200, $tiers[0]['price_per_person']);
        $this->assertEquals(150, $tiers[1]['price_per_person']);
        $this->assertEquals(140, $tiers[2]['price_per_person']);
        $this->assertEquals(130, $tiers[3]['price_per_person']);
        $this->assertEquals(120, $tiers[4]['price_per_person']);
        $this->assertEquals(110, $tiers[5]['price_per_person']);

        $this->assertTrue($tiers[1]['is_popular']);
        $this->assertTrue($tiers[5]['is_best_value']);
        $this->assertTrue($tiers[5]['is_variable']);
    }

    public function test_package_group_pricing_tiers_fallback_to_price_from()
    {
        $package = new Package([
            'price_from' => 100,
        ]);

        $tiers = $package->group_pricing_tiers;

        $this->assertCount(6, $tiers);
        $this->assertEquals(130.0, $tiers[0]['price_per_person']);
        $this->assertEquals(100.0, $tiers[1]['price_per_person']);
    }
}
