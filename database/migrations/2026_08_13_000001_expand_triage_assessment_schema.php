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
        Schema::table('triage_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('triage_assessments', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('triage_assessments', 'priority_score')) {
                $table->unsignedTinyInteger('priority_score')->nullable()->after('priority');
            }

            if (! Schema::hasColumn('triage_assessments', 'triage_color')) {
                $table->string('triage_color')->nullable()->after('priority_score');
            }

            if (! Schema::hasColumn('triage_assessments', 'symptoms')) {
                $table->json('symptoms')->nullable()->after('chief_complaint');
            }
        });

        Schema::table('triage_vitals', function (Blueprint $table) {
            if (! Schema::hasColumn('triage_vitals', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('triage_vitals', 'recorded_at')) {
                $table->timestamp('recorded_at')->nullable()->after('weight');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('triage_vitals', function (Blueprint $table) {
            if (Schema::hasColumn('triage_vitals', 'recorded_at')) {
                $table->dropColumn('recorded_at');
            }

            if (Schema::hasColumn('triage_vitals', 'patient_id')) {
                $table->dropConstrainedForeignId('patient_id');
            }
        });

        Schema::table('triage_assessments', function (Blueprint $table) {
            if (Schema::hasColumn('triage_assessments', 'symptoms')) {
                $table->dropColumn('symptoms');
            }

            if (Schema::hasColumn('triage_assessments', 'triage_color')) {
                $table->dropColumn('triage_color');
            }

            if (Schema::hasColumn('triage_assessments', 'priority_score')) {
                $table->dropColumn('priority_score');
            }

            if (Schema::hasColumn('triage_assessments', 'patient_id')) {
                $table->dropConstrainedForeignId('patient_id');
            }
        });
    }
};
