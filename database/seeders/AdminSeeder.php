<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::updateOrCreate(
            ['email' => 'mohamedramadan@renix.com'], // يمكنك تغييره
            [
                'name' => 'mohamedramadan',
                'email' => 'mohamedramadan@renix.com',
                'phone' => '01000000000', 
                'avatar' => null,
                'password' => Hash::make('password123'),
            ]
        );
    }
}
