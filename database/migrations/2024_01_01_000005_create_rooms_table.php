<?php
// 2024_01_01_000005_create_rooms_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "AC Single", "Non-AC Double"
            $table->enum('type', ['single', 'double', 'triple', 'shared', 'dormitory']);
            $table->boolean('is_ac')->default(false);
            $table->decimal('price_per_month', 10, 2);
            $table->decimal('price_per_day', 10, 2)->nullable();
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->integer('capacity')->default(1); // max occupants
            $table->integer('total_count')->default(1); // total rooms of this type
            $table->integer('available_count')->default(1);
            // Facilities
            $table->boolean('has_attached_bathroom')->default(false);
            $table->boolean('has_balcony')->default(false);
            $table->boolean('has_study_table')->default(true);
            $table->boolean('has_wardrobe')->default(false);
            $table->boolean('has_tv')->default(false);
            $table->boolean('has_fridge')->default(false);
            $table->string('floor_number')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hostel_id', 'type', 'is_available']);
        });

        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('disk', 10)->default('local');
            $table->boolean('is_cover')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('rooms');
    }
};
