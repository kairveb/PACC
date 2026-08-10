<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add security-related columns to the users table:
     * - mfa_secret:  the TOTP secret used for two-factor (MFA) verification
     * - mfa_enabled: whether the user has completed MFA setup
     * - last_activity_at: timestamp of the last authenticated request (for inactivity lockout)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mfa_secret')->nullable()->after('remember_token');
            $table->boolean('mfa_enabled')->default(false)->after('mfa_secret');
            $table->timestamp('last_activity_at')->nullable()->after('mfa_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mfa_secret', 'mfa_enabled', 'last_activity_at']);
        });
    }
};
