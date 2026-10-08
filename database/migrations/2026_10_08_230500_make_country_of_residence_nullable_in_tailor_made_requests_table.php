<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tailor_made_requests', function (Blueprint $table) {
            $table->string('country_of_residence', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tailor_made_requests', function (Blueprint $table) {
            $table->string('country_of_residence', 120)->nullable(false)->change();
        });
    }
};

