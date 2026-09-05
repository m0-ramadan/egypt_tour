<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_decimal_money_is_converted_without_float_accumulation(): void
    {
        $this->assertSame(12345, Money::toMinor('123.45'));
        $this->assertSame(5, Money::toMinor('0.05'));
        $this->assertSame('123.45', Money::fromMinor(12345));
    }
}
