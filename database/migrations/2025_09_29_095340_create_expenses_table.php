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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('category', [
                'rent',
                'utilities',
                'maintenance',
                'supplies',
                'equipment',
                'salaries',
                'marketing',
                'insurance',
                'other',
            ]);
            $table->string('vendor')->nullable();
            $table->date('expense_date');
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('paid');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();

            // Users relation
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
