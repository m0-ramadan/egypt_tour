<?php

namespace Tests\Unit;

use App\Services\PackageTagNormalizer;
use Tests\TestCase;

class PackageTagNormalizerTest extends TestCase
{
    public function test_it_normalizes_hashes_whitespace_and_case_insensitive_duplicates(): void
    {
        $tags = app(PackageTagNormalizer::class)->normalizeList([
            ' #Egypt ',
            'egypt',
            '  معابد   النوبة  ',
            '#معابد النوبة',
        ]);

        $this->assertSame(['Egypt', 'معابد النوبة'], $tags);
    }

    public function test_long_unicode_tag_has_a_database_safe_deterministic_slug(): void
    {
        $normalizer = app(PackageTagNormalizer::class);
        $tag = str_repeat('Nubian Temples أبو سمبل ', 30);

        $this->assertLessThanOrEqual(130, mb_strlen($normalizer->slug($tag)));
        $this->assertSame($normalizer->slug($tag), $normalizer->slug($tag));
    }

    public function test_it_reports_count_and_length_limits_before_persistence(): void
    {
        config(['translation.max_tag_count' => 2, 'translation.max_tag_length' => 10]);
        $normalizer = app(PackageTagNormalizer::class);

        $this->assertCount(2, $normalizer->validationErrors(['one', 'two', 'this tag is too long']));
    }
}
