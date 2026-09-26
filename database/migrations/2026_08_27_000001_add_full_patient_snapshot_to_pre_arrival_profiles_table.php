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
            if (!Schema::hasColumn('pre_arrival_profiles', 'first_name')) {
                $table->string('first_name')->nullable()->after('patient_id');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'last_name')) {
                $table->string('last_name')->nullable()->after('middle_name');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'suffix')) {
                $table->string('suffix')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('suffix');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'sex')) {
                $table->string('sex')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'civil_status')) {
                $table->string('civil_status')->nullable()->after('sex');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'nationality')) {
                $table->string('nationality')->nullable()->after('civil_status');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'phone')) {
                $table->string('phone')->nullable()->after('nationality');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'address_barangay')) {
                $table->string('address_barangay')->nullable()->after('address_line1');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'address_postal')) {
                $table->string('address_postal')->nullable()->after('address_province');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'emergency_name')) {
                $table->string('emergency_name')->nullable()->after('allergies');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'emergency_phone')) {
                $table->string('emergency_phone')->nullable()->after('emergency_name');
            }
            if (!Schema::hasColumn('pre_arrival_profiles', 'emergency_relationship')) {
                $table->string('emergency_relationship')->nullable()->after('emergency_phone');
            }
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
