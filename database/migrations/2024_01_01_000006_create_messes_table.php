<?php
// 2024_01_01_000006_create_messes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->enum('food_type', ['veg', 'non_veg', 'both'])->default('veg');
            $table->enum('status', ['active', 'inactive', 'pending', 'rejected'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->boolean('has_delivery')->default(false);
            $table->boolean('has_tiffin')->default(false);
            $table->boolean('has_dine_in')->default(true);
            $table->string('cover_image')->nullable();
            $table->string('disk', 10)->default('local');
            // Timings
            $table->time('morning_open')->nullable()->default('07:00:00');
            $table->time('morning_close')->nullable()->default('10:00:00');
            $table->time('afternoon_open')->nullable()->default('12:00:00');
            $table->time('afternoon_close')->nullable()->default('15:00:00');
            $table->time('evening_open')->nullable()->default('17:00:00');
            $table->time('evening_close')->nullable()->default('19:00:00');
            $table->time('night_open')->nullable()->default('19:30:00');
            $table->time('night_close')->nullable()->default('22:00:00');
            // Rating
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamp('admin_reviewed_at')->nullable();
            $table->string('admin_rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lat', 'lng']);
            $table->index(['status', 'is_featured']);
            $table->index('city');
        });

        Schema::create('mess_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mess_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('disk', 10)->default('local');
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mess_id')->constrained()->cascadeOnDelete();
            $table->enum('slot', ['morning', 'afternoon', 'evening', 'night']);
            $table->enum('day_type', ['everyday', 'weekday', 'weekend', 'custom'])->default('everyday');
            $table->json('days_of_week')->nullable(); // [0,1,2,3,4,5,6] for custom
            $table->string('title')->nullable(); // e.g., "South Indian Breakfast"
            $table->json('items'); // [{"name":"Idli","qty":"2 pcs"},...]
            $table->decimal('price', 8, 2);
            $table->boolean('is_available')->default(true);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['mess_id', 'slot', 'is_available']);
        });

        Schema::create('menu_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('disk', 10)->default('local');
            $table->timestamps();
        });

        Schema::create('mess_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mess_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Monthly Full Board"
            $table->json('slots'); // ['morning','afternoon','evening','night']
            $table->integer('duration_days')->default(30);
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mess_subscription_plans');
        Schema::dropIfExists('menu_images');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('mess_images');
        Schema::dropIfExists('messes');
    }
};
