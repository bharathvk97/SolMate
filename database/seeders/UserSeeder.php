<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'                 => 'Super Admin',
            'email'                => 'admin@solmate.com',
            'phone'                => '9000000001',
            'password'             => Hash::make('Admin@123'),
            'role'                 => 'admin',
            'status'               => 'active',
            'email_verified_at'    => now(),
            'phone_verified_at'    => now(),
        ]);

        // Hostel Owner 1
        User::create([
            'name'                   => 'Rajan Kumar',
            'email'                  => 'rajan@solmate.com',
            'phone'                  => '9000000002',
            'password'               => Hash::make('Owner@123'),
            'role'                   => 'hostel_owner',
            'status'                 => 'active',
            'email_verified_at'      => now(),
            'phone_verified_at'      => now(),
            'identity_type'          => 'aadhaar',
            'identity_number'        => '1234-5678-9012',
            'identity_status'        => 'verified',
            'subscription_status'    => 'active',
            'subscription_expires_at'=> Carbon::now()->addDays(30),
            'city'                   => 'Kozhikode',
            'state'                  => 'Kerala',
            'lat'                    => 11.2588,
            'lng'                    => 75.7804,
        ]);

        // Hostel Owner 2
        User::create([
            'name'                   => 'Priya Nair',
            'email'                  => 'priya@solmate.com',
            'phone'                  => '9000000003',
            'password'               => Hash::make('Owner@123'),
            'role'                   => 'hostel_owner',
            'status'                 => 'active',
            'email_verified_at'      => now(),
            'phone_verified_at'      => now(),
            'identity_type'          => 'passport',
            'identity_number'        => 'P1234567',
            'identity_status'        => 'verified',
            'subscription_status'    => 'active',
            'subscription_expires_at'=> Carbon::now()->addDays(30),
            'city'                   => 'Kozhikode',
            'state'                  => 'Kerala',
            'lat'                    => 11.2550,
            'lng'                    => 75.7800,
        ]);

        // Mess Owner 1
        User::create([
            'name'                   => 'Suresh Menon',
            'email'                  => 'suresh@solmate.com',
            'phone'                  => '9000000004',
            'password'               => Hash::make('Owner@123'),
            'role'                   => 'mess_owner',
            'status'                 => 'active',
            'email_verified_at'      => now(),
            'phone_verified_at'      => now(),
            'identity_type'          => 'aadhaar',
            'identity_number'        => '9876-5432-1098',
            'identity_status'        => 'verified',
            'subscription_status'    => 'active',
            'subscription_expires_at'=> Carbon::now()->addDays(30),
            'city'                   => 'Kozhikode',
            'state'                  => 'Kerala',
            'lat'                    => 11.2600,
            'lng'                    => 75.7810,
        ]);

        // Mess Owner 2
        User::create([
            'name'                   => 'Lakshmi Devi',
            'email'                  => 'lakshmi@solmate.com',
            'phone'                  => '9000000005',
            'password'               => Hash::make('Owner@123'),
            'role'                   => 'mess_owner',
            'status'                 => 'active',
            'email_verified_at'      => now(),
            'phone_verified_at'      => now(),
            'identity_type'          => 'aadhaar',
            'identity_number'        => '1122-3344-5566',
            'identity_status'        => 'verified',
            'subscription_status'    => 'active',
            'subscription_expires_at'=> Carbon::now()->addDays(30),
            'city'                   => 'Kozhikode',
            'state'                  => 'Kerala',
            'lat'                    => 11.2530,
            'lng'                    => 75.7820,
        ]);

        // Regular Users
        $users = [
            ['name' => 'Arjun Singh',     'email' => 'arjun@example.com',   'phone' => '9100000001'],
            ['name' => 'Meena Pillai',    'email' => 'meena@example.com',   'phone' => '9100000002'],
            ['name' => 'Rahul Sharma',    'email' => 'rahul@example.com',   'phone' => '9100000003'],
            ['name' => 'Anjali Thomas',   'email' => 'anjali@example.com',  'phone' => '9100000004'],
            ['name' => 'Dev Krishnan',    'email' => 'dev@example.com',     'phone' => '9100000005'],
        ];
        foreach ($users as $u) {
            User::create(array_merge($u, [
                'password'          => Hash::make('User@123'),
                'role'              => 'user',
                'status'            => 'active',
                'email_verified_at' => now(),
                'city'              => 'Kozhikode',
                'state'             => 'Kerala',
                'lat'               => 11.2588 + (rand(-100, 100) / 10000),
                'lng'               => 75.7804 + (rand(-100, 100) / 10000),
            ]));
        }
    }
}
