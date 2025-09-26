<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Account type and role
            $table->enum('account_type', ['individual', 'company']);
            $table->enum('role', ['admin', 'user'])->default('user');
            
            // Individual fields
            $table->string('prenom')->nullable();
            $table->string('nom')->nullable();
            $table->string('cin')->nullable()->unique();
            
            // Company fields
            $table->string('company_name')->nullable();
            $table->string('rc')->nullable()->unique();
            $table->string('ice')->nullable();
            $table->string('legal_representative')->nullable();
            
            // Common contact fields
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone');
            $table->text('address')->nullable();
            
            // Membership details
            $table->enum('membership_plan', ['hot-desk', 'bureau-dedie', 'bureau-prive']);
            $table->decimal('price', 8, 2);
            $table->enum('billing_cycle', ['daily', 'weekly', 'biweekly', 'monthly'])->default('daily');
            
            // Authentication
            $table->string('password');
            $table->boolean('newsletter')->default(false);
            $table->boolean('terms_accepted')->default(false);
            
            // Status and tracking
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['account_type', 'role']);
            $table->index('membership_plan');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};