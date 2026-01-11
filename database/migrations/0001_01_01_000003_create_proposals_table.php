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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete(); // الطلب
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete(); // مزود الخدمة
            $table->decimal('price', 10, 2); // السعر
            $table->text('notes')->nullable(); // ملاحظات المزود
            $table->boolean('is_accepted')->default(false); // هل تم القبول
            $table->timestamps();

            // Prevent duplicate proposals from same provider
            $table->unique(['request_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
