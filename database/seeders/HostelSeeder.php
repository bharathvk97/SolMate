<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Hostel, Room, Mess, Menu, MessSubscriptionPlan};

class HostelSeeder extends Seeder
{
    public function run(): void
    {
        $owner1 = \App\Models\User::where('email', 'rajan@solmate.com')->first();
        $owner2 = \App\Models\User::where('email', 'priya@solmate.com')->first();

        $hostel1 = Hostel::create([
            'owner_id'        => $owner1->id,
            'name'            => 'Green Valley Boys Hostel',
            'slug'            => 'green-valley-boys-hostel',
            'description'     => 'A comfortable and safe boys hostel located in the heart of Kozhikode with excellent facilities and 24/7 security.',
            'address'         => '12, MG Road, Palayam',
            'city'            => 'Kozhikode',
            'state'           => 'Kerala',
            'pincode'         => '673001',
            'lat'             => 11.2588,
            'lng'             => 75.7804,
            'phone'           => '9400000001',
            'gender_type'     => 'boys',
            'status'          => 'active',
            'has_wifi'        => true,
            'has_cctv'        => true,
            'has_power_backup'=> true,
            'has_security'    => true,
            'has_mess'        => true,
            'curfew_time'     => '22:00',
            'allow_guests'    => false,
        ]);

        Room::create(['hostel_id'=>$hostel1->id,'name'=>'Single Non-AC','type'=>'single','is_ac'=>false,'price_per_month'=>5000,'security_deposit'=>5000,'capacity'=>1,'total_count'=>10,'available_count'=>4,'has_attached_bathroom'=>false,'has_study_table'=>true,'has_wardrobe'=>true]);
        Room::create(['hostel_id'=>$hostel1->id,'name'=>'Single AC','type'=>'single','is_ac'=>true,'price_per_month'=>7000,'security_deposit'=>7000,'capacity'=>1,'total_count'=>5,'available_count'=>2,'has_attached_bathroom'=>true,'has_study_table'=>true,'has_wardrobe'=>true]);
        Room::create(['hostel_id'=>$hostel1->id,'name'=>'Shared Room (3-bed)','type'=>'shared','is_ac'=>false,'price_per_month'=>3000,'security_deposit'=>3000,'capacity'=>3,'total_count'=>8,'available_count'=>6,'has_attached_bathroom'=>false,'has_study_table'=>true]);

        $hostel2 = Hostel::create([
            'owner_id'        => $owner2->id,
            'name'            => 'Sunrise Girls Hostel',
            'slug'            => 'sunrise-girls-hostel',
            'description'     => 'Premium girls hostel with homely atmosphere, hygienic food, and excellent connectivity to colleges and markets.',
            'address'         => '45, Beach Road, Kappad',
            'city'            => 'Kozhikode',
            'state'           => 'Kerala',
            'pincode'         => '673001',
            'lat'             => 11.2550,
            'lng'             => 75.7800,
            'phone'           => '9400000002',
            'gender_type'     => 'girls',
            'status'          => 'active',
            'has_wifi'        => true,
            'has_ac'          => true,
            'has_cctv'        => true,
            'has_laundry'     => true,
            'has_power_backup'=> true,
            'has_security'    => true,
        ]);

        Room::create(['hostel_id'=>$hostel2->id,'name'=>'Double AC','type'=>'double','is_ac'=>true,'price_per_month'=>4500,'security_deposit'=>4500,'capacity'=>2,'total_count'=>6,'available_count'=>3,'has_attached_bathroom'=>true,'has_study_table'=>true]);
        Room::create(['hostel_id'=>$hostel2->id,'name'=>'Single Premium AC','type'=>'single','is_ac'=>true,'price_per_month'=>8000,'security_deposit'=>8000,'capacity'=>1,'total_count'=>4,'available_count'=>1,'has_attached_bathroom'=>true,'has_balcony'=>true,'has_tv'=>true]);
    }
}

