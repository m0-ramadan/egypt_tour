<?php

namespace Tests\Unit;

use App\Support\LocaleNormalizer;
use PHPUnit\Framework\TestCase;

class LocaleNormalizerTest extends TestCase
{
    public function test_it_normalizes_legacy_english_and_arabic_codes(): void
    {
        $normalizer = new LocaleNormalizer();

        $this->assertSame('en', $normalizer->normalize('english'));
        $this->assertSame('en', $normalizer->normalize('en-US'));
        $this->assertSame('ar', $normalizer->normalize('arabic'));
        $this->assertSame('ar', $normalizer->normalize('ar_EG'));
    }
}
