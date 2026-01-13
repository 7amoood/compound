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
        // Help Requests Table
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->text('description');
            $table->enum('status', ['open', 'picked', 'cancelled'])->default('open');
            $table->foreignId('helper_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('picked_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('requester_id');
            $table->index('helper_id');
        });

        // Help Comments Table
        Schema::create('help_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_request_id')->constrained('help_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->timestamps();

            $table->index('help_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_comments');
        Schema::dropIfExists('help_requests');
    }
};
