<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'infants')) {
                $table->unsignedInteger('infants')->default(0)->after('children');
            }
            if (! Schema::hasColumn('bookings', 'pickup_location')) {
                $table->string('pickup_location')->nullable()->after('infants');
            }
            if (! Schema::hasColumn('bookings', 'checkout_details')) {
                $table->json('checkout_details')->nullable()->after('special_requests');
            }
        });

        if (! Schema::hasTable('booking_travelers')) {
            Schema::create('booking_travelers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->string('traveler_type', 20);
                $table->string('title', 20);
                $table->string('first_name', 120);
                $table->string('last_name', 120);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['booking_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('booking_items')) {
            Schema::create('booking_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('pricing_source', 40);
                $table->unsignedBigInteger('source_id')->nullable();
                $table->foreignId('cabin_id')->nullable()->constrained('nile_cruise_cabins')->nullOnDelete();
                $table->string('option_label');
                $table->string('occupancy_type', 40)->nullable();
                $table->decimal('unit_price', 12, 2);
                $table->unsignedInteger('quantity')->default(1);
                $table->unsignedInteger('room_count')->default(1);
                $table->decimal('total_amount', 12, 2);
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['cabin_id', 'pricing_source']);
            });
        }

        if (Schema::hasTable('payment_methods')) {
            $paypal = DB::table('payment_methods')->where('code', 'paypal')->first();
            if ($paypal) {
                DB::table('payment_methods')->where('id', $paypal->id)->update([
                    'provider' => 'paypal',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('payment_methods')->insert([
                    'name' => 'PayPal',
                    'code' => 'paypal',
                    'provider' => 'paypal',
                    'image' => null,
                    'description' => 'PayPal Checkout',
                    'config' => null,
                    'currency_code' => Schema::hasColumn('payment_methods', 'currency_code') ? 'USD' : null,
                    'is_active' => false,
                    'is_default' => false,
                    'sort_order' => 110,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('booking_travelers');
        // Booking columns are retained to avoid deleting customer booking data.
    }
};
