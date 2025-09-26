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
        Schema::create('space_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->text('purpose')->nullable(); // Purpose of the reservation
            $table->integer('expected_attendees')->default(1);
            $table->json('additional_services')->nullable(); // Extra services requested
            $table->text('notes')->nullable();
            $table->datetime('checked_in_at')->nullable();
            $table->datetime('checked_out_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_rules')->nullable(); // Rules for recurring reservations
            $table->timestamps();

            // Indexes for better performance
            $table->index(['space_id', 'starts_at', 'ends_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_reservations');
    }
};