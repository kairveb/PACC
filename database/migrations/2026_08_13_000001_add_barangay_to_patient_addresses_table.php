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
        Schema::table('patient_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_addresses', 'barangay')) {
                $table->string('barangay')->nullable()->after('line2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('patient_addresses', 'barangay')) {
                $table->dropColumn('barangay');
            }
        });
    }
};
