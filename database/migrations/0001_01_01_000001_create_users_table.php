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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الاسم
            $table->string('phone')->unique(); // رقم الهاتف
            $table->string('password');
            $table->enum('role', ['admin', 'resident', 'provider'])->default('resident'); // الدور
            $table->string('block_no')->nullable(); // رقم المبنى
            $table->string('floor')->nullable(); // الطابق
            $table->string('apt_no')->nullable(); // رقم الشقة
            $table->foreignId('service_type_id')->nullable()->constrained('service_categories')->nullOnDelete(); // نوع الخدمة (للمزود)
            $table->string('photo')->nullable(); // صورة الملف الشخصي
            $table->boolean('is_active')->default(false); // حالة التفعيل
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
