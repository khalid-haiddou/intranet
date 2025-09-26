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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('options'); // Store poll options as JSON
            $table->enum('status', ['draft', 'active', 'ended'])->default('draft');
            $table->enum('visibility', ['all', 'active', 'plan', 'custom'])->default('all');
            $table->integer('duration_days')->default(7);
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('allow_multiple_choices')->default(false);
            $table->boolean('anonymous_voting')->default(false);
            $table->integer('total_votes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};