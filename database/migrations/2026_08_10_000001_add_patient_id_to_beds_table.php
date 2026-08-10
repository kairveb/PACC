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
        Schema::table('beds', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('room_id')->constrained('patients')->nullOnDelete();
            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropIndex(['patient_id']);
            $table->dropColumn('patient_id');
        });
    }
};
