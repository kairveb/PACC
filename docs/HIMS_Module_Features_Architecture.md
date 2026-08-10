# HIMS — Hospital Information Management System
## Module Features & System Architecture Documentation

**System Acronym:** HIMS (Hospital Information Management System)  
**Version:** 2026.08  
**Tech Stack:** Laravel 11 (Backend API/Web) + Blade + Vanilla JS (Frontend)  
**Database:** MySQL 8.4.3 (Laragon)  
**Runtime:** PHP 8.3.33

---

## System Overview

HIMS is a modular, role-secure hospital management system covering the full patient lifecycle from registration through discharge. The product connects registration, scheduling, outpatient encounters, emergency triage, telehealth, and inpatient/bed management around one centralized patient identity and audit trail.

### Core Database Tables: ~51 Total
- Patient & Demographics (5+ tables): `patients`, `patient_addresses`, `patient_identifiers`, `patient_consents`, `emergency_contacts`
- Appointments & Scheduling (5+ tables): `appointments`, `appointment_types`, `appointment_slots`, `appointment_status_histories`, `provider_schedules`, `waitlists`
- Clinical & Encounters (4+ tables): `encounters`, `encounter_notes`, `vitals`, `clinical_documents`
- Emergency & Triage (4+ tables): `er_visits`, `er_queue`, `triage_assessments`, `triage_vitals`
- Inpatient Management (6+ tables): `wards`, `rooms`, `beds`, `bed_assignments`, `bed_reservations`, `admissions`, `discharges`, `patient_transfers`
- Providers & Departments (4+ tables): `providers`, `provider_specialties`, `specialties`, `departments`
- Telehealth (2 tables): `telehealth_sessions`, `telehealth_participants`
- Authorization (users & RBAC): `users`, `roles`, `permissions`, `permission_role`, `role_user`
- Audit & Logs (3 tables): `audit_logs`, `api_logs`, `integration_logs`
- Infrastructure & Jobs (several): `migrations`, `jobs`, `cache`, `cache_locks`, `sessions`, `notifications`, `personal_access_tokens`, `password_resets`

---

## Role-Based Access Control (RBAC)

HIMS uses permission-based RBAC implemented with roles and permission mappings. Roles are enforced in routes and controllers via middleware and policies.

### Roles & Responsibilities
- **Super Admin:** full system administration, user/role management, audit exports
- **Hospital Admin:** hospital configuration, reporting, user and facility oversight
- **Registration / Front Desk:** patient registration, appointment booking and check-in
- **Doctor:** clinical access for encounters, prescriptions, telehealth consultations
- **Nurse:** triage, vitals recording, bed/ward management, nursing workflows
- **Patient:** self-service for own appointments, telehealth joining, basic profile

### Representative Permissions
- `manage-users`, `manage-roles`
- `view-patients`, `create-patients`, `update-patients`, `verify-patients`
- `view-appointments`, `create-appointments`, `update-appointments`, `cancel-appointments`
- `view-encounters`, `create-encounters`, `update-encounters`
- `view-er`, `create-er-visits`, `triage-patients`, `triage-score`
- `view-beds`, `manage-beds`, `view-admissions`, `manage-admissions`
- `view-reports`, `view-audit-logs`
- `view-telehealth`, `start-telehealth`, `join-telehealth`

---

## 1. Patient Management Module

**Route Prefix:** `/api/v1/patients`, web controllers under `routes/web.php`  
**Primary Tables:** `patients`, `patient_addresses`, `patient_identifiers`, `patient_consents`, `emergency_contacts`  
**Access Control:** Registration (create), Doctors/Nurses (view), Admins (manage/delete)

### Core Features
- Patient registration with unique MRN generation and duplicate detection (`POST /api/v1/patients`)
- Search & filter by name, MRN, phone, and date ranges (`GET /api/v1/patients` and search routes)
- Patient 360 detail including addresses, contacts, consents, appointments and audit trail (`GET /api/v1/patients/{id}`)
- Patient verification workflow for appointment eligibility (`POST /patients/{patient}/verify` on web routes)
- Emergency contact and multiple address support with optional geocoding
- Consent recording for procedures and data sharing

### Data Highlights
- `mrn` unique, `verified` flag for appointment eligibility, `user_id` to link patient accounts for self-service

---

## 2. Appointment & Scheduling Module

**Route Prefix:** `/api/v1/appointments`, web routes under `routes/web.php`  
**Primary Tables:** `appointments`, `appointment_types`, `appointment_slots`, `appointment_status_histories`, `provider_schedules`, `waitlists`

### Core Features
- Book, list, and view appointments with provider, department, type (telehealth/in-person) (`POST /api/v1/appointments`, `GET /api/v1/appointments`)
- Provider schedule and slot generation (slot APIs under `provider-schedules` and `appointments/slots/json`)
- Double-booking prevention enforced at schedule/slot level and in controllers
- Check-in, cancel, reschedule, and mark no-show flows (`/check-in`, `/cancel`, `/reschedule`, `/no-show` endpoints)
- Waitlist support for full schedules
- Appointment status history tracking via `appointment_status_histories`

### Model Notes
- Appointments are timeboxed (`starts_at` / `ends_at`), linked to `patient_id`, `provider_id`, `department_id`, and `appointment_type_id`.

