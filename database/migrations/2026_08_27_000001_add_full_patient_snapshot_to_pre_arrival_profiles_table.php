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
        Schema::table('pre_arrival_profiles', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('patient_id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('suffix')->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('suffix');
            $table->string('sex')->nullable()->after('date_of_birth');
            $table->string('civil_status')->nullable()->after('sex');
            $table->string('nationality')->nullable()->after('civil_status');
            $table->string('phone')->nullable()->after('nationality');
            $table->string('email')->nullable()->after('phone');
            $table->string('address_barangay')->nullable()->after('address_line1');
            $table->string('address_postal')->nullable()->after('address_province');
            $table->string('emergency_name')->nullable()->after('allergies');
            $table->string('emergency_phone')->nullable()->after('emergency_name');
            $table->string('emergency_relationship')->nullable()->after('emergency_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_arrival_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'date_of_birth',
                'sex',
                'civil_status',
                'nationality',
                'phone',
                'email',
                'address_barangay',
                'address_postal',
                'emergency_name',
                'emergency_phone',
                'emergency_relationship',
            ]);
        });
    }
};
