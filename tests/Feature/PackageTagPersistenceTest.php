<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Services\PackageTypeContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PackageTagPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_long_arabic_multilingual_and_duplicate_tags_are_persisted_safely(): void
    {
        $package = Package::query()->create([
            'package_type' => 'day_tour',
            'slug' => 'package-tag-test',
            'title' => ['en' => 'Package Tag Test', 'ar' => 'اختبار وسوم الباقة'],
            'is_active' => true,
        ]);
        $long = 'Nubian Temples #Aswan Tours #Abu Simbel Tours #Egypt Cruises ' . str_repeat('رحلة ', 30);
        $request = Request::create('/', 'POST', [
            'tags' => [$long, '#Egypt', 'egypt', 'معابد النوبة'],
        ]);

        app(PackageTypeContentService::class)->syncFromRequest($package, $request);

        $package->refresh()->load('tags');
        $this->assertCount(3, $package->tags);
        $normalizer = app(\App\Services\PackageTagNormalizer::class);
        $this->assertSame(
            $normalizer->normalizeName($long),
            $package->tags->firstWhere('slug', $normalizer->slug($long))->name['en']
        );
        $this->assertLessThanOrEqual(130, $package->tags->max(fn ($tag) => mb_strlen($tag->slug)));
    }
}