---

## 3. Encounter & Clinical Documentation Module

**Route Prefix:** `/api/v1/encounters`  
**Primary Tables:** `encounters`, `encounter_notes`, `vitals`, `clinical_documents`

### Core Features
- Create and manage encounters for appointments or walk-ins (`POST /api/v1/encounters`)
- Record vitals (BP, HR, RR, SpO₂, temperature, weight) linked to encounters or triage
- Provider notes, assessment, plan, and follow-up scheduling (`encounter_notes`)
- Attach clinical documents (images, lab results) and store metadata in `clinical_documents`
- Encounter lifecycle status (OPEN → COMPLETED) with timestamps

---

## 4. Emergency Department & Triage Module

**Route Prefix:** `/api/v1/emergency`, `/api/v1/triage`  
**Primary Tables:** `er_visits`, `er_queue`, `triage_assessments`, `triage_vitals`

### Core Features
- ER visit intake with arrival method and immediate triage (`POST /api/v1/emergency/visits`)
- Triage assessment capture with vitals and ESI-based acuity scoring (`POST /api/v1/triage/score`)
- ER queue sorted by acuity with nurse-facing queue management (`GET /api/v1/emergency/queue`)
- Reassessment and triage-level updates (`PATCH /api/v1/triage-assessments/{id}`)
- Integration with bed assignment to move critical patients to inpatient/isolation beds

---

## 5. Telehealth & Remote Care Module

**Route Prefix:** `/api/v1/telehealth`  
**Primary Tables:** `telehealth_sessions`, `telehealth_participants`

### Core Features
- Telehealth-capable appointment type and scheduling
- Create, start, join, and end telehealth sessions with participant tracking (`POST /api/v1/telehealth`, `/start`, `/end`, `/participants` endpoints)
- Session metadata and optional recording URL storage
- Zoom integration configurable via environment feature flags

---

## 6. Inpatient & Bed Management Module

**Route Prefix:** `/api/v1/beds`, `/api/v1/admissions`  
**Primary Tables:** `wards`, `rooms`, `beds`, `bed_assignments`, `bed_reservations`, `admissions`, `discharges`, `patient_transfers`

### Core Features
- Ward/room inventory and bed status dashboard (`GET /api/v1/wards`, `GET /api/v1/beds`)
- Transactional bed assignment & reservation (`POST /api/v1/beds/{id}/assign`, `/reserve`, `/release`)
- Admission intake, approval, admit, transfer, and discharge flows (`/admissions` endpoints)
- Bed status lifecycle management (AVAILABLE → OCCUPIED → CLEANING → AVAILABLE)
- Occupancy and utilization reporting per ward/date

---

## 7. Provider & Department Management

**Route Prefix:** `/api/v1/providers`, `/api/v1/provider-schedules`  
**Primary Tables:** `providers`, `provider_specialties`, `specialties`, `departments`, `provider_schedules`

### Core Features
- Provider registry with license and department assignment
- Specialty assignment and multi-specialty support
- Schedule setup with daily windows, breaks, and slot generation
- Availability endpoints used by appointment booking flows

---

## 8. Audit, Logs & Compliance

**Tables:** `audit_logs`, `api_logs`, `integration_logs`  
**Access Control:** Admins only

### Features
- API request logging via middleware (user, endpoint, payload summary, response code)
- Change history logging for sensitive resources via `audit_logs`
- Integration logging for outbound services
- Exportable audit data for compliance workflows (report endpoints exist but can be extended)

---

## 9. System Infrastructure & Architecture

### API Architecture
- Framework: Laravel 11 with versioned API under `/api/v1`  
- Auth: Session-based web auth + Laravel Sanctum for API tokens  
- Authorization: Policy + permission middleware  
- Response format: JSON envelope with predictable `data` payloads

### Database
- MySQL 8.4.3 (Laragon) with InnoDB, foreign keys, and indexed MRN/appointment timestamps  
- Soft deletes used where auditability is required

### Frontend
- Laravel Blade server-rendered views with minimal Vanilla JS  
- Tailwind CSS is present in the repo and used for styling

### Middleware & Policies
- Authentication, verification, MFA enforcement, and permission checks are implemented in middleware and policies (e.g., `PatientPolicy`, `AppointmentPolicy`).

---

## 10. Tests, Commands & Dev Notes

### Tests
- PHPUnit feature tests cover core flows (RBAC, appointments, admissions)  
- `php artisan test --filter=RbacMatrixTest` for RBAC-specific checks

### Common Artisan Commands
```
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed --class=HimsSeeder
php artisan key:generate
```

### Local Environment
- PHP 8.3.33 (Laragon pack), MySQL 8.4.3, project accessible at `http://coor.test` locally

---

## Implementation Notes & Roadmap

### Implemented (Core) ✅
- Single patient index with MRN generation, appointment scheduling, telehealth framework, ER triage, bed management, and role-based control.

### Planned Enhancements 🚀
- Billing & claims, E-prescription, Lab & imaging integration (HL7/FHIR), patient portal, advanced analytics, mobile app, real-time notifications, and stronger rate-limiting for API traffic.

---

Document Version: 2026.08  
Generated: 2026-08-10
