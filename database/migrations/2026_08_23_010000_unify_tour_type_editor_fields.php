<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                if (!Schema::hasColumn('packages', 'what_to_bring')) {
                    $table->json('what_to_bring')->nullable();
                }
                if (!Schema::hasColumn('packages', 'on_tour_languages')) {
                    $table->json('on_tour_languages')->nullable();
                }
                if (!Schema::hasColumn('packages', 'operating_days')) {
                    $table->json('operating_days')->nullable();
                }
                if (!Schema::hasColumn('packages', 'departure_times')) {
                    $table->json('departure_times')->nullable();
                }
                if (!Schema::hasColumn('packages', 'tour_timezone')) {
                    $table->string('tour_timezone', 100)->nullable();
                }
                if (!Schema::hasColumn('packages', 'default_seat_capacity')) {
                    $table->unsignedInteger('default_seat_capacity')->nullable();
                }
                if (!Schema::hasColumn('packages', 'brochure_path')) {
                    $table->string('brochure_path')->nullable();
                }
                if (!Schema::hasColumn('packages', 'promotional_videos')) {
                    $table->json('promotional_videos')->nullable();
                }
                if (!Schema::hasColumn('packages', 'deposit_policy')) {
                    $table->string('deposit_policy', 30)->nullable();
                }
                if (!Schema::hasColumn('packages', 'deposit_type')) {
                    $table->string('deposit_type', 30)->nullable();
                }
                if (!Schema::hasColumn('packages', 'deposit_value')) {
                    $table->decimal('deposit_value', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('packages', 'allowed_payment_method_ids')) {
                    $table->json('allowed_payment_method_ids')->nullable();
                }
                if (!Schema::hasColumn('packages', 'focus_keyword')) {
                    $table->string('focus_keyword')->nullable();
                }
                if (!Schema::hasColumn('packages', 'meta_keywords')) {
                    $table->json('meta_keywords')->nullable();
                }
                if (!Schema::hasColumn('packages', 'og_title')) {
                    $table->string('og_title')->nullable();
                }
                if (!Schema::hasColumn('packages', 'og_description')) {
                    $table->text('og_description')->nullable();
                }
                if (!Schema::hasColumn('packages', 'og_image_path')) {
                    $table->string('og_image_path')->nullable();
                }
                if (!Schema::hasColumn('packages', 'twitter_card')) {
                    $table->string('twitter_card', 40)->nullable();
                }
                if (!Schema::hasColumn('packages', 'twitter_title')) {
                    $table->string('twitter_title')->nullable();
                }
                if (!Schema::hasColumn('packages', 'twitter_description')) {
                    $table->text('twitter_description')->nullable();
                }
                if (!Schema::hasColumn('packages', 'robots_index')) {
                    $table->boolean('robots_index')->default(true);
                }
                if (!Schema::hasColumn('packages', 'robots_follow')) {
                    $table->boolean('robots_follow')->default(true);
                }
                if (!Schema::hasColumn('packages', 'itinerary_mode')) {
                    $table->string('itinerary_mode', 20)->nullable();
                }
            });
        }

        if (Schema::hasTable('itineraries')) {
            Schema::table('itineraries', function (Blueprint $table) {
                if (!Schema::hasColumn('itineraries', 'accommodation')) {
                    $table->json('accommodation')->nullable();
                }
                if (!Schema::hasColumn('itineraries', 'transport_notes')) {
                    $table->json('transport_notes')->nullable();
                }
                if (!Schema::hasColumn('itineraries', 'activities')) {
                    $table->json('activities')->nullable();
                }
            });
        }

        if (!Schema::hasTable('tour_package_details')) {
            Schema::create('tour_package_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->unique()->constrained('packages')->cascadeOnDelete();
                $table->string('accommodation_standard')->nullable();
                $table->json('meals_included')->nullable();
                $table->boolean('flexible_itinerary')->default(false);
                $table->text('additional_notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('package_addons')) {
            Schema::create('package_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->string('price_unit', 80)->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->index(['package_id', 'is_active'], 'pkg_addon_package_active_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('package_addons');
        Schema::dropIfExists('tour_package_details');

        if (Schema::hasTable('itineraries')) {
            $itineraryColumns = ['accommodation', 'transport_notes', 'activities'];
            $existingItineraryColumns = array_values(array_filter($itineraryColumns, fn ($column) => Schema::hasColumn('itineraries', $column)));
            if ($existingItineraryColumns) {
                Schema::table('itineraries', function (Blueprint $table) use ($existingItineraryColumns) {
                    $table->dropColumn($existingItineraryColumns);
                });
            }
        }

        if (Schema::hasTable('packages')) {
            $columns = [
                'what_to_bring', 'on_tour_languages', 'operating_days', 'departure_times',
                'tour_timezone', 'default_seat_capacity', 'brochure_path', 'promotional_videos',
                'deposit_policy', 'deposit_type', 'deposit_value', 'allowed_payment_method_ids',
                'focus_keyword', 'meta_keywords', 'og_title', 'og_description', 'og_image_path',
                'twitter_card', 'twitter_title', 'twitter_description', 'robots_index', 'robots_follow',
                'itinerary_mode',
            ];
            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('packages', $column)));
            if ($existing) {
                Schema::table('packages', function (Blueprint $table) use ($existing) {
                    $table->dropColumn($existing);
                });
            }
        }
    }
};
