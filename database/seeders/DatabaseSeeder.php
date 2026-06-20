<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AmenitySeeder::class,
            OwnerSubscriptionPlanSeeder::class,
            UserSeeder::class,
            HostelSeeder::class,
            MessSeeder::class,
        ]);
    }
}
