<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | packages
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                if (!Schema::hasColumn('packages', 'destination_id')) {
                    $table->unsignedBigInteger('destination_id')->nullable()->after('category_id');
                }

                if (!Schema::hasColumn('packages', 'duration_text')) {
                    $table->string('duration_text')->nullable()->after('duration_nights');
                }

                if (!Schema::hasColumn('packages', 'route_text')) {
                    $table->string('route_text')->nullable()->after('duration_text');
                }

                if (!Schema::hasColumn('packages', 'featured_image')) {
                    $table->string('featured_image')->nullable()->after('video_url');
                }

                if (!Schema::hasColumn('packages', 'pricing_information')) {
                    $table->longText('pricing_information')->nullable()->after('terms_conditions');
                }

                if (!Schema::hasColumn('packages', 'children_policy')) {
                    $table->longText('children_policy')->nullable()->after('pricing_information');
                }

                if (!Schema::hasColumn('packages', 'pickup_policy')) {
                    $table->longText('pickup_policy')->nullable()->after('children_policy');
                }

                if (!Schema::hasColumn('packages', 'base_price')) {
                    $table->decimal('base_price', 12, 2)->nullable()->after('start_from_price');
                }

                if (!Schema::hasColumn('packages', 'gallery_images')) {
                    $table->json('gallery_images')->nullable()->after('featured_image');
                }

                if (!Schema::hasColumn('packages', 'faq_json')) {
                    $table->json('faq_json')->nullable()->after('gallery_images');
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | package_facilities
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('package_facilities')) {
            Schema::create('package_facilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('title');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('package_facilities', function (Blueprint $table) {
                if (!Schema::hasColumn('package_facilities', 'package_id')) {
                    $table->unsignedBigInteger('package_id')->nullable();
                }

                if (!Schema::hasColumn('package_facilities', 'title')) {
                    $table->string('title')->nullable();
                }

                if (!Schema::hasColumn('package_facilities', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }

                if (!Schema::hasColumn('package_facilities', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | itineraries
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('itineraries')) {
            Schema::create('itineraries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();

                $table->string('duration')->nullable();
                $table->integer('day_number')->nullable();

                $table->json('title')->nullable();
                $table->json('description')->nullable();

                $table->boolean('meals_breakfast')->default(false);
                $table->boolean('meals_lunch')->default(false);
                $table->boolean('meals_dinner')->default(false);

                $table->json('overnight_location')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();

                $table->timestamps();
            });
        } else {
            Schema::table('itineraries', function (Blueprint $table) {
                if (!Schema::hasColumn('itineraries', 'duration')) {
                    $table->string('duration')->nullable()->after('package_id');
                }

                if (!Schema::hasColumn('itineraries', 'package_id')) {
                    $table->unsignedBigInteger('package_id')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'day_number')) {
                    $table->integer('day_number')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'title')) {
                    $table->json('title')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'description')) {
                    $table->json('description')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'meals_breakfast')) {
                    $table->boolean('meals_breakfast')->default(false);
                }

                if (!Schema::hasColumn('itineraries', 'meals_lunch')) {
                    $table->boolean('meals_lunch')->default(false);
                }

                if (!Schema::hasColumn('itineraries', 'meals_dinner')) {
                    $table->boolean('meals_dinner')->default(false);
                }

                if (!Schema::hasColumn('itineraries', 'overnight_location')) {
                    $table->json('overnight_location')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'start_time')) {
                    $table->time('start_time')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'end_time')) {
                    $table->time('end_time')->nullable();
                }

                if (!Schema::hasColumn('itineraries', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | package_inclusions
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('package_inclusions')) {
            Schema::create('package_inclusions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();

                $table->enum('type', ['included', 'excluded'])->default('included');
                $table->enum('item_type', ['included', 'excluded'])->default('included');

                $table->string('title')->nullable();
                $table->json('content')->nullable();

                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('package_inclusions', function (Blueprint $table) {
                if (!Schema::hasColumn('package_inclusions', 'package_id')) {
                    $table->unsignedBigInteger('package_id')->nullable();
                }

                if (!Schema::hasColumn('package_inclusions', 'type')) {
                    $table->enum('type', ['included', 'excluded'])->default('included')->after('package_id');
                }

                if (!Schema::hasColumn('package_inclusions', 'item_type')) {
                    $table->enum('item_type', ['included', 'excluded'])->default('included')->after('type');
                }

                if (!Schema::hasColumn('package_inclusions', 'title')) {
                    $table->string('title')->nullable()->after('item_type');
                }

                if (!Schema::hasColumn('package_inclusions', 'content')) {
                    $table->json('content')->nullable()->after('title');
                }

                if (!Schema::hasColumn('package_inclusions', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }

                if (!Schema::hasColumn('package_inclusions', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | package_prices
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('package_prices')) {
            Schema::create('package_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();

                $table->json('label')->nullable();
                $table->json('season_name')->nullable();

                $table->enum('price_type', ['from', 'fixed'])->default('from');
                $table->enum('room_type', ['single', 'double', 'triple', 'suite'])->nullable();

                $table->integer('pax_min')->nullable();
                $table->integer('pax_max')->nullable();
                $table->integer('group_size_min')->nullable();
                $table->integer('group_size_max')->nullable();

                $table->decimal('amount', 12, 2)->nullable();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();

                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();

                $table->json('notes')->nullable();

                $table->timestamps();
            });
        } else {
            Schema::table('package_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('package_prices', 'package_id')) {
                    $table->unsignedBigInteger('package_id')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'label')) {
                    $table->json('label')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'season_name')) {
                    $table->json('season_name')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'price_type')) {
                    $table->enum('price_type', ['from', 'fixed'])->default('from');
                }

                if (!Schema::hasColumn('package_prices', 'room_type')) {
                    $table->enum('room_type', ['single', 'double', 'triple', 'suite'])->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'pax_min')) {
                    $table->integer('pax_min')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'pax_max')) {
                    $table->integer('pax_max')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'group_size_min')) {
                    $table->integer('group_size_min')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'group_size_max')) {
                    $table->integer('group_size_max')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'amount')) {
                    $table->decimal('amount', 12, 2)->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'currency_id')) {
                    $table->unsignedBigInteger('currency_id')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'valid_from')) {
                    $table->date('valid_from')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'valid_to')) {
                    $table->date('valid_to')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'notes')) {
                    $table->json('notes')->nullable();
                }

                if (!Schema::hasColumn('package_prices', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | package_images
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('package_images')) {
            Schema::create('package_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();

                $table->string('image');
                $table->boolean('is_featured')->default(false);
                $table->integer('sort_order')->default(0);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('package_images')) {
            Schema::dropIfExists('package_images');
        }

        if (Schema::hasTable('package_facilities')) {
            Schema::dropIfExists('package_facilities');
        }

        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                foreach (
                    [
                        'destination_id',
                        'duration_text',
                        'route_text',
                        'featured_image',
                        'pricing_information',
                        'children_policy',
                        'pickup_policy',
                        'base_price',
                        'gallery_images',
                        'faq_json',
                    ] as $column
                ) {
                    if (Schema::hasColumn('packages', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
