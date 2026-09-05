<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            [
                'name' => 'فرع المعادي',
                'location' => 'المعادي، القاهرة، مصر',
                'location_link' => 'https://maps.google.com/?q=29.9602,31.2629',
            ],
            [
                'name' => 'فرع مدينة نصر',
                'location' => 'مدينة نصر، القاهرة، مصر',
                'location_link' => 'https://maps.google.com/?q=30.0444,31.3357',
            ],
            [
                'name' => 'فرع الإسكندرية',
                'location' => 'سموحة، الإسكندرية، مصر',
                'location_link' => 'https://maps.google.com/?q=31.2156,29.9553',
            ],
            [
                'name' => 'فرع الزمالك',
                'location' => 'الزمالك، القاهرة، مصر',
                'location_link' => 'https://maps.google.com/?q=30.0626,31.2207',
            ],
            [
                'name' => 'فرع التجمع الخامس',
                'location' => 'التجمع الخامس، القاهرة الجديدة، مصر',
                'location_link' => 'https://maps.google.com/?q=30.0131,31.4286',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
