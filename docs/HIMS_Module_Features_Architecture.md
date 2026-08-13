# HIMS — Hospital Information Management System
## Module Features and Current System Architecture

**System Acronym:** HIMS (Hospital Information Management System)  
**Version:** 2026.08.13  
**Current Status:** Functional implementation of the core hospital management modules  
**Technology Stack:** Laravel 11, Blade, Vanilla JavaScript, Tailwind CSS, MySQL  
**Database:** MySQL 8.x (Laragon environment)  
**Runtime:** PHP 8.3.x

---

## 1. Executive Summary

The HIMS platform implemented in this workspace constitutes an operational hospital information system for patient access, care coordination, emergency management, and inpatient services. The system is structured around a centralized patient identity and integrates multiple functional domains, including patient registration, appointment scheduling, outpatient encounter management, telehealth services, emergency triage, and inpatient bed management.

The architecture is based on a modular service-oriented design implemented using Laravel, with a role-based web interface and a versioned REST API. The system is designed to support multi-role access across administrative, clinical, and patient workflows while preserving operational controls such as authorization enforcement, auditability, and structured reporting.

---

## 2. System Scope and Functional Modules

The current implementation is aligned to five principal operational modules:

- SPRS — Smart Patient Registration System
- ASS — Appointment and Scheduling System
- TOCS — Telehealth and Outpatient Care System
- EERTS — Emergency and ER Triage System
- IBMS — Inpatient and Bed Management System

These modules are integrated through a shared patient record and a common clinical workflow model, enabling continuity from registration to consultation, triage, admission, transfer, and discharge.

---

## 3. Current Implementation Status

### 3.1 Core functionality implemented
- Patient registration, MRN generation, search, lookup, verification, and consolidated patient records
- Appointment creation, scheduling, waitlist management, slot generation, and status transitions
- Encounter creation, documentation, vitals capture, and clinical notes
- Emergency visit intake, queue management, triage assessment, and clinical prioritization
- Telehealth session management, participant tracking, reminders, prescription handling, and closeout procedures
- Ward, room, and bed inventory management, reservation, assignment, transfer, and discharge workflows
- Operational dashboards, audit logging, notifications, and reporting mechanisms
- Role-based access control enforced through middleware and authorization policies
- Versioned REST API under `/api/v1` using Sanctum authentication

### 3.2 Areas outside the present core implementation scope
- Billing and claims management
- E-prescription lifecycle management
- HL7/FHIR interoperability for laboratory and imaging systems
- Patient portal self-service beyond basic profile and appointment visibility
- Advanced analytics functionality beyond the current reporting layer
- Real-time multi-user collaborative clinical tooling beyond the implemented operational workflows

---

## 4. Role-Based Access Control (RBAC)

The system implements permission-based RBAC through role assignments, authorization middleware, and policy enforcement. This supports a multi-role care model in which access is constrained according to operational responsibility rather than unrestricted administrative access.

### 4.1 Current roles
- **Super Admin** — full administrative control, user management, audit visibility, and reporting access
- **Hospital Admin** — operational oversight, reporting, configuration, and administrative review
- **Registration / Front Desk** — patient registration, lookup, and appointment processing
- **Doctor** — encounter review, outpatient management, ER queue oversight, and telehealth consultation workflows
- **Nurse** — triage operations, ER queue management, admission workflows, and bed coordination
- **Patient** — profile access, appointment visibility, notification access, and telehealth participation

### 4.2 Representative permissions
- `manage-users`, `manage-roles`
- `view-patients`, `create-patients`, `update-patients`, `delete-patients`
- `view-appointments`, `create-appointments`, `update-appointments`, `cancel-appointments`, `delete-appointments`
- `view-encounters`, `create-encounters`
- `view-er`, `create-er-visits`, `triage-patients`
- `view-beds`, `manage-beds`, `view-admissions`, `manage-admissions`
- `view-telehealth`, `start-telehealth`, `join-telehealth`
- `view-reports`, `view-audit-logs`

The permission model is reflected in the current route middleware definitions in the Laravel API and web routing layers.

