# Hospital Information Management System (HIMS)

A complete, modular, secure, responsive web-based **Hospital Information Management System** for **Patient Access & Care Coordination**, built with **Laravel + PHP + MySQL**.

**Local project folder:** `C:\laragon\www\coor`
**Canonical local URL:** `http://127.0.0.1:8000`
**Run command:** `php artisan serve --host=127.0.0.1 --port=8000`
**Database:** `coor`

> Note: The system itself is **HIMS**. Only the local project folder and database are named `coor`. For local web access, the canonical URL is the PHP dev server at `http://127.0.0.1:8000`; Laragon remains useful for MySQL, but the app should not be served through the Apache vhost when using this local dev flow.

---

## 1. Project Overview

HIMS integrates **five connected care-coordination modules** around one centralized patient identity:

1. **SPRS** – Smart Patient Registration System
2. **ASS** – Appointment and Scheduling System
3. **TOCS** – Telehealth and Outpatient Care System
4. **EERTS** – Emergency and ER Triage System
5. **IBMS** – Inpatient and Bed Management System

**Core principle:** One patient identity, one centralized patient record, five connected modules.

Example journey: Registration → MRN → Appointment/Emergency → Outpatient/Telehealth Encounter → ER Triage → Admission → Bed Assignment → Transfer → Discharge.

## 2. Features

- Patient registration with unique **MRN** generation (`MRN-2026-000001`)
- Smart duplicate-patient detection
- **Patient 360** view (demographics, appointments, encounters, ER, triage, admissions, bed history, discharges, audit)
- Patient web **pre-registration** portal for emergency or outpatient arrival preparation
- Arrival **check-in** workflow that pre-fills ER intake using a secure pre-arrival token
- **AI-assisted triage evaluation hook** that merges pre-arrival data with live vitals and complaint data
- Appointment booking with **double-booking prevention**
- Provider schedules, slots, cancellation, rescheduling, check-in, no-show
- Outpatient encounters, vitals, clinical notes, follow-up
- Telehealth sessions with secure room fallback, join-token validation, and optional Zoom integration
- ER arrival, triage, priority, ER queue
- Wards, rooms, beds, reservations, assignments, transfers, discharges
- **Transactional bed assignment** with concurrency protection
- Role-based authentication & authorization (6 roles)
- Audit logging, notifications, reports
- Versioned **REST API** (`/api/v1`) with Sanctum token auth
- Optional integrations: Google Maps, Gemini, Zoom, Zapier (feature-flagged)

### 2.1 Patient Web Pre-Registration Portal

The system includes a patient-facing portal flow for pre-arrival registration and intake preparation. Patients can self-register visit details, medical history, emergency contacts, and relevant background information at `/portal/pre-register` before arriving at the hospital. The portal creates a secure digital ticket with a QR code and token that can be used later by staff to rapidly locate and pre-populate a patient's static profile data.

This helps reduce redundant manual intake and ensures staff receive a structured pre-arrival snapshot before the patient reaches the ER or registration desk. The portal is intentionally scoped to the patient role and access is limited to the patient's own profile and pre-registration records, rather than hospital-wide operational data.

### 2.2 Staff Arrival Check-In

Staff can access a patient check-in flow at `/emergency/check-in/{token}` to look up a pre-arrival token and immediately pre-fill ER intake with the patient’s static profile information. This includes demographic details, emergency contact data, allergies, and relevant medical history from the pre-registration record.

The goal is to eliminate duplicate manual entry and shorten the initial intake time when a patient arrives for emergency or urgent assessment. This check-in step is intentionally paired with the triage workflow so staff can validate the patient record while moving directly into severity scoring and queue assignment.

### 2.3 AI-Assisted Triage Evaluation Hook

HIMS includes an AI-assisted triage decision-support hook that combines:

- pre-arrival profile information (allergies, chronic conditions, prior history, existing risk factors),
- staff-entered patient vitals,
- the triage chief complaint and symptom details,
- contextual evaluation factors such as urgency and severity indicators.

