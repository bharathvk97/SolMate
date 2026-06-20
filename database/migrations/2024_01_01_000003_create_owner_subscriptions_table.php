<?php
// 2024_01_01_000003_create_owner_subscription_plans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Basic Monthly", "Pro Monthly"
            $table->string('slug')->unique();
            $table->enum('owner_type', ['hostel_owner', 'mess_owner', 'both'])->default('both');
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->default(30);
            $table->integer('max_listings')->default(1);
            $table->boolean('allow_image_upload')->default(true);
            $table->integer('max_images_per_listing')->default(10);
            $table->boolean('featured_listing')->default(false);
            $table->json('features')->nullable(); // list of feature strings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('owner_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('owner_subscription_plans');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_subscriptions');
        Schema::dropIfExists('owner_subscription_plans');
    }
};
