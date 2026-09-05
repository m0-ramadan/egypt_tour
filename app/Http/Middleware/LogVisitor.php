<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use App\Support\RateLimitedLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;
use Torann\GeoIP\Facades\GeoIP;

class LogVisitor
{
    public function __construct(private readonly RateLimitedLogger $logger) {}

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $ip = (string) ($request->ip() ?: '0.0.0.0');
            $agent = new Agent();
            $agent->setUserAgent((string) $request->userAgent());
            $isBot = $agent->isRobot();
            $geo = $this->getGeoIpData($ip, $isBot);

            Visitor::create([
                'ip' => $ip,
                'host' => $this->getHostByIp($ip),
                'method' => $request->method(),
                'path' => $request->path(),
                'full_url' => $request->fullUrl(),
                'referer' => $request->header('referer'),
                'user_agent' => $request->userAgent(),
                'browser' => $agent->browser(),
                'browser_version' => $agent->version($agent->browser()),
                'platform' => $agent->platform(),
                'device' => $agent->device(),
                'is_mobile' => $agent->isMobile(),
                'is_tablet' => $agent->isTablet(),
                'is_desktop' => !$agent->isMobile() && !$agent->isTablet(),
                'is_bot' => $isBot,
                'country' => $geo['country'] ?? null,
                'country_iso' => $geo['country_code'] ?? null,
                'region' => $geo['region'] ?? null,
                'city' => $geo['city'] ?? null,
                'latitude' => $geo['lat'] ?? null,
                'longitude' => $geo['lon'] ?? null,
                'timezone' => $geo['timezone'] ?? null,
                'headers' => $this->safeHeaders($request),
                'query' => $request->query(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning(
                'visitor-logging-failed',
                'Visitor analytics could not be recorded.',
                ['exception' => $e::class],
                (int) config('geoip.log_cooldown', 3600)
            );
        }

        return $response;
    }

    private function getGeoIpData(string $ip, bool $isBot): array
    {
        if (!$this->isPublicIp($ip) || ($isBot && !(bool) config('geoip.lookup_bots', false))) {
            return [];
        }

        $cacheKey = 'visitor:geoip:' . hash('sha256', $ip);
        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        } catch (\Throwable) {
            // GeoIP can still use its own cache or graceful fallback.
        }

        try {
            $location = GeoIP::getLocation($ip);
            if ($location && $location->default === false) {
                $geo = [
                    'country' => $location->country,
                    'country_code' => $location->iso_code,
                    'region' => $location->state,
                    'city' => $location->city,
                    'lat' => $location->lat,
                    'lon' => $location->lon,
                    'timezone' => $location->timezone,
                ];
                $this->cacheGeoResult($cacheKey, $geo, (int) config('geoip.visitor_cache_ttl', 2592000));
                return $geo;
            }
        } catch (\Throwable $e) {
            $this->logger->warning(
                'torann-geoip-unavailable',
                'Torann GeoIP lookup is temporarily unavailable; visitor logging will use empty location data.',
                ['exception' => $e::class],
                (int) config('geoip.log_cooldown', 3600)
            );
        }

        $this->cacheGeoResult($cacheKey, [], (int) config('geoip.failure_cache_ttl', 900));
        return [];
    }

    private function cacheGeoResult(string $key, array $value, int $ttl): void
    {
        try {
            Cache::put($key, $value, max(60, $ttl));
        } catch (\Throwable) {
            // Analytics remains non-critical when cache storage is unavailable.
        }
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function getHostByIp(string $ip): ?string
    {
        if (!(bool) config('geoip.reverse_dns', false) || !$this->isPublicIp($ip)) {
            return null;
        }

        $key = 'visitor:host:' . hash('sha256', $ip);
        try {
            return Cache::remember($key, 86400, static function () use ($ip): ?string {
                $host = gethostbyaddr($ip);
                return $host !== false && $host !== $ip ? $host : null;
            });
        } catch (\Throwable) {
            return null;
        }
    }

    private function safeHeaders(Request $request): array
    {
        $sensitive = ['authorization', 'cookie', 'x-api-key', 'x-csrf-token', 'x-xsrf-token'];

        return collect($request->headers->all())
            ->map(function (array $values, string $name) use ($sensitive): mixed {
                if (in_array(strtolower($name), $sensitive, true)) {
                    return '[redacted]';
                }

                return count($values) === 1 ? $values[0] : $values;
            })
            ->all();
    }
}
