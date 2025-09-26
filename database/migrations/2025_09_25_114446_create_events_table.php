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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['networking', 'workshop', 'conference', 'social', 'training']);
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->integer('duration'); // in minutes
            $table->integer('capacity');
            $table->decimal('price', 8, 2)->default(0);
            $table->string('location');
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('published');
            $table->text('notes')->nullable();
            $table->json('additional_services')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['status', 'starts_at']);
            $table->index(['type']);
            $table->index(['created_by']);
            $table->index(['starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};