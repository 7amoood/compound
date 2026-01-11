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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('users')->cascadeOnDelete(); // الساكن
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete(); // نوع الخدمة
            $table->enum('status', ['pending', 'accepted_offer', 'in_progress', 'completed', 'cancelled'])->default('pending'); // الحالة
            $table->text('notes')->nullable(); // ملاحظات
            $table->string('delivery_method')->nullable(); // طريقة التوصيل
            $table->string('location')->nullable(); // الموقع (مطبخ، غرفة نوم، إلخ)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
