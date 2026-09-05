<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && ! Schema::hasColumn('bookings', 'currency_code')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('currency_code', 10)->default('USD')->after('total_amount')->index();
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'gateway_intention_id')) {
                    $table->string('gateway_intention_id', 150)->nullable()->after('gateway_reference')->index();
                }

                if (! Schema::hasColumn('payments', 'gateway_transaction_id')) {
                    $table->string('gateway_transaction_id', 150)->nullable()->after('gateway_intention_id')->unique();
                }

                if (! Schema::hasColumn('payments', 'gateway_order_id')) {
                    $table->string('gateway_order_id', 150)->nullable()->after('gateway_transaction_id')->index();
                }

                if (! Schema::hasColumn('payments', 'checkout_url')) {
                    $table->text('checkout_url')->nullable()->after('gateway_order_id');
                }

                if (! Schema::hasColumn('payments', 'refunded_amount')) {
                    $table->decimal('refunded_amount', 12, 2)->default(0)->after('amount');
                }

                if (! Schema::hasColumn('payments', 'refunded_at')) {
                    $table->timestamp('refunded_at')->nullable()->after('paid_at');
                }

                if (! Schema::hasColumn('payments', 'last_reconciled_at')) {
                    $table->timestamp('last_reconciled_at')->nullable()->after('refunded_at');
                }

                if (! Schema::hasColumn('payments', 'reconciliation_attempts')) {
                    $table->unsignedInteger('reconciliation_attempts')->default(0)->after('last_reconciled_at');
                }

                if (! Schema::hasColumn('payments', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('gateway_payload');
                }
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE payments MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            }
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (! Schema::hasColumn('clients', 'passport_expiry')) {
                    $table->date('passport_expiry')->nullable()->after('passport_number');
                }

                if (! Schema::hasColumn('clients', 'total_bookings')) {
                    $table->unsignedInteger('total_bookings')->default(0);
                }

                if (! Schema::hasColumn('clients', 'total_spent')) {
                    $table->decimal('total_spent', 14, 2)->default(0);
                }

                if (! Schema::hasColumn('clients', 'last_activity')) {
                    $table->timestamp('last_activity')->nullable();
                }

                if (! Schema::hasColumn('clients', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        }

        if (Schema::hasTable('payment_methods') && ! Schema::hasColumn('payment_methods', 'currency_code')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->string('currency_code', 10)->default('USD')->after('config');
            });
        }

        if (! Schema::hasTable('payment_refunds')) {
            Schema::create('payment_refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained()->restrictOnDelete();
                $table->string('refund_reference', 150)->unique();
                $table->string('gateway_refund_id', 150)->nullable()->unique();
                $table->decimal('amount', 12, 2);
                $table->string('currency_code', 10);
                $table->string('status', 30)->default('pending');
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->json('gateway_payload')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamps();

                $table->index(['payment_id', 'status']);
            });
        }

        if (Schema::hasTable('payment_methods')) {
            $existing = DB::table('payment_methods')->where('code', 'paymob')->first();

            if ($existing) {
                DB::table('payment_methods')
                    ->where('code', 'paymob')
                    ->update([
                        'provider' => 'paymob',
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('payment_methods')->insert([
                    'name' => 'Paymob',
                    'code' => 'paymob',
                    'provider' => 'paymob',
                    'image' => null,
                    'description' => 'Paymob Intention API + Unified Checkout',
                    'config' => null,
                    'currency_code' => Schema::hasColumn('payment_methods', 'currency_code') ? 'EGP' : null,
                    'is_active' => false,
                    'is_default' => false,
                    'sort_order' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive.
        // Financial/audit columns and refund history are retained on rollback.
    }
};
