<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number')->unique(); // Space number/identifier (e.g., "A101", "B205")
            $table->enum('type', ['office', 'meeting_room', 'open_space', 'phone_booth', 'other']);
            $table->text('description')->nullable();
            $table->integer('capacity'); // Maximum number of people
            $table->decimal('area', 8, 2)->nullable(); // Area in square meters
            $table->json('features')->nullable(); // Equipment/features available
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance', 'out_of_order'])->default('available');
            $table->decimal('hourly_rate', 8, 2)->nullable(); // Hourly rental rate
            $table->decimal('daily_rate', 8, 2)->nullable(); // Daily rental rate
            $table->json('iot_sensors')->nullable(); // IoT sensor data/configuration
            $table->boolean('is_active')->default(true);
            $table->integer('floor_level')->default(1);
            $table->string('location_details')->nullable(); // Additional location info
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};