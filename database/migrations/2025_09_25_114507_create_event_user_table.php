<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['registered', 'waitlist', 'attended', 'cancelled'])->default('registered');
            $table->timestamp('registered_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_user');
    }
};