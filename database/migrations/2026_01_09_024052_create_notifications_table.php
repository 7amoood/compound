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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // new_request, new_proposal, proposal_accepted, work_started, work_completed, new_review
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // Additional data like request_id, proposal_id, etc.
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
