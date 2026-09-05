<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PackageCategoryController;
use App\Models\PackageCategory;
use App\Services\TranslationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AdminPackageCategoryCycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->createTables();
        Storage::fake('public');

        $translationService = Mockery::mock(TranslationService::class);
        $translationService->shouldReceive('translateFields')
            ->andReturnUsing(function (array $data, array $fields): array {
                foreach ($fields as $field) {
                    if (isset($data[$field]) && $data[$field] !== '') {
                        $data[$field] = ['ar' => $data[$field], 'en' => $data[$field]];
                    }
                }

                return $data;
            });

        $this->app->instance(TranslationService::class, $translationService);
    }

    public function test_create_update_and_delete_cycle_preserves_related_content(): void
    {
        $controller = $this->app->make(PackageCategoryController::class);

        $storeResponse = $controller->store(Request::create('/admin/package-categories', 'POST', [
            'slug' => 'egypt-tours',
            'name' => 'رحلات مصر',
            'description' => 'باقات سياحية',
            'category_type' => 'travel_package',
            'min_days' => 3,
            'max_days' => 8,
            'price_from' => 250,
            'is_active' => 1,
            'is_featured' => 0,
            'sort_order' => 2,
        ]));

        $this->assertSame(route('admin.package-categories.index'), $storeResponse->getTargetUrl());

        $category = PackageCategory::firstOrFail();
        $this->assertSame('travel_package', $category->category_type);
        $this->assertSame('رحلات مصر', $category->name['ar']);

        Storage::disk('public')->put('images/package-categories/old.webp', 'image');
        $category->update(['image' => 'images/package-categories/old.webp']);

        $updateResponse = $controller->update(Request::create('/admin/package-categories/'.$category->id, 'PUT', [
            'slug' => 'egypt-luxury-tours',
            'name' => 'رحلات مصر الفاخرة',
            'category_type' => 'deal',
            'min_days' => 4,
            'max_days' => 10,
            'remove_image' => 1,
            'is_active' => 0,
            'is_featured' => 1,
            'sort_order' => 4,
        ]), $category);

        $this->assertSame(route('admin.package-categories.index'), $updateResponse->getTargetUrl());

        $category->refresh();
        $this->assertSame('deal', $category->category_type);
        $this->assertFalse($category->is_active);
        $this->assertTrue($category->is_featured);
        $this->assertNull($category->image);
        Storage::disk('public')->assertMissing('images/package-categories/old.webp');

        $child = PackageCategory::create([
            'parent_id' => $category->id,
            'slug' => 'child',
            'name' => ['ar' => 'فرعي'],
            'category_type' => 'day_tour',
        ]);
        $packageId = DB::table('packages')->insertGetId(['category_id' => $category->id]);
        $faqId = DB::table('faqs')->insertGetId(['category_id' => $category->id]);
        DB::table('translations')->insert([
            'translatable_type' => $category->getMorphClass(),
            'translatable_id' => $category->id,
            'locale' => 'ar',
            'field' => 'name',
            'value' => 'ترجمة',
        ]);

        $deleteResponse = $controller->destroy($category);

        $this->assertSame(route('admin.package-categories.index'), $deleteResponse->getTargetUrl());
        $this->assertDatabaseMissing('package_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('package_categories', ['id' => $child->id, 'parent_id' => null]);
        $this->assertDatabaseHas('packages', ['id' => $packageId, 'category_id' => null]);
        $this->assertDatabaseHas('faqs', ['id' => $faqId, 'category_id' => null]);
        $this->assertDatabaseMissing('translations', ['translatable_id' => $category->id]);
    }

    public function test_a_category_cannot_use_one_of_its_descendants_as_parent(): void
    {
        $parent = PackageCategory::create([
            'slug' => 'parent',
            'name' => ['ar' => 'رئيسي'],
            'category_type' => 'travel_package',
        ]);
        $child = PackageCategory::create([
            'parent_id' => $parent->id,
            'slug' => 'child',
            'name' => ['ar' => 'فرعي'],
            'category_type' => 'day_tour',
        ]);

        $this->expectException(ValidationException::class);

        $this->app->make(PackageCategoryController::class)->update(
            Request::create('/admin/package-categories/'.$parent->id, 'PUT', [
                'parent_id' => $child->id,
                'slug' => 'parent',
                'name' => 'رئيسي',
                'category_type' => 'travel_package',
            ]),
            $parent,
        );
    }

    private function createTables(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('package_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('slug', 190)->unique();
            $table->text('name');
            $table->text('description')->nullable();
            $table->string('category_type')->default('travel_package');
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->integer('min_days')->nullable();
            $table->integer('max_days')->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale');
            $table->string('field');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });
    }
}
