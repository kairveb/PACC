<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('lookup_code')->nullable()->unique()->after('email');
            $table->string('pre_registration_status')->default('not_started')->after('lookup_code');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['lookup_code']);
            $table->dropColumn(['lookup_code', 'pre_registration_status']);
        });
    }
};
