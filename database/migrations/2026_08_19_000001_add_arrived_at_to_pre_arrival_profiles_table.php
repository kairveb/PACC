<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_arrival_profiles', function (Blueprint $table) {
            $table->dateTime('arrived_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pre_arrival_profiles', function (Blueprint $table) {
            $table->dropColumn('arrived_at');
        });
    }
};
