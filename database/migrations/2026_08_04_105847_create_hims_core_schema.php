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
        Schema::create('roles', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->string('label'); $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->string('label'); $table->timestamps();
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->primary(['role_id', 'user_id']);
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete(); $table->foreignId('role_id')->constrained()->cascadeOnDelete(); $table->primary(['permission_id', 'role_id']);
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('name')->unique(); $table->string('phone')->nullable(); $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('specialties', function (Blueprint $table) {
            $table->id(); $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete(); $table->string('name')->unique(); $table->timestamps();
        });
        Schema::create('providers', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete(); $table->string('license_number')->nullable()->unique(); $table->string('display_name'); $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('provider_specialties', function (Blueprint $table) {
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete(); $table->foreignId('specialty_id')->constrained()->cascadeOnDelete(); $table->primary(['provider_id', 'specialty_id']);
        });
        Schema::create('patients', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete(); $table->string('mrn')->unique(); $table->string('first_name'); $table->string('middle_name')->nullable(); $table->string('last_name'); $table->string('suffix')->nullable(); $table->date('date_of_birth')->index(); $table->string('sex', 20); $table->string('civil_status', 30)->nullable(); $table->string('nationality')->nullable(); $table->string('phone', 30)->nullable()->index(); $table->string('email')->nullable()->index(); $table->text('allergies')->nullable(); $table->string('insurance_number')->nullable(); $table->boolean('verified')->default(false); $table->softDeletes(); $table->timestamps();
            $table->index(['last_name', 'first_name']);
        });
        Schema::create('patient_identifiers', function (Blueprint $table) {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->string('type'); $table->string('value'); $table->timestamps(); $table->unique(['type', 'value']);
        });
        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->string('line1'); $table->string('line2')->nullable(); $table->string('city')->nullable(); $table->string('province')->nullable(); $table->string('postal_code', 20)->nullable(); $table->boolean('primary')->default(true); $table->timestamps();
        });
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('relationship'); $table->string('phone', 30); $table->timestamps();
        });
        Schema::create('patient_consents', function (Blueprint $table) {
            $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->string('type'); $table->boolean('granted'); $table->timestamp('recorded_at'); $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->unsignedSmallInteger('default_duration')->default(30); $table->boolean('telehealth')->default(false); $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('provider_schedules', function (Blueprint $table) {
            $table->id(); $table->foreignId('provider_id')->constrained()->cascadeOnDelete(); $table->unsignedTinyInteger('day_of_week'); $table->time('start_time'); $table->time('end_time'); $table->unsignedSmallInteger('slot_duration')->default(30); $table->time('break_start')->nullable(); $table->time('break_end')->nullable(); $table->date('unavailable_date')->nullable(); $table->timestamps(); $table->index(['provider_id', 'day_of_week']);
        });
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id(); $table->foreignId('provider_id')->constrained()->cascadeOnDelete(); $table->foreignId('appointment_type_id')->nullable()->constrained()->nullOnDelete(); $table->dateTime('starts_at'); $table->dateTime('ends_at'); $table->string('status')->default('AVAILABLE'); $table->timestamps(); $table->unique(['provider_id', 'starts_at']);
        });
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); $table->string('appointment_number')->unique(); $table->foreignId('patient_id')->constrained(); $table->foreignId('provider_id')->constrained(); $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('appointment_type_id')->nullable()->constrained()->nullOnDelete(); $table->dateTime('starts_at'); $table->dateTime('ends_at'); $table->string('status')->default('PENDING'); $table->text('reason')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->unique(['provider_id', 'starts_at']); $table->index(['patient_id', 'starts_at']);
        });
        Schema::create('appointment_status_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('appointment_id')->constrained()->cascadeOnDelete(); $table->string('from_status')->nullable(); $table->string('to_status'); $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); $table->text('reason')->nullable(); $table->timestamps();
        });
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id(); $table->foreignId('patient_id')->constrained(); $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('appointment_type_id')->nullable()->constrained()->nullOnDelete(); $table->date('preferred_date')->nullable(); $table->string('status')->default('WAITING'); $table->timestamps();
        });
        Schema::create('encounters', function (Blueprint $table) {
            $table->id(); $table->string('encounter_number')->unique(); $table->foreignId('patient_id')->constrained(); $table->foreignId('provider_id')->constrained(); $table->foreignId('appointment_id')->nullable()->unique()->constrained()->nullOnDelete(); $table->string('type'); $table->dateTime('started_at'); $table->dateTime('ended_at')->nullable(); $table->text('chief_complaint')->nullable(); $table->text('assessment')->nullable(); $table->text('plan')->nullable(); $table->text('discharge_instructions')->nullable(); $table->date('follow_up_date')->nullable(); $table->string('status')->default('OPEN'); $table->timestamps();
        });
        Schema::create('encounter_notes', function (Blueprint $table) {
            $table->id(); $table->foreignId('encounter_id')->constrained()->cascadeOnDelete(); $table->foreignId('author_id')->constrained('users'); $table->text('body'); $table->timestamps();
        });
        Schema::create('vitals', function (Blueprint $table) {
            $table->id(); $table->foreignId('encounter_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('patient_id')->constrained(); $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('blood_pressure')->nullable(); $table->unsignedSmallInteger('heart_rate')->nullable(); $table->unsignedSmallInteger('respiratory_rate')->nullable(); $table->decimal('temperature', 4, 1)->nullable(); $table->decimal('spo2', 5, 2)->nullable(); $table->decimal('weight', 6, 2)->nullable(); $table->unsignedTinyInteger('pain_score')->nullable(); $table->timestamp('recorded_at'); $table->timestamps();
        });
        Schema::create('clinical_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('patient_id')->constrained(); $table->foreignId('encounter_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('name'); $table->string('path'); $table->string('mime_type'); $table->timestamps();
        });
        Schema::create('telehealth_sessions', function (Blueprint $table) {
            $table->id(); $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete(); $table->string('zoom_meeting_id')->nullable()->unique(); $table->text('join_url')->nullable(); $table->text('host_start_url')->nullable(); $table->dateTime('start_time'); $table->unsignedSmallInteger('duration')->default(30); $table->string('status')->default('NOT_CONFIGURED'); $table->timestamps();
        });
        Schema::create('telehealth_participants', function (Blueprint $table) {
            $table->id(); $table->foreignId('telehealth_session_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('role'); $table->timestamp('joined_at')->nullable(); $table->timestamps();
        });
        Schema::create('er_visits', function (Blueprint $table) {
            $table->id(); $table->string('visit_number')->unique(); $table->foreignId('patient_id')->constrained(); $table->dateTime('arrived_at'); $table->string('arrival_method')->nullable(); $table->text('chief_complaint'); $table->text('referral_details')->nullable(); $table->string('status')->default('ARRIVED'); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['status', 'arrived_at']);
        });
        Schema::create('triage_assessments', function (Blueprint $table) {
            $table->id(); $table->foreignId('er_visit_id')->unique()->constrained()->cascadeOnDelete(); $table->foreignId('triage_nurse_id')->constrained('users'); $table->dateTime('triaged_at'); $table->text('chief_complaint')->nullable(); $table->unsignedTinyInteger('pain_score')->nullable(); $table->string('priority'); $table->text('notes')->nullable(); $table->string('status')->default('COMPLETE'); $table->timestamps();
        });
        Schema::create('triage_vitals', function (Blueprint $table) {
            $table->id(); $table->foreignId('triage_assessment_id')->constrained()->cascadeOnDelete(); $table->string('blood_pressure')->nullable(); $table->unsignedSmallInteger('heart_rate')->nullable(); $table->unsignedSmallInteger('respiratory_rate')->nullable(); $table->decimal('temperature', 4, 1)->nullable(); $table->decimal('spo2', 5, 2)->nullable(); $table->decimal('weight', 6, 2)->nullable(); $table->timestamps();
        });
        Schema::create('er_queue', function (Blueprint $table) {
            $table->id(); $table->foreignId('er_visit_id')->unique()->constrained()->cascadeOnDelete(); $table->string('priority'); $table->string('status')->default('WAITING'); $table->string('treatment_area')->nullable(); $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete(); $table->dateTime('queued_at'); $table->timestamps(); $table->index(['status', 'priority', 'queued_at']);
        });
        Schema::create('wards', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('name')->unique(); $table->string('type')->nullable(); $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); $table->foreignId('ward_id')->constrained()->cascadeOnDelete(); $table->string('number'); $table->string('type')->nullable(); $table->timestamps(); $table->unique(['ward_id', 'number']);
        });
        Schema::create('beds', function (Blueprint $table) {
            $table->id(); $table->foreignId('room_id')->constrained()->cascadeOnDelete(); $table->string('number'); $table->string('status')->default('AVAILABLE'); $table->timestamp('status_updated_at')->nullable(); $table->timestamps(); $table->unique(['room_id', 'number']); $table->index('status');
        });
        Schema::create('admissions', function (Blueprint $table) {
            $table->id(); $table->string('admission_number')->unique(); $table->foreignId('patient_id')->constrained(); $table->foreignId('er_visit_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('attending_provider_id')->nullable()->constrained('providers')->nullOnDelete(); $table->string('status')->default('REQUESTED'); $table->text('reason')->nullable(); $table->dateTime('admitted_at')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['patient_id', 'status']);
        });
        Schema::create('bed_reservations', function (Blueprint $table) {
            $table->id(); $table->foreignId('bed_id')->constrained(); $table->foreignId('admission_id')->constrained()->cascadeOnDelete(); $table->foreignId('reserved_by')->nullable()->constrained('users')->nullOnDelete(); $table->dateTime('expires_at')->nullable(); $table->string('status')->default('ACTIVE'); $table->timestamps(); $table->unique(['bed_id', 'status']);
        });
        Schema::create('bed_assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('admission_id')->constrained()->cascadeOnDelete(); $table->foreignId('bed_id')->constrained(); $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); $table->dateTime('assigned_at'); $table->dateTime('released_at')->nullable(); $table->string('status')->default('ACTIVE'); $table->timestamps(); $table->unique(['bed_id', 'status']); $table->index(['admission_id', 'status']);
        });
        Schema::create('patient_transfers', function (Blueprint $table) {
            $table->id(); $table->foreignId('admission_id')->constrained()->cascadeOnDelete(); $table->foreignId('from_bed_id')->nullable()->constrained('beds')->nullOnDelete(); $table->foreignId('to_bed_id')->constrained('beds'); $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete(); $table->dateTime('transferred_at'); $table->text('reason')->nullable(); $table->timestamps();
        });
        Schema::create('discharges', function (Blueprint $table) {
            $table->id(); $table->foreignId('admission_id')->unique()->constrained()->cascadeOnDelete(); $table->foreignId('authorized_by')->constrained('users'); $table->dateTime('discharged_at'); $table->string('reason')->nullable(); $table->string('disposition')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable'); $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action'); $table->string('resource_type'); $table->unsignedBigInteger('resource_id')->nullable(); $table->string('result')->default('SUCCESS'); $table->ipAddress('ip_address')->nullable(); $table->json('metadata')->nullable(); $table->timestamps(); $table->index(['resource_type', 'resource_id']); $table->index(['action', 'created_at']);
        });
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id(); $table->string('integration'); $table->string('event'); $table->string('status'); $table->json('metadata')->nullable(); $table->timestamps();
        });
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('method'); $table->string('path'); $table->unsignedSmallInteger('status_code'); $table->ipAddress('ip_address')->nullable(); $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['api_logs','integration_logs','audit_logs','notifications','discharges','patient_transfers','bed_assignments','bed_reservations','admissions','beds','rooms','wards','er_queue','triage_vitals','triage_assessments','er_visits','telehealth_participants','telehealth_sessions','clinical_documents','vitals','encounter_notes','encounters','waitlists','appointment_status_histories','appointments','appointment_slots','provider_schedules','appointment_types','patient_consents','emergency_contacts','patient_addresses','patient_identifiers','patients','provider_specialties','providers','specialties','departments','permission_role','role_user','permissions','roles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
