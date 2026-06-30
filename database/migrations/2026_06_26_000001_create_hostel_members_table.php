<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            // Optional link to a room type defined for the hostel.
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            // Free-text room number/label shown in the list (e.g. "A-101").
            $table->string('room_number')->nullable();

            $table->string('name');
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // ID proof: aadhaar / pan / passport + the number.
            $table->enum('id_proof_type', ['aadhaar', 'pan', 'passport'])->nullable();
            $table->string('id_proof_number')->nullable();

            // Native place / hometown — used by the search box.
            $table->string('place')->nullable();

            $table->string('photo_path')->nullable();
            $table->string('photo_disk', 20)->default('public');

            $table->date('date_of_join')->nullable();
            $table->date('date_of_left')->nullable();

            $table->decimal('monthly_rent', 10, 2)->nullable();
            // Rent state — mirrors the vocabulary already used on hostel bookings.
            $table->enum('rent_status', ['pending', 'advance_paid', 'fully_paid'])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['hostel_id', 'rent_status']);
            $table->index('phone');
            $table->index('id_proof_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_members');
    }
};
