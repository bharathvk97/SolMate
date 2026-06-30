<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hostel_bookings', function (Blueprint $table) {
            $table->enum('rent_status', ['pending', 'advance_paid', 'fully_paid'])
                  ->default('pending')
                  ->after('payment_status');
        });

        // Backfill: anyone whose payment is already complete is treated as fully-paid rent.
        DB::table('hostel_bookings')->where('payment_status', 'paid')->update(['rent_status' => 'fully_paid']);
    }

    public function down(): void
    {
        Schema::table('hostel_bookings', function (Blueprint $table) {
            $table->dropColumn('rent_status');
        });
    }
};
