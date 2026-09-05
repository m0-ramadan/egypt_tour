<?php

namespace Tests\Feature;

use App\Http\Middleware\LogVisitor;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;
use Torann\GeoIP\Facades\GeoIP;

class GeoIpGracefulFallbackTest extends TestCase
{
    public function test_geoip_exception_is_cached_as_an_empty_non_breaking_fallback(): void
    {
        Cache::flush();
        GeoIP::shouldReceive('getLocation')->once()->andThrow(new \RuntimeException('provider unavailable'));

        $middleware = app(LogVisitor::class);
        $method = new ReflectionMethod($middleware, 'getGeoIpData');

        $this->assertSame([], $method->invoke($middleware, '8.8.8.8', false));
        $this->assertSame([], $method->invoke($middleware, '8.8.8.8', false));
    }

    public function test_bot_lookup_is_skipped_by_default(): void
    {
        GeoIP::shouldReceive('getLocation')->never();

        $method = new ReflectionMethod(app(LogVisitor::class), 'getGeoIpData');
        $this->assertSame([], $method->invoke(app(LogVisitor::class), '1.1.1.1', true));
    }
}
