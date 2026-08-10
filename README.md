# Hospital Information Management System (HIMS)

A complete, modular, secure, responsive web-based **Hospital Information Management System** for **Patient Access & Care Coordination**, built with **Laravel + PHP + MySQL**.

**Local project folder:** `C:\laragon\www\coor`
**Local URL:** `http://coor.test` (or `http://127.0.0.1:8000`)
**Database:** `coor`

> Note: The system itself is **HIMS**. Only the local project folder/database/domain are named `coor`.

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
- Appointment booking with **double-booking prevention**
- Provider schedules, slots, cancellation, rescheduling, check-in, no-show
- Outpatient encounters, vitals, clinical notes, follow-up
- Telehealth sessions with Zoom integration (optional)
- ER arrival, triage, priority, ER queue
- Wards, rooms, beds, reservations, assignments, transfers, discharges
- **Transactional bed assignment** with concurrency protection
- Role-based authentication & authorization (6 roles)
- Audit logging, notifications, reports
- Versioned **REST API** (`/api/v1`) with Sanctum token auth
- Optional integrations: Google Maps, Gemini, Zoom, Zapier (feature-flagged)

## 3. Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript, Laravel Blade, Vite, Tailwind CSS, Alpine.js
- **Backend:** PHP, Laravel, Eloquent ORM, Controllers, Services, Form Requests, Middleware, Policies
- **Database:** MySQL (InnoDB), foreign keys, indexes, transactions
- **Auth:** Session-based (Laravel starter kit) + Sanctum tokens for API
- **API:** RESTful JSON, versioned `/api/v1`
- **Tools:** Laragon, Git, Composer, Node/NPM, Postman

## 4. Requirements

- [Laragon](https://laragon.org/) (PHP 8.3, MySQL 8.x, Apache/Nginx)
- Composer 2.x
- Node.js 20+ / NPM
- Git
- Postman (for API testing)

## 5. Laragon Setup

1. Install and start **Laragon**.
2. Click **Start All** (Apache/Nginx + MySQL).
3. Place the project at `C:\laragon\www\coor`.
4. Laragon auto-configures `http://coor.test` (add project via the Laragon menu if needed).

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

## 10. `.env` Configuration

Copy `.env.example` to `.env` and configure:

```env
APP_NAME=HIMS
APP_ENV=local
APP_URL=http://coor.test

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

This seeds fictional demo data: 6 roles, permissions, 10 users, 5 departments, providers, 4+ patients, appointment types, wards/rooms/beds (18 beds), appointments, ER visits, triage, admissions, and bed assignments.

**Demo login (password for all):** `Password123!`

| Role | Email |
|------|-------|
| Super Admin | `super.admin@coor.test` |
| Hospital Admin | `hospital.admin@coor.test` |
| Registration | `registration@coor.test` |
| Doctor | `doctor@coor.test` |
| Nurse | `nurse@coor.test` |
| Patient | `patient@coor.test` |

## 13. Authentication

- Session-based login for the web UI.
- Token-based (Sanctum) auth for the REST API.
- 6 roles: Super Admin, Hospital Admin, Registration Staff, Doctor, Nurse, Patient.
- Permissions mapped to roles via `role_user` / `permission_role` pivot tables.
- Server-side authorization via Policies, Middleware, and Gates (a user manually entering a URL is still blocked if unauthorized).

## 14. API Documentation

Base URL: `http://coor.test/api/v1` (or `http://127.0.0.1:8000/api/v1`)

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
- Telehealth: `GET/POST /api/v1/telehealth`
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
2. Set the base URL variable to `http://coor.test/api/v1`.
3. Call `POST /auth/login` → copy the returned `token`.
4. Set a `token` collection variable and enable it in the `Authorization: Bearer {{token}}` header.
5. Test each module (Patients, Appointments, ER, Triage, Beds, Admissions, Transfers, Discharges).

## 16–19. External Integrations

Integrations are **optional** and isolated behind service classes. When disabled (`..._ENABLED=false`), core HIMS workflows continue normally and the UI shows a clear **"Not Configured"** status. No fake external calls are made.

### 16. Google Maps
`app/Services/GoogleMapsService.php` — address autocomplete, hospital location. Set `GOOGLE_MAPS_API_KEY` and `GOOGLE_MAPS_ENABLED=true`.

### 17. Zoom
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

Critical workflows covered: patient registration/search/duplicate detection, appointment booking + double-booking prevention, ER registration + triage + queue, bed availability/reservation/assignment concurrency, transfer, discharge, and API auth/authorization/validation.

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
```

Open `http://coor.test` (or `php artisan serve` → `http://127.0.0.1:8000`).
