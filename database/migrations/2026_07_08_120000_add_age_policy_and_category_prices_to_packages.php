<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'adult_price')) {
                $table->decimal('adult_price', 10, 2)->default(0)->after('compare_price');
            }

            if (!Schema::hasColumn('packages', 'child_price')) {
                $table->decimal('child_price', 10, 2)->default(0)->after('adult_price');
            }

            if (!Schema::hasColumn('packages', 'infant_price')) {
                $table->decimal('infant_price', 10, 2)->default(0)->after('child_price');
            }

            if (!Schema::hasColumn('packages', 'adult_min_age')) {
                $table->unsignedInteger('adult_min_age')->default(12)->after('infant_price');
            }

            if (!Schema::hasColumn('packages', 'child_min_age')) {
                $table->unsignedInteger('child_min_age')->default(2)->after('adult_min_age');
            }

            if (!Schema::hasColumn('packages', 'child_max_age')) {
                $table->unsignedInteger('child_max_age')->default(11)->after('child_min_age');
            }

            if (!Schema::hasColumn('packages', 'infant_min_age')) {
                $table->unsignedInteger('infant_min_age')->default(0)->after('child_max_age');
            }

            if (!Schema::hasColumn('packages', 'infant_max_age')) {
                $table->unsignedInteger('infant_max_age')->default(1)->after('infant_min_age');
            }

            if (!Schema::hasColumn('packages', 'price_from')) {
                $table->decimal('price_from', 10, 2)->default(0)->after('infant_max_age');
            }

            if (!Schema::hasColumn('packages', 'price_to')) {
                $table->decimal('price_to', 10, 2)->default(0)->after('price_from');
            }
        });

        Schema::table('inquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('inquiries', 'infants')) {
                $table->unsignedInteger('infants')->default(0)->after('children');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'infants')) {
                $table->unsignedInteger('infants')->default(0)->after('children');
            }
        });

        DB::table('packages')->select([
            'id',
            'start_from_price',
            'compare_price',
            'adult_price',
            'child_price',
            'infant_price',
            'price_from',
            'price_to',
        ])->orderBy('id')->chunkById(100, function ($packages) {
            foreach ($packages as $package) {
                $adultPrice = (float) ($package->adult_price ?? 0);
                $childPrice = (float) ($package->child_price ?? 0);
                $infantPrice = (float) ($package->infant_price ?? 0);
                $startFrom = (float) ($package->start_from_price ?? 0);
                $comparePrice = (float) ($package->compare_price ?? 0);
                $priceFrom = (float) ($package->price_from ?? 0);
                $priceTo = (float) ($package->price_to ?? 0);

                if ($adultPrice <= 0 && $startFrom > 0) {
                    $adultPrice = $startFrom;
                }

                $allPrices = collect([$adultPrice, $childPrice, $infantPrice])
                    ->filter(fn ($value) => $value !== null);
                $paidPrices = $allPrices->filter(fn ($value) => (float) $value > 0);

                $computedFrom = (float) ($paidPrices->min() ?? 0);
                $computedTo = (float) ($allPrices->max() ?? 0);

                if ($priceFrom <= 0 && $computedFrom > 0) {
                    $priceFrom = $computedFrom;
                }

                if ($priceTo <= 0) {
                    $priceTo = $computedTo > 0 ? $computedTo : max($startFrom, $comparePrice);
                }

                DB::table('packages')
                    ->where('id', $package->id)
                    ->update([
                        'adult_price' => $adultPrice,
                        'price_from' => $priceFrom,
                        'price_to' => $priceTo,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'infants')) {
                $table->dropColumn('infants');
            }
        });

        Schema::table('inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('inquiries', 'infants')) {
                $table->dropColumn('infants');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            $columns = [
                'adult_price',
                'child_price',
                'infant_price',
                'adult_min_age',
                'child_min_age',
                'child_max_age',
                'infant_min_age',
                'infant_max_age',
                'price_from',
                'price_to',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('packages', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
