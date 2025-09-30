<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop if exists to avoid conflicts
        Schema::dropIfExists('invoices');
        
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // THIS COLUMN IS MISSING
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->datetime('issued_at');
            $table->datetime('due_at')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->text('description');
            $table->json('items')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'issued_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};