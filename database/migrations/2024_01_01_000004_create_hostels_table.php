<?php
// 2024_01_01_000004_create_hostels_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->enum('gender_type', ['boys', 'girls', 'coed'])->default('coed');
            $table->enum('status', ['active', 'inactive', 'pending', 'rejected'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->string('cover_image')->nullable();
            // Facilities
            $table->boolean('has_wifi')->default(false);
            $table->boolean('has_ac')->default(false);
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_laundry')->default(false);
            $table->boolean('has_cctv')->default(false);
            $table->boolean('has_power_backup')->default(false);
            $table->boolean('has_gym')->default(false);
            $table->boolean('has_mess')->default(false);
            $table->boolean('has_security')->default(false);
            // Rules
            $table->string('curfew_time')->nullable();
            $table->boolean('allow_guests')->default(false);
            $table->boolean('allow_smoking')->default(false);
            $table->boolean('allow_alcohol')->default(false);
            $table->text('house_rules')->nullable();
            // Meta
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->json('nearby_landmarks')->nullable();
            $table->timestamp('admin_reviewed_at')->nullable();
            $table->string('admin_rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lat', 'lng']);
            $table->index(['status', 'is_featured']);
            $table->index('city');
        });

        Schema::create('hostel_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('disk', 10)->default('local'); // 's3' or 'local'
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->index(['hostel_id', 'sort_order']);
        });

        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable(); // lucide icon name
            $table->string('category', 50)->nullable(); // 'comfort', 'security', 'utility'
            $table->timestamps();
        });

        Schema::create('hostel_amenities', function (Blueprint $table) {
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['hostel_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_amenities');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('hostel_images');
        Schema::dropIfExists('hostels');
    }
};
