<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nile_cruise_details')) {
            Schema::table('nile_cruise_details', function (Blueprint $table) {
                if (!Schema::hasColumn('nile_cruise_details', 'operating_days')) {
                    $table->json('operating_days')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'promotional_videos')) {
                    $table->json('promotional_videos')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'deposit_policy')) {
                    $table->string('deposit_policy')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'deposit_type')) {
                    $table->string('deposit_type')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'deposit_value')) {
                    $table->decimal('deposit_value', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'allowed_payment_method_ids')) {
                    $table->json('allowed_payment_method_ids')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'focus_keyword')) {
                    $table->string('focus_keyword')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'meta_keywords')) {
                    $table->json('meta_keywords')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'og_title')) {
                    $table->string('og_title')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'og_description')) {
                    $table->text('og_description')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'social_image_path')) {
                    $table->string('social_image_path')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'twitter_card')) {
                    $table->string('twitter_card')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'twitter_title')) {
                    $table->string('twitter_title')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'twitter_description')) {
                    $table->text('twitter_description')->nullable();
                }
                if (!Schema::hasColumn('nile_cruise_details', 'robots_index')) {
                    $table->boolean('robots_index')->default(true);
                }
                if (!Schema::hasColumn('nile_cruise_details', 'robots_follow')) {
                    $table->boolean('robots_follow')->default(true);
                }
            });
        }

        if (!Schema::hasTable('nile_cruise_addons')) {
            Schema::create('nile_cruise_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['package_id', 'is_active', 'sort_order'], 'idx_nc_addon_package_active_sort');
            });
        }
    }

    public function down(): void
    {
        // Compatibility migration: deliberately non-destructive on rollback.
        // Some production installations may already have part of this schema
        // from an earlier deployment/manual hotfix, so ownership cannot be
        // determined safely enough to drop columns or tables here.
    }

};