class MessSeeder extends Seeder
{
    public function run(): void
    {
        $owner1 = \App\Models\User::where('email', 'suresh@solmate.com')->first();
        $owner2 = \App\Models\User::where('email', 'lakshmi@solmate.com')->first();

        $mess1 = Mess::create([
            'owner_id'      => $owner1->id,
            'name'          => 'Suresh Home Foods',
            'slug'          => 'suresh-home-foods',
            'description'   => 'Authentic Kerala home-cooked vegetarian and non-vegetarian meals at affordable prices. Serving students since 2010.',
            'address'       => '8, College Road, Chevayur',
            'city'          => 'Kozhikode',
            'state'         => 'Kerala',
            'pincode'       => '673017',
            'lat'           => 11.2600,
            'lng'           => 75.7810,
            'phone'         => '9400000003',
            'food_type'     => 'both',
            'status'        => 'active',
            'has_delivery'  => true,
            'has_tiffin'    => true,
        ]);

        Menu::create(['mess_id'=>$mess1->id,'slot'=>'morning','title'=>'Kerala Breakfast','items'=>json_encode([['name'=>'Idli','qty'=>'3 pcs'],['name'=>'Sambar','qty'=>'1 bowl'],['name'=>'Chutney','qty'=>'2 types'],['name'=>'Tea/Coffee','qty'=>'1 cup']]),'price'=>60,'is_available'=>true,'status'=>'open']);
        Menu::create(['mess_id'=>$mess1->id,'slot'=>'afternoon','title'=>'Kerala Sadya','items'=>json_encode([['name'=>'Rice','qty'=>'Unlimited'],['name'=>'Dal','qty'=>'1 bowl'],['name'=>'Sambar','qty'=>'1 bowl'],['name'=>'Fish Curry','qty'=>'1 piece'],['name'=>'Pickle & Papad','qty'=>'1 each']]),'price'=>100,'is_available'=>true,'status'=>'open']);
        Menu::create(['mess_id'=>$mess1->id,'slot'=>'evening','title'=>'Evening Snacks','items'=>json_encode([['name'=>'Pazham Pori','qty'=>'2 pcs'],['name'=>'Tea','qty'=>'1 cup']]),'price'=>30,'is_available'=>true,'status'=>'open']);
        Menu::create(['mess_id'=>$mess1->id,'slot'=>'night','title'=>'Dinner','items'=>json_encode([['name'=>'Chapati','qty'=>'3 pcs'],['name'=>'Chicken Curry','qty'=>'1 bowl'],['name'=>'Dal','qty'=>'1 bowl'],['name'=>'Rice','qty'=>'1 bowl']]),'price'=>90,'is_available'=>true,'status'=>'open']);

        MessSubscriptionPlan::create(['mess_id'=>$mess1->id,'name'=>'Full Board Monthly','slots'=>json_encode(['morning','afternoon','evening','night']),'duration_days'=>30,'price'=>2400,'is_active'=>true]);
        MessSubscriptionPlan::create(['mess_id'=>$mess1->id,'name'=>'Lunch & Dinner Monthly','slots'=>json_encode(['afternoon','night']),'duration_days'=>30,'price'=>1800,'is_active'=>true]);

        $mess2 = Mess::create([
            'owner_id'      => $owner2->id,
            'name'          => 'Lakshmi Pure Veg Mess',
            'slug'          => 'lakshmi-pure-veg-mess',
            'description'   => 'Pure vegetarian South Indian meals cooked with love. No onion, no garlic options available on request.',
            'address'       => '22, KSRTC Road, Nadakkavu',
            'city'          => 'Kozhikode',
            'state'         => 'Kerala',
            'pincode'       => '673011',
            'lat'           => 11.2530,
            'lng'           => 75.7820,
            'phone'         => '9400000004',
            'food_type'     => 'veg',
            'status'        => 'active',
            'has_delivery'  => false,
            'has_dine_in'   => true,
        ]);

        Menu::create(['mess_id'=>$mess2->id,'slot'=>'morning','title'=>'South Indian Breakfast','items'=>json_encode([['name'=>'Dosa','qty'=>'2 pcs'],['name'=>'Sambar','qty'=>'1 bowl'],['name'=>'Coconut Chutney','qty'=>'1 bowl'],['name'=>'Coffee','qty'=>'1 cup']]),'price'=>50,'is_available'=>true,'status'=>'open']);
        Menu::create(['mess_id'=>$mess2->id,'slot'=>'afternoon','title'=>'Veg Meals','items'=>json_encode([['name'=>'Rice','qty'=>'Unlimited'],['name'=>'Sambar','qty'=>'Unlimited'],['name'=>'Rasam','qty'=>'1 bowl'],['name'=>'Curd','qty'=>'1 bowl'],['name'=>'2 Vegetable Curries','qty'=>'1 each']]),'price'=>80,'is_available'=>true,'status'=>'open']);
        Menu::create(['mess_id'=>$mess2->id,'slot'=>'night','title'=>'Light Dinner','items'=>json_encode([['name'=>'Chapati','qty'=>'3 pcs'],['name'=>'Dal Tadka','qty'=>'1 bowl'],['name'=>'Sabzi','qty'=>'1 bowl']]),'price'=>70,'is_available'=>true,'status'=>'open']);

        MessSubscriptionPlan::create(['mess_id'=>$mess2->id,'name'=>'Monthly Veg Thali','slots'=>json_encode(['morning','afternoon','night']),'duration_days'=>30,'price'=>2100,'is_active'=>true]);
    }
}
