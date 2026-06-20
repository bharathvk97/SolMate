<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable()->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'hostel_owner', 'mess_owner', 'user'])->default('user');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_verification'])->default('pending_verification');
            $table->string('avatar')->nullable();
            $table->string('email_otp', 6)->nullable();
            $table->timestamp('email_otp_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_otp', 6)->nullable();
            $table->timestamp('phone_otp_expires_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            // Identity verification
            $table->enum('identity_type', ['aadhaar', 'passport'])->nullable();
            $table->string('identity_number')->nullable();
            $table->string('identity_document_front')->nullable();
            $table->string('identity_document_back')->nullable();
            $table->enum('identity_status', ['pending', 'verified', 'rejected'])->nullable();
            // Subscription (for owners)
            $table->enum('subscription_status', ['active', 'expired', 'cancelled', 'trial'])->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamp('last_subscription_reminder_at')->nullable();
            // Location
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            // Preferences
            $table->string('theme_preference', 10)->default('system');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'status']);
            $table->index(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
