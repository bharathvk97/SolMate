<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            // Optional link to a room type; room_number keeps a human label.
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('room_number')->nullable();

            // The item kept in the room, e.g. "Bed", "Chair", "Pillow".
            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(0);

            $table->string('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('hostel_id');
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_assets');
    }
};
