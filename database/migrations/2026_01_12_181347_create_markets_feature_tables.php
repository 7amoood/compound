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
        // 1. Markets Table
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add market_id to Users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('market_id')->nullable()->after('role')->constrained('markets')->nullOnDelete();
        });

        // 3. Add market_id to Requests
        Schema::table('requests', function (Blueprint $table) {
            $table->foreignId('market_id')->nullable()->after('service_category_id')->constrained('markets')->nullOnDelete();
        });

        // 4. Request Items Table
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('name');
            $table->string('quantity')->nullable();
            $table->boolean('is_picked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_items');

        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropColumn('market_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropColumn('market_id');
        });

        Schema::dropIfExists('markets');
    }
};