---

## 5. Functional Architecture by Module

## 5.1 Patient Management Module

**Current route scope:** `/api/v1/patients` and the corresponding web patient management routes  
**Primary tables:** `patients`, `patient_addresses`, `patient_identifiers`, `patient_consents`, `emergency_contacts`

### Functional capabilities
- Patient registration with unique MRN generation and duplicate-detection logic
- Search and lookup by patient name, MRN, and contact-related identifiers
- Consolidated patient record view presenting demographic, appointment, encounter, ER, and admission context
- Verification workflow for appointment eligibility
- Support for emergency contact records and multi-address patient data
- Consent and clinical metadata support within the patient record model

### Assessment
This module is a foundational component of the current system and is fully integrated into both the web application and API services.

---

## 5.2 Appointment and Scheduling Module

**Current route scope:** `/api/v1/appointments`, `/api/v1/schedules`, `/api/v1/provider-schedules`, `/api/v1/waitlists`  
**Primary tables:** `appointments`, `appointment_types`, `appointment_slots`, `appointment_status_histories`, `provider_schedules`, `waitlists`

### Functional capabilities
- Appointment creation, listing, and patient/provider/department association
- Provider schedule generation and slot availability management
- Waitlist handling for full or unavailable schedules
- Check-in, cancellation, rescheduling, and no-show workflows
- Appointment status tracking and role-based operational handling
- Prevention of double-booking at the scheduling and booking logic layer

### Assessment
This module is operational and integrated into the system’s patient access and workflow logic.

---

## 5.3 Encounter and Clinical Documentation Module

**Current route scope:** `/api/v1/encounters`, patient clinical document endpoints, and encounter note endpoints  
**Primary tables:** `encounters`, `encounter_notes`, `vitals`, `clinical_documents`

### Functional capabilities
- Encounter creation for scheduled and walk-in patient visits
- Capture of clinical vitals associated with encounter and triage activities
- Documentation of provider notes, assessments, treatment planning, and follow-up details
- Attachment and retrieval of clinical documents and diagnostic files
- Workflow support for encounter lifecycle management

### Assessment
The encounter module provides the core clinical documentation layer required for outpatient and inpatient care continuity.

---

## 5.4 Emergency Department and Triage Module

**Current route scope:** `/api/v1/emergency`, `/api/v1/triage`  
**Primary tables:** `er_visits`, `er_queue`, `triage_assessments`, `triage_vitals`

### Functional capabilities
- ER intake and arrival capture
- Queue-based patient tracking with status updates
- Triage assessment, acuity evaluation, and priority classification
- Nurse-facing queue review and provider-facing queue progression
- Coordination with admission and inpatient bed allocation pathways

### Assessment
This is one of the most operationally complete modules in the system and is directly supported by both the web interface and API layer.

---

## 5.5 Telehealth and Remote Care Module

**Current route scope:** `/api/v1/telehealth` and participant-related endpoints  
**Primary tables:** `telehealth_sessions`, `telehealth_participants`

### Functional capabilities
- Telehealth consultation session management
- Participant tracking for remote care visits
- Session lifecycle actions including start, reminder, prescription, closeout, and end
- Optional Zoom integration support through feature flags
- Integration with appointment and encounter workflows

### Assessment
The telehealth module is implemented as a functional remote-care component of the broader patient workflow model.

---

## 5.6 Inpatient and Bed Management Module

**Current route scope:** `/api/v1/wards`, `/api/v1/rooms`, `/api/v1/beds`, `/api/v1/admissions`  
**Primary tables:** `wards`, `rooms`, `beds`, `bed_assignments`, `bed_reservations`, `admissions`, `discharges`, `patient_transfers`

### Functional capabilities
- Inventory and status management for wards, rooms, and beds
- Bed availability filtering, reservation, assignment, and release actions
- Admission intake and approval workflow
- Transfer and discharge handling
- Inpatient operational visibility and ward-focused workflow support

### Assessment
This module represents a fully implemented inpatient operational workflow and is not limited to presentation-only functionality.

