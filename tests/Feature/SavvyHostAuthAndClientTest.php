<?php

namespace Tests\Feature;

use App\Services\SavvyHostAuthService;
use App\Services\SavvyHostClient;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SavvyHostAuthAndClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.savvyhost.base_url' => 'https://api.savvyhost.net',
            'services.savvyhost.tenant' => 'etrotours',
            'services.savvyhost.email' => 'admin@etrotours.com',
            'services.savvyhost.password' => 'secret123',
            'services.savvyhost.token' => 'fallback-config-token',
        ]);

        Cache::flush();
    }

    /** @test */
    public function it_uses_valid_cached_token_without_calling_login_endpoint()
    {
        $tenant = 'etrotours';
        Cache::put("savvyhost:auth:token:{$tenant}", 'cached-valid-token', 3600);
        Cache::put("savvyhost:auth:expires_at:{$tenant}", now()->timestamp + 3600, 3600);

        Http::fake([
            'https://api.savvyhost.net/api/v1/dashboard/ai/templates*' => Http::response([
                'data' => [['id' => 1, 'name' => 'Cairo Tour']],
                'meta' => ['total' => 1, 'last_page' => 1],
            ], 200),
        ]);

        $client = app(SavvyHostClient::class);
        $response = $client->get('/api/v1/dashboard/ai/templates');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Cairo Tour', $response->json('data.0.name'));

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/api/v1/dashboard/login');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/dashboard/ai/templates') &&
                $request->hasHeader('Authorization', 'Bearer cached-valid-token');
        });
    }

    /** @test */
    public function it_auto_logins_when_cached_token_is_expired()
    {
        $tenant = 'etrotours';
        // Expired 10 minutes ago
        Cache::put("savvyhost:auth:token:{$tenant}", 'old-expired-token', 3600);
        Cache::put("savvyhost:auth:expires_at:{$tenant}", now()->timestamp - 600, 3600);

        Http::fake([
            'https://api.savvyhost.net/api/v1/dashboard/login' => Http::response([
                'success' => true,
                'data' => [
                    'token' => 'freshly-logged-in-token',
                    'expires_in' => 86400,
                ],
            ], 200),
            'https://api.savvyhost.net/api/v1/dashboard/ai/templates*' => Http::response([
                'data' => [['id' => 2, 'name' => 'Luxor Tour']],
                'meta' => ['total' => 1, 'last_page' => 1],
            ], 200),
        ]);

        $client = app(SavvyHostClient::class);
        $response = $client->get('/api/v1/dashboard/ai/templates');

        $this->assertEquals(200, $response->status());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/dashboard/login') &&
                $request['email'] === 'admin@etrotours.com';
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/dashboard/ai/templates') &&
                $request->hasHeader('Authorization', 'Bearer freshly-logged-in-token');
        });
    }

    /** @test */
    public function it_handles_401_runtime_expiration_with_auto_login_and_retry()
    {
        $tenant = 'etrotours';
        Cache::put("savvyhost:auth:token:{$tenant}", 'stale-token', 3600);
        Cache::put("savvyhost:auth:expires_at:{$tenant}", now()->timestamp + 1000, 3600);

        Http::fake([
            'https://api.savvyhost.net/api/v1/dashboard/login' => Http::response([
                'success' => true,
                'data' => [
                    'token' => 'new-retried-token',
                    'expires_in' => 3600,
                ],
            ], 200),
            'https://api.savvyhost.net/api/v1/dashboard/ai/templates*' => Http::sequence()
                ->push(['error' => 'Unauthenticated.'], 401)
                ->push(['data' => [['id' => 3, 'name' => 'Aswan Cruise']], 'meta' => ['total' => 1]], 200),
        ]);

        $client = app(SavvyHostClient::class);
        $response = $client->get('/api/v1/dashboard/ai/templates');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Aswan Cruise', $response->json('data.0.name'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/dashboard/login');
        });
    }

    /** @test */
    public function it_prevents_infinite_loop_on_double_401()
    {
        $tenant = 'etrotours';
        Cache::put("savvyhost:auth:token:{$tenant}", 'failing-token', 3600);
        Cache::put("savvyhost:auth:expires_at:{$tenant}", now()->timestamp + 1000, 3600);

        Http::fake([
            'https://api.savvyhost.net/api/v1/dashboard/login' => Http::response([
                'success' => true,
                'data' => ['token' => 'refreshed-failing-token', 'expires_in' => 3600],
            ], 200),
            'https://api.savvyhost.net/api/v1/dashboard/ai/templates*' => Http::response([
                'error' => 'Unauthenticated.',
            ], 401),
        ]);

        $client = app(SavvyHostClient::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SavvyHost authentication failed after token refresh.');

        $client->get('/api/v1/dashboard/ai/templates');
    }

    /** @test */
    public function it_throws_safe_error_on_login_failure()
    {
        Http::fake([
            'https://api.savvyhost.net/api/v1/dashboard/login' => Http::response([
                'message' => 'Invalid credentials.',
            ], 401),
        ]);

        $authService = app(SavvyHostAuthService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SavvyHost authentication failed. Please verify credentials.');

        $authService->login();
    }

    /** @test */
    public function it_considers_token_expired_within_safety_margin()
    {
        $authService = app(SavvyHostAuthService::class);
        $tenant = $authService->getTenant();

        // Expiring in 2 minutes (less than 5 min / 300s safety margin)
        Cache::put("savvyhost:auth:expires_at:{$tenant}", now()->timestamp + 120, 300);

        $this->assertTrue($authService->isTokenExpired());
    }
}
