<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->string('type', 100);
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('host')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('path')->nullable();
            $table->text('full_url')->nullable();
            $table->text('referer')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('device', 100)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_tablet')->default(false);
            $table->boolean('is_desktop')->default(true);
            $table->boolean('is_bot')->default(false);
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('country_iso', 10)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 100)->nullable();
            $table->longText('headers')->nullable();
            $table->text('query')->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('notifications');
    }
};
