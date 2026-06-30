<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mess_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mess_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('location')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Aadhaar / ID number.
            $table->string('id_proof_number')->nullable();

            $table->string('photo_path')->nullable();
            $table->string('photo_disk', 20)->default('public');

            $table->date('join_date')->nullable();
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['mess_id', 'payment_status']);
            $table->index('phone');
            $table->index('id_proof_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mess_members');
    }
};