This logic is executed through `AiTriageService`, which is intentionally a **rule-based clinical decision support engine** rather than a trained machine learning model. It uses keyword matching, symptom pattern detection, and vital-sign thresholds to generate a severity score and explainable contributing factors. It is not an autonomous diagnosis engine and it does not replace clinical judgment.

The project intentionally avoids a naïve ML approach because there is not yet enough historical, labeled triage data to train a reliable model. The current architecture supports a future upgrade to real ML inference once a sufficiently large and validated data set exists. The current system is designed for transparency and human oversight: the nurse or doctor sees the severity score, the reason factors, and a clear explanation panel, then must explicitly confirm or revise the final triage assessment before it is considered complete.

## 3. Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript, Laravel Blade, Vite, Tailwind CSS, Alpine.js
- **Backend:** PHP, Laravel, Eloquent ORM, Controllers, Services, Form Requests, Middleware, Policies
- **Database:** MySQL (InnoDB), foreign keys, indexes, transactions
- **Auth:** Session-based (Laravel starter kit) + Sanctum tokens for API
- **API:** RESTful JSON, versioned `/api/v1`
- **Tools:** Laragon (MySQL), Git, Composer, Node/NPM, Postman

## 4. Requirements

- [Laragon](https://laragon.org/) (PHP 8.3, MySQL 8.x, Apache/Nginx)
- Composer 2.x
- Node.js 20+ / NPM
- Git
- Postman (for API testing)

## 5. Laragon Setup

1. Install and start **Laragon**.
2. Start **MySQL** and any required local services from Laragon.
3. Place the project at `C:\laragon\www\coor`.
4. For local app access, use the PHP dev server, not the Apache vhost: `php artisan serve --host=127.0.0.1 --port=8000`.

> Laragon remains useful for the database and local environment management, but the application itself should be served through the Laravel dev server on `http://127.0.0.1:8000` to avoid session/cookie mismatches and follow the canonical local setup.

## 6. Installation

```bash
cd C:\laragon\www\coor
```

## 7. Composer Commands

```bash
composer install
```

If Composer is not on PATH (Laragon), use:
```bash
php "C:\laragon\bin\composer\composer.phar" install
```

## 8. NPM Commands

```bash
npm install
npm run build        # production-style local asset build
# npm run dev        # during frontend development
```

## 9. Database Setup

1. Open Laragon → **Database** (or phpMyAdmin/HeidiSQL).
2. Create a database named **`coor`** (utf8mb4).
3. Keep MySQL running locally while the app is served via `php artisan serve`.

## 10. `.env` Configuration

Copy `.env.example` to `.env` and configure:

```env
APP_NAME=HIMS
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coor
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log        # local mail capture

# Optional integrations (disabled by default)
GOOGLE_MAPS_ENABLED=false
GOOGLE_MAPS_API_KEY=
GEMINI_ENABLED=false
GEMINI_API_KEY=
ZOOM_ENABLED=false
ZOOM_ACCOUNT_ID=
ZOOM_CLIENT_ID=
ZOOM_CLIENT_SECRET=
ZAPIER_ENABLED=false
ZAPIER_WEBHOOK_URL=
```

Never commit `.env` (it is git-ignored). Secrets are read from `.env` only.

For production, copy `.env.production.example` to `.env` and set real values.

## 10.1 Production Configuration

Copy the production template and update it with real values:

```bash
copy .env.production.example .env
```

Set production values for:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-production-domain.com`
- `SESSION_ENCRYPT=true`
- `SESSION_DOMAIN=your-production-domain.com`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_SAME_SITE=strict`
- `MAIL_MAILER=smtp`
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`

## 10.2 Production Deployment

Run these commands before going live:

```bash
npm install
npm run build
php artisan key:generate --ansi
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize --force
```

## 11. Migration Instructions

```bash
php artisan key:generate
php artisan migrate
```

## 12. Seeder Instructions

```bash
php artisan db:seed
```

This seeds fictional demo data: 6 roles, permissions, multiple staff and patient users, 5 departments, providers, 4+ patients, appointment types, wards/rooms/beds, appointments, ER visits, triage records, admissions, and bed assignments.

**Demo login (password for all):** `Password123!`

| Role | Email |
|------|-------|
| Super Admin | `super.admin@coor.test` |
| Hospital Admin | `hospital.admin@coor.test` |
| Registration Staff | `registration@coor.test` |
| Doctor | `doctor@coor.test` |
| Nurse | `nurse@coor.test` |
| Patient | `patient@coor.test` |

The current RBAC audit confirms that patients are restricted to their own portal/profile and pre-registration access, while nurse/doctor/admin users can access the staff-level triage, check-in, reports, and audit features appropriate to their role. Audit logs remain restricted to the super-admin and hospital-admin roles, not all doctors or nurses.

## 13. Authentication

- Session-based login for the web UI.
- Token-based (Sanctum) auth for the REST API.
- 6 roles: Super Admin, Hospital Admin, Registration Staff, Doctor, Nurse, Patient.
- Permissions mapped to roles via `role_user` / `permission_role` pivot tables.
- Server-side authorization via Policies, Middleware, and Gates (a user manually entering a URL is still blocked if unauthorized).
- Patient role access is intentionally scoped to the patient-only portal and own record flow.
- Nurse/doctor/admin roles are scoped to staff workflows such as ER triage, patient check-in, reporting, and operational oversight; audit log access is restricted to super-admin and hospital-admin users.

## 14. API Documentation

Base URL: `http://127.0.0.1:8000/api/v1`

**Authentication**

```
POST /api/v1/auth/login       { "email": "...", "password": "..." }
POST /api/v1/auth/logout      (Bearer token)
GET  /api/v1/auth/me          (Bearer token)
```

All other endpoints require header `Authorization: Bearer <token>`.

- Patients: `GET/POST /api/v1/patients`, `GET/PUT/PATCH/DELETE /api/v1/patients/{id}`, `GET /api/v1/patients/search/{term}`
- Appointments: `GET/POST /api/v1/appointments`, `GET/PATCH/DELETE /api/v1/appointments/{id}`, `POST .../cancel|reschedule|check-in`
- Providers / Departments / Schedules: `GET /api/v1/providers`, `GET /api/v1/departments`, `GET /api/v1/schedules`, `GET /api/v1/schedules/{providerId}/slots`
- Encounters: `GET/POST /api/v1/encounters`
- Telehealth: `GET/POST /api/v1/telehealth`, `POST /api/v1/telehealth/{id}/start`, `POST /api/v1/telehealth/{id}/cancel`, `POST /api/v1/telehealth/{id}/closeout`, `POST /api/v1/telehealth/{id}/end`
- Emergency: `GET /api/v1/emergency/queue`, `POST /api/v1/emergency/visits`, `POST /api/v1/emergency/{id}/triage`
- Wards/Rooms/Beds: `GET /api/v1/wards`, `GET /api/v1/rooms`, `GET /api/v1/beds`, `GET /api/v1/beds/available`, `POST /api/v1/beds/{id}/reserve|assign|release`
- Admissions: `GET/POST /api/v1/admissions`, `POST /api/v1/admissions/{id}/transfer`, `POST /api/v1/admissions/{id}/discharge`
- Notifications: `GET /api/v1/notifications`, `POST /api/v1/notifications/{id}/read`

**Response format**

```json
{ "success": true, "message": "...", "data": {} }
```

Validation errors return HTTP 422; unauthorized 401; forbidden 403; conflicts (double-booking/bed) 409.

## 15. Postman Instructions

1. Import the Postman collection (see `postman/HIMS_API.postman_collection.json` if present, or construct requests manually).
2. Set the base URL variable to `http://127.0.0.1:8000/api/v1`.
3. Call `POST /auth/login` → copy the returned `token`.
4. Set a `token` collection variable and enable it in the `Authorization: Bearer {{token}}` header.
5. Test each module (Patients, Appointments, ER, Triage, Beds, Admissions, Transfers, Discharges).

## 16–19. External Integrations

Integrations are **optional** and isolated behind service classes. When disabled (`..._ENABLED=false`), core HIMS workflows continue normally and the UI shows a clear **"Not Configured"** status. No fake external calls are made.

### 16. Telehealth secure-room flow
`app/Models/TelehealthSession.php` + `app/Services/TelehealthService.php` — generate a secure join URL even when Zoom is not configured. Sessions create a local meeting room with a signed token and only expose the join URL to authorized participants. Lifecycle transitions include scheduled, active/ongoing, completed, and cancelled states.

### 17. Google Maps
`app/Services/GoogleMapsService.php` — address autocomplete, hospital location. Set `GOOGLE_MAPS_API_KEY` and `GOOGLE_MAPS_ENABLED=true`.

### 18. Zoom
`app/Services/ZoomService.php` + `TelehealthService` — creates telehealth meetings. Set `ZOOM_ACCOUNT_ID`, `ZOOM_CLIENT_ID`, `ZOOM_CLIENT_SECRET`, `ZOOM_ENABLED=true`. Patients receive the join URL only; host secrets are never exposed in the frontend.

### 18. Gemini
`app/Services/GeminiService.php` — assistive, non-clinical assistance only. Set `GEMINI_API_KEY`, `GEMINI_ENABLED=true`. Gemini never autonomously diagnoses, prescribes, or determines ER priority.

### 19. Zapier
`app/Services/ZapierService.php` — operational webhook automation. Set `ZAPIER_WEBHOOK_URL`, `ZAPIER_ENABLED=true`. MySQL remains the source of truth.

## 20. Testing

Run automated tests (if present under `tests/`):

```bash
php artisan test
```

Current verified status: **79 tests passing, 345 assertions, 0 failures**.

The suite includes dedicated coverage for the current HIMS workflows, including:

- `PortalPreRegistrationTest`
- `ArrivalCheckInTest`
- `TriageAiEvaluationHookTest`
- `RbacMatrixTest`

Critical workflows covered: patient registration/search/duplicate detection, portal pre-registration, arrival check-in, AI triage evaluation, RBAC enforcement, appointment booking + double-booking prevention, ER registration + triage + queue, bed availability/reservation/assignment concurrency, transfer, discharge, and API auth/authorization/validation.

## 21. Git / GitHub Workflow

```bash
git init
git add .
git commit -m "Initial HIMS"
git branch -M main
git remote add origin <your-repo-url>
git push -u origin main
```

Suggested branches: `main`, `develop`, `feature/...`. **Never commit `.env`, `.env.backup`, `.env.production`, API keys, passwords, or private credentials.**

## 22. Security Notes

- Passwords hashed (Laravel `hashed` cast).
- CSRF protection, input validation (Form Requests), output escaping, SQL injection protection (Eloquent).
- Role/permission-based authorization enforced server-side via Policies, Middleware, and Gates.
- Sanctum token auth for the API with per-route permission checks.
- Secrets stored only in `.env` (git-ignored).
- Audit logging for sensitive actions.
- Friendly error messages (no raw stack traces/database credentials exposed to users).

## 23. Privacy Notes

- Designed for data minimization, access control, accountability, and auditability.
- Sensitive clinical data is restricted to authorized roles.
- Prefer de-identified/non-sensitive data when using external AI services.
- No real patient data is used in seed/demo data.

## 24. Known Limitations

- External integrations (Maps, Gemini, Zoom, Zapier) require real credentials and are disabled by default on localhost.
- Mail uses the `log` driver locally (no production SMTP required).
- Production deployment requires configuring real domains, HTTPS, SMTP, queue/storage, and security hardening — a configuration change, not an application rewrite.
- AI-assisted triage is currently **rule-based rather than a trained ML model** because the project does not yet have enough historical, labeled triage data to support reliable model training. The architecture supports upgrading to a real ML-based inference service once sufficient data is collected.
- The current triage engine relies on keyword matching, symptom pattern recognition, and vital-sign thresholds; it is intended as explainable clinical decision support rather than autonomous diagnosis.

---

## Localhost Setup (Quick Reference)

```bash
cd C:\laragon\www\coor
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000`.

This is the canonical local URL for the app. The app should be served with `php artisan serve --host=127.0.0.1 --port=8000`; Laragon may still be used for MySQL, but not as the primary web-serving method for this local developer setup.
