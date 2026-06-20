<?php
// 2024_01_01_000007_create_bookings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hostel Bookings
        Schema::create('hostel_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('occupants')->default(1);
            $table->decimal('monthly_rate', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'cancelled', 'checked_in', 'checked_out'])->default('pending');
            $table->text('user_note')->nullable();
            $table->text('owner_note')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['hostel_id', 'status']);
        });

        // Mess Subscriptions/Bookings
        Schema::create('mess_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mess_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('mess_subscription_plans');
            $table->json('selected_slots');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['active', 'expired', 'cancelled', 'paused'])->default('active');
            $table->boolean('auto_renew')->default(false);
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['mess_id', 'status']);
            $table->index('end_date');
        });

        // Reviews (polymorphic: hostel or mess)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reviewable'); // hostel or mess
            $table->integer('rating'); // 1-5
            $table->integer('cleanliness_rating')->nullable();
            $table->integer('food_rating')->nullable();
            $table->integer('value_rating')->nullable();
            $table->integer('staff_rating')->nullable();
            $table->integer('location_rating')->nullable();
            $table->text('body');
            $table->boolean('is_verified')->default(false); // verified booking
            $table->boolean('is_hidden')->default(false); // admin hidden
            $table->integer('helpful_count')->default(0);
            $table->text('owner_reply')->nullable();
            $table->timestamp('owner_replied_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'reviewable_type', 'reviewable_id']);
        });

        Schema::create('review_helpful', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'review_id']);
        });

        // Favourites (polymorphic)
        Schema::create('favourites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('favourable');
            $table->timestamps();

            $table->unique(['user_id', 'favourable_type', 'favourable_id']);
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('favourites');
        Schema::dropIfExists('review_helpful');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('mess_bookings');
        Schema::dropIfExists('hostel_bookings');
    }
};
