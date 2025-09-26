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
        Schema::create('space_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['preventive', 'corrective', 'emergency', 'inspection']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'postponed']);
            $table->datetime('scheduled_at');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->string('assigned_to')->nullable(); // Maintenance person/company
            $table->text('notes')->nullable();
            $table->json('checklist')->nullable(); // Maintenance checklist items
            $table->json('parts_needed')->nullable(); // Parts/materials needed
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index(['space_id', 'status']);
            $table->index(['scheduled_at', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_maintenance');
    }
};