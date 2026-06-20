<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;
use App\Models\OwnerSubscriptionPlan;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Wi-Fi',           'icon' => 'wifi',           'category' => 'utility'],
            ['name' => 'AC',              'icon' => 'wind',           'category' => 'comfort'],
            ['name' => 'Parking',         'icon' => 'car',            'category' => 'utility'],
            ['name' => 'Laundry',         'icon' => 'shirt',          'category' => 'utility'],
            ['name' => 'CCTV',            'icon' => 'camera',         'category' => 'security'],
            ['name' => 'Power Backup',    'icon' => 'zap',            'category' => 'utility'],
            ['name' => 'Gym',             'icon' => 'dumbbell',       'category' => 'comfort'],
            ['name' => 'Mess / Canteen',  'icon' => 'utensils',       'category' => 'utility'],
            ['name' => 'Security Guard',  'icon' => 'shield',         'category' => 'security'],
            ['name' => 'Study Room',      'icon' => 'book-open',      'category' => 'comfort'],
            ['name' => 'TV Lounge',       'icon' => 'tv',             'category' => 'comfort'],
            ['name' => 'Water Purifier',  'icon' => 'droplet',        'category' => 'utility'],
            ['name' => 'Lift / Elevator', 'icon' => 'arrow-up',       'category' => 'utility'],
            ['name' => 'Housekeeping',    'icon' => 'brush',          'category' => 'utility'],
            ['name' => 'First Aid',       'icon' => 'heart-pulse',    'category' => 'security'],
        ];
        foreach ($amenities as $a) {
            Amenity::firstOrCreate(['name' => $a['name']], $a);
        }
    }
}

class OwnerSubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                   => 'Basic Monthly',
                'slug'                   => 'basic-monthly',
                'owner_type'             => 'both',
                'price'                  => 499.00,
                'duration_days'          => 30,
                'max_listings'           => 1,
                'allow_image_upload'     => true,
                'max_images_per_listing' => 10,
                'featured_listing'       => false,
                'features'               => json_encode([
                    '1 Listing',
                    'Up to 10 Images',
                    'Email Support',
                    'Standard Visibility',
                ]),
                'is_active'              => true,
            ],
            [
                'name'                   => 'Pro Monthly',
                'slug'                   => 'pro-monthly',
                'owner_type'             => 'both',
                'price'                  => 999.00,
                'duration_days'          => 30,
                'max_listings'           => 3,
                'allow_image_upload'     => true,
                'max_images_per_listing' => 25,
                'featured_listing'       => true,
                'features'               => json_encode([
                    'Up to 3 Listings',
                    'Up to 25 Images per Listing',
                    'Featured Badge',
                    'Priority Visibility',
                    'Priority Support',
                    'Analytics Dashboard',
                ]),
                'is_active'              => true,
            ],
            [
                'name'                   => 'Enterprise Monthly',
                'slug'                   => 'enterprise-monthly',
                'owner_type'             => 'both',
                'price'                  => 1999.00,
                'duration_days'          => 30,
                'max_listings'           => 10,
                'allow_image_upload'     => true,
                'max_images_per_listing' => 50,
                'featured_listing'       => true,
                'features'               => json_encode([
                    'Up to 10 Listings',
                    'Up to 50 Images per Listing',
                    'Featured + Top of Search',
                    'Dedicated Account Manager',
                    'Advanced Analytics',
                    'API Access',
                ]),
                'is_active'              => true,
            ],
        ];

        foreach ($plans as $plan) {
            OwnerSubscriptionPlan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
