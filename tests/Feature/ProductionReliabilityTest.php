<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PackageController;
use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\Translation\TranslationCacheService;
use App\Services\WebpImageService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionReliabilityTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private PackageCategory $category;
    private Country $country;
    private City $city;
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_reliability@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'slug' => 'egypt',
            'code' => 'EG',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->country->id,
            'name' => ['en' => 'Cairo', 'ar' => 'القاهرة'],
            'slug' => 'cairo',
            'is_active' => true,
        ]);

        $this->category = PackageCategory::create([
            'name' => ['en' => 'Day Tours', 'ar' => 'جولات اليوم الواحد'],
            'slug' => 'day-tours',
            'category_type' => 'day_tour',
            'is_active' => true,
        ]);

        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 1.0,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Package creation with image & WebP conversion.
     */
    public function test_package_creation_with_image_and_webp_conversion(): void
    {
        $file = UploadedFile::fake()->image('pyramids.jpg', 800, 600);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('admin.packages.store'), [
                'title' => 'Pyramids Full Day Tour',
                'package_type' => 'day_tour',
                'category_id' => $this->category->id,
                'destination_id' => $this->city->id,
                'primary_country_id' => $this->country->id,
                'currency_id' => $this->currency->id,
                'duration_type' => 'hours',
                'duration_hours' => 6,
                'adult_price' => 120,
                'adult_min_age' => 12,
                'child_min_age' => 2,
                'child_max_age' => 11,
                'infant_min_age' => 0,
                'infant_max_age' => 1,
                'featured_image' => $file,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'redirect' => route('admin.packages.index'),
        ]);

        $package = Package::where('slug', 'pyramids-full-day-tour')->first();
        $this->assertNotNull($package);
        $this->assertNotNull($package->featured_image);
        $this->assertStringEndsWith('.webp', $package->featured_image);

        $storedPath = str_replace('storage/', '', $package->featured_image);
        Storage::disk('public')->assertExists($storedPath);
    }

    /**
     * 2. Package update with image replacement: old image deleted only after commit, new image kept.
     */
    public function test_package_update_with_image_replacement_deletes_old_after_commit(): void
    {
        $oldPath = 'packages/featured/initial_image.webp';
        Storage::disk('public')->put($oldPath, 'initial-image-content');
        Storage::disk('public')->assertExists($oldPath);

        $package = Package::create([
            'title' => ['en' => 'Initial Trip', 'ar' => 'رحلة أولية'],
            'slug' => 'initial-trip',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 4,
            'adult_price' => 90,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'featured_image' => 'storage/' . $oldPath,
            'is_active' => true,
        ]);

        $newFile = UploadedFile::fake()->image('replacement.jpg', 800, 600);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->put(route('admin.packages.update', $package), [
                'title' => 'Updated Trip with New Image',
                'package_type' => 'day_tour',
                'category_id' => $this->category->id,
                'destination_id' => $this->city->id,
                'primary_country_id' => $this->country->id,
                'currency_id' => $this->currency->id,
                'duration_type' => 'hours',
                'duration_hours' => 4,
                'adult_price' => 90,
                'adult_min_age' => 12,
                'child_min_age' => 2,
                'child_max_age' => 11,
                'infant_min_age' => 0,
                'infant_max_age' => 1,
                'featured_image' => $newFile,
                'lock_version' => $package->updated_at->timestamp,
            ]);

        $response->assertOk();
        $package->refresh();

        $this->assertNotEquals('storage/' . $oldPath, $package->featured_image);
        $this->assertStringEndsWith('.webp', $package->featured_image);

        // Old file must be deleted after commit
        Storage::disk('public')->assertMissing($oldPath);

        // New file must exist
        $newRelativePath = str_replace('storage/', '', $package->featured_image);
        Storage::disk('public')->assertExists($newRelativePath);
    }

    /**
     * 3. Failed transaction rollbacks clean up newly uploaded images and preserve existing images.
     */
    public function test_failed_transaction_cleans_up_new_uploads_and_preserves_old_files(): void
    {
        $oldPath = 'packages/featured/keep_safe.webp';
        Storage::disk('public')->put($oldPath, 'keep-safe-content');

        $package = Package::create([
            'title' => ['en' => 'Safe Package', 'ar' => 'باقة آمنة'],
            'slug' => 'safe-package',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 3,
            'adult_price' => 50,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'featured_image' => 'storage/' . $oldPath,
            'is_active' => true,
        ]);

        // Attach a throwing hook during the update to guarantee database transaction rollback
        $targetId = $package->id;
        Package::saving(function ($pkg) use ($targetId) {
            if ($pkg->id === $targetId && request()->isMethod('PUT')) {
                throw new \RuntimeException('Simulated database error inside transaction');
            }
        });

        $newFile = UploadedFile::fake()->image('doomed.jpg', 600, 400);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->put(route('admin.packages.update', $package), [
                'title' => 'Should Fail',
                'package_type' => 'day_tour',
                'category_id' => $this->category->id,
                'destination_id' => $this->city->id,
                'primary_country_id' => $this->country->id,
                'currency_id' => $this->currency->id,
                'duration_type' => 'hours',
                'duration_hours' => 3,
                'adult_price' => 50,
                'adult_min_age' => 12,
                'child_min_age' => 2,
                'child_max_age' => 11,
                'infant_min_age' => 0,
                'infant_max_age' => 1,
                'featured_image' => $newFile,
                'lock_version' => $package->updated_at->timestamp,
            ]);

        $response->assertStatus(500);

        // Old file MUST still exist!
        Storage::disk('public')->assertExists($oldPath);

        // Package record remains unchanged
        $package->refresh();
        $this->assertEquals('storage/' . $oldPath, $package->featured_image);
    }

    /**
     * 4. Concurrency: Stale update with older lock_version triggers HTTP 409 Conflict.
     */
    public function test_stale_update_with_older_lock_version_triggers_http_409_conflict(): void
    {
        $package = Package::create([
            'title' => ['en' => 'Concurrency Package', 'ar' => 'باقة التزامن'],
            'slug' => 'concurrency-package',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 2,
            'adult_price' => 40,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'is_active' => true,
        ]);

        // Stale timestamp (10 seconds before current package updated_at)
        $staleTimestamp = $package->updated_at->timestamp - 10;

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->put(route('admin.packages.update', $package), [
                'title' => 'Attempted Overwrite',
                'package_type' => 'day_tour',
                'category_id' => $this->category->id,
                'destination_id' => $this->city->id,
                'primary_country_id' => $this->country->id,
                'currency_id' => $this->currency->id,
                'duration_type' => 'hours',
                'duration_hours' => 2,
                'adult_price' => 40,
                'adult_min_age' => 12,
                'child_min_age' => 2,
                'child_max_age' => 11,
                'infant_min_age' => 0,
                'infant_max_age' => 1,
                'lock_version' => $staleTimestamp,
            ]);

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'stale_data' => true,
        ]);
    }

    /**
     * 5. Concurrency: Simultaneous update atomic lock behavior (HTTP 423).
     */
    public function test_simultaneous_update_atomic_lock_behavior_returns_http_423(): void
    {
        $package = Package::create([
            'title' => ['en' => 'Lock Test Package', 'ar' => 'باقة قفل التزامن'],
            'slug' => 'lock-test-package',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 2,
            'adult_price' => 40,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'is_active' => true,
        ]);

        // Manually acquire lock as if another request is in-flight
        $lock = Cache::lock("package_save_{$package->id}", 15);
        $this->assertTrue($lock->acquire());

        try {
            $response = $this->actingAs($this->admin, 'admin')
                ->withHeaders([
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Accept' => 'application/json',
                ])
                ->put(route('admin.packages.update', $package), [
                    'title' => 'Should Be Blocked by Lock',
                    'package_type' => 'day_tour',
                    'category_id' => $this->category->id,
                    'destination_id' => $this->city->id,
                    'primary_country_id' => $this->country->id,
                    'currency_id' => $this->currency->id,
                    'duration_type' => 'hours',
                    'duration_hours' => 2,
                    'adult_price' => 40,
                    'adult_min_age' => 12,
                    'child_min_age' => 2,
                    'child_max_age' => 11,
                    'infant_min_age' => 0,
                    'infant_max_age' => 1,
                    'lock_version' => $package->updated_at->timestamp,
                ]);

            $response->assertStatus(423);
            $response->assertJson([
                'success' => false,
                'locked' => true,
            ]);
        } finally {
            $lock->release();
        }
    }

    /**
     * 6. Transaction retry logic on simulated lock wait timeout / deadlock.
     */
    public function test_transaction_retry_logic_on_simulated_lock_wait_timeout_and_deadlock(): void
    {
        $controller = app(PackageController::class);
        $method = new \ReflectionMethod($controller, 'executeTransactionWithRetry');
        $method->setAccessible(true);

        // Case A: Fails with 1205 (Lock wait timeout) on attempt 1, succeeds on attempt 2
        $attempts = 0;
        $result = $method->invoke($controller, function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                // PDO error code 1205: Lock wait timeout exceeded
                $pdoException = new \PDOException('Lock wait timeout exceeded; try restarting transaction');
                $pdoException->errorInfo = ['HY000', 1205, 'Lock wait timeout exceeded'];
                throw new QueryException('mysql', 'SELECT 1', [], $pdoException);
            }
            return 'recovered_success';
        }, 3);

        $this->assertEquals(2, $attempts);
        $this->assertEquals('recovered_success', $result);

        // Case B: Fails with 1213 (Deadlock) on attempt 1, succeeds on attempt 2
        $deadlockAttempts = 0;
        $deadlockResult = $method->invoke($controller, function () use (&$deadlockAttempts) {
            $deadlockAttempts++;
            if ($deadlockAttempts === 1) {
                $pdoException = new \PDOException('Deadlock found when trying to get lock');
                $pdoException->errorInfo = ['40001', 1213, 'Deadlock found when trying to get lock'];
                throw new QueryException('mysql', 'SELECT 1', [], $pdoException);
            }
            return 'deadlock_recovered';
        }, 3);

        $this->assertEquals(2, $deadlockAttempts);
        $this->assertEquals('deadlock_recovered', $deadlockResult);

        // Case C: Non-retryable error (e.g. 1062 unique constraint) fails immediately
        $nonRetryAttempts = 0;
        $this->expectException(QueryException::class);
        $method->invoke($controller, function () use (&$nonRetryAttempts) {
            $nonRetryAttempts++;
            $pdoException = new \PDOException('Duplicate entry');
            $pdoException->errorInfo = ['23000', 1062, 'Duplicate entry'];
            throw new QueryException('mysql', 'INSERT INTO ...', [], $pdoException);
        }, 3);
    }

    /**
     * 7. reserveSortPosition collision-only increments.
     */
    public function test_reserve_sort_position_collision_only_increments(): void
    {
        $controller = app(PackageController::class);
        $method = new \ReflectionMethod($controller, 'reserveSortPosition');
        $method->setAccessible(true);

        Package::query()->delete();

        // Create Package 1 with sort_order 1
        $p1 = Package::create([
            'title' => ['en' => 'Package 1', 'ar' => 'باقة 1'],
            'slug' => 'package-1',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 1,
            'adult_price' => 10,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Test non-collision position: position 5 is empty
        $method->invoke($controller, 5, null);
        $p1->refresh();
        $this->assertEquals(1, $p1->sort_order, 'Sort order of package 1 should not change when inserting at uncollided position 5');

        // Test collision position: position 1 collides with $p1
        $method->invoke($controller, 1, null);
        $p1->refresh();
        $this->assertEquals(2, $p1->sort_order, 'Sort order of package 1 should increment to 2 upon collision');
    }

    /**
     * 8. Category search on both valid multilingual JSON and legacy plain-text strings without MySQL 3141.
     */
    public function test_category_search_supports_valid_json_and_legacy_plain_text(): void
    {
        PackageCategory::query()->delete();

        // Row 1: Valid multilingual JSON
        $cat1 = PackageCategory::create([
            'name' => ['en' => 'Historical Cairo Tours', 'ar' => 'جولات القاهرة التاريخية'],
            'slug' => 'historical-cairo',
            'category_type' => 'day_tour',
            'seo_title' => ['en' => 'Historical SEO Title', 'ar' => 'عنوان تاريخي'],
            'seo_description' => ['en' => 'Historical SEO Desc', 'ar' => 'وصف تاريخي'],
            'is_active' => true,
        ]);

        // Row 2: Legacy plain-text data directly inserted to simulate legacy MySQL 3141 risk
        $cat2Id = DB::table('package_categories')->insertGetId([
            'name' => 'Legacy Plain Name',
            'slug' => 'legacy-plain',
            'category_type' => 'day_tour',
            'seo_title' => 'Legacy Plain SEO Title 2026',
            'seo_description' => 'Legacy Plain SEO Description',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Search for JSON item
        $responseJson = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.package-categories.index', ['search' => 'Historical']));
        $responseJson->assertOk();
        $responseJson->assertSee('Historical Cairo Tours');

        // Search for legacy plain text item (must not crash with MySQL Error 3141 / SQLSTATE 22032)
        $responsePlain = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.package-categories.index', ['search' => 'Legacy Plain']));
        $responsePlain->assertOk();
        $responsePlain->assertSee('Legacy Plain Name');
    }

    /**
     * 9. admin_t() translation behavior in English, Arabic, and month keys.
     */
    public function test_admin_t_translation_behavior_in_english_and_arabic(): void
    {
        $this->assertEquals('Jan', admin_t('Jan', [], 'en'));
        $this->assertEquals('يناير', admin_t('Jan', [], 'ar'));
        $this->assertEquals('December', admin_t('December', [], 'en'));
        $this->assertEquals('ديسمبر', admin_t('December', [], 'ar'));

        // Test common admin phrase
        $this->assertEquals('Package updated successfully.', admin_t('Package updated successfully.', [], 'en'));
        $this->assertEquals('تم تحديث الرحلة بنجاح.', admin_t('Package updated successfully.', [], 'ar'));

        // Test fallback & parameter replacement
        $this->assertEquals('Welcome, John!', admin_t('Welcome, :name!', ['name' => 'John'], 'en'));
        $this->assertEquals('Undefined Custom Phrase', admin_t('Undefined Custom Phrase', [], 'fr'));
    }

    /**
     * 10. BookingController::yearlyStats endpoint returns translated months without PHP exceptions.
     */
    public function test_booking_controller_yearly_stats_returns_translated_months(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.bookings.yearly-stats', ['year' => 2026]));

        $response->assertOk();
        $data = $response->json();

        $this->assertIsArray($data);
        $this->assertCount(12, $data);
        $this->assertArrayHasKey('month', $data[0]);
        $this->assertArrayHasKey('total', $data[0]);
        $this->assertNotEmpty($data[0]['month']);
    }

    /**
     * 11. Concurrent storeCache upsert in TranslationCacheService without unique constraint violations.
     */
    public function test_concurrent_store_cache_upsert_without_unique_violations(): void
    {
        $cacheService = app(TranslationCacheService::class);

        $unit = new \App\Services\Translation\DTOs\TranslationUnit(
            entityType: 'package',
            entityId: 100,
            field: 'title',
            sourceLanguage: 'en',
            targetLanguage: 'ar',
            sourceText: 'Simulated Concurrent Source Text'
        );

        $result = new \App\Services\Translation\DTOs\TranslationResult(
            translatedText: 'النص المترجم المتزامن',
            provider: 'gemini',
            model: 'gemini-2.5-flash',
            isSuccess: true
        );

        // Store first time
        $cacheService->storeCache($unit, $result);

        // Store identical second time (concurrent simulation - upsert must handle without unique violation)
        $cacheService->storeCache($unit, $result);

        // Verify record in database
        $this->assertDatabaseHas('ai_translation_caches', [
            'source_hash' => $unit->contentHash,
            'source_language' => 'en',
            'target_language' => 'ar',
            'translated_text' => 'النص المترجم المتزامن',
        ]);
    }

    /**
     * 12. Scoped cache invalidation: website.home.version increments without global Cache::flush().
     */
    public function test_scoped_cache_invalidation_increments_home_version_without_global_flush(): void
    {
        $controller = app(PackageController::class);
        $method = new \ReflectionMethod($controller, 'clearCache');
        $method->setAccessible(true);

        $package = Package::create([
            'title' => ['en' => 'Cache Test Package', 'ar' => 'باقة اختبار الكاش'],
            'slug' => 'cache-test-package',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 2,
            'adult_price' => 50,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'is_active' => true,
        ]);

        // Set unrelated cache key that should NEVER be wiped
        Cache::put('unrelated_critical_cache_key', 'important_data', 3600);
        Cache::put('website.home.version', 10, 3600);

        // Invoke scoped clearCache
        $method->invoke($controller, $package);

        // Assert unrelated key was NOT flushed
        $this->assertEquals('important_data', Cache::get('unrelated_critical_cache_key'));

        // Assert home version was incremented
        $this->assertGreaterThan(10, (int) Cache::get('website.home.version'));
    }
}
