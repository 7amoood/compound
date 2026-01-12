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
        Schema::table('users', function (Blueprint $table) {
            $table->text('webauthn_credential_id')->nullable()->after('password');
            $table->text('webauthn_public_key')->nullable()->after('webauthn_credential_id');
            $table->boolean('biometric_enabled')->default(false)->after('webauthn_public_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['webauthn_credential_id', 'webauthn_public_key', 'biometric_enabled']);
        });
    }
};