---

## 5.7 Provider and Department Management

**Current route scope:** `/api/v1/providers`, `/api/v1/departments`, `/api/v1/schedules`  
**Primary tables:** `providers`, `provider_specialties`, `specialties`, `departments`, `provider_schedules`

### Functional capabilities
- Provider registry and department assignment management
- Schedule creation and slot generation
- Availability support for appointment booking logic
- Provider and department retrieval endpoints for system coordination

### Assessment
This component is integrated with the scheduling and appointment workflow and supports care-delivery coordination across departments.

---

## 5.8 Reporting, Audit, Logs, and Notifications

**Current route scope:** reporting views, audit-log interfaces, and notification APIs  
**Primary tables:** `audit_logs`, `api_logs`, `integration_logs`, `notifications`

### Functional capabilities
- Operational dashboard data and summary metrics
- Functional reporting for patients, appointments, encounters, ER visits, beds, and telehealth
- Administrative access to audit records
- Notification management and read-status updates
- Structured system activity tracking for governance and operational oversight

### Assessment
These support layers form the monitoring and accountability framework for ongoing system operations.

---

## 6. System Architecture

### 6.1 API architecture
- Laravel 11 application structure with versioned API endpoints under `/api/v1`
- Sanctum-based authentication for API access
- Session-based authentication for the web application
- Policy-based and middleware-based authorization mechanisms
- JSON response structures for application integration and frontend consumption

### 6.2 Data model and persistence
- MySQL relational schema supporting patient, provider, scheduling, clinical, ER, telehealth, and inpatient records
- Foreign-key-based entity relationships and transactional workflow patterns
- Audit-friendly recordkeeping through logging and status-tracking entities

### 6.3 Frontend architecture
- Blade-based server-rendered views with Tailwind CSS styling
- Minimal JavaScript usage combined with structured dashboard interfaces
- Role-specific operational views for clinical, administrative, and patient interaction flows

---

## 7. Current Operational Reality

The current codebase reflects a working hospital coordination platform covering the core operational processes required for patient-centric care management. The implemented architecture provides a practical foundation for a healthcare information system with integrated patient administration, scheduling, clinical documentation, emergency management, telehealth services, and inpatient logistics.

The system therefore represents an operational platform for core healthcare coordination rather than a conceptual specification alone. However, it remains a modular healthcare management solution rather than a full enterprise hospital system encompassing advanced billing, claims, interoperability, and extensive patient self-service functionalities.

### Core operational domains currently in place ✅
- Patient registration and identity management
- Appointment scheduling and provider coordination
- Clinical encounter documentation
- ER intake and triage operations
- Telehealth consultation workflow
- Admission, transfer, and bed management
- Reporting and audit mechanisms

### Remaining future expansion areas 🚧
- Billing and insurance claims processing
- E-prescription workflows
- HL7/FHIR interoperability integration for lab and imaging systems
- Advanced patient portal functionality
- Expanded analytics and forecasting capabilities

---

## 8. Development and Local Environment

### Commands currently used
```bash
php artisan migrate
php artisan test --stop-on-failure
php artisan db:seed
php artisan key:generate
```

### Local environment
- Project directory: `C:\laragon\www\coor`
- Local URL: `http://coor.test`
- Database: `coor`
- Runtime: PHP 8.3.x with MySQL 8.x

---

## 9. Final System Summary

As of 2026-08-13, the HIMS application in this workspace demonstrates a functioning healthcare information system that has progressed beyond prototype status into a working implementation across the principal operational domains of patient management, scheduling, emergency processing, telehealth coordination, and inpatient bed management. The project establishes a viable foundation for healthcare operations and demonstrates the technical integration of modern web application architecture with healthcare workflow processes.

The principal remaining gap relative to a full enterprise hospital platform is in advanced financial, interoperability, and patient-engagement capabilities. These areas remain future enhancement opportunities rather than deficiencies in the current core clinical and operational architecture.

---

Document Version: 2026.08.13  
Updated: 2026-08-13
