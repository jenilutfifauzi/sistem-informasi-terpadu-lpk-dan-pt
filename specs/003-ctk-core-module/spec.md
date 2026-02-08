# Feature Specification: CTK Core Module

**Feature Branch**: `003-ctk-core-module`  
**Created**: January 22, 2026  
**Status**: Draft  
**Input**: User description: "modul ctk berdasarkan PRD, untuk ctk formnya ada pada alur_ctk.md, dan untuk doc selain ceklis ada upload berkas seluruh form yang ada doc nya"

## Overview

The CTK (Calon Tenaga Kerja) Core Module is the **single source of truth** for managing prospective workers through their entire lifecycle—from initial registration through placement and departure. This module implements a comprehensive 15-stage workflow process tracking each CTK's progress, documents, payments, and status transitions with full audit trails and document management.

The module serves both LPK (training) and PT (placement) entities, enforcing proper entity isolation while allowing controlled data handoff between stages. All CTK data remains immutable once reaching final stages (OPP received, Terbang), ensuring compliance and audit requirements.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - CTK Registration & Profile Management (Priority: P1)

Admin LPK can register new CTK candidates with their personal information and track their basic profile throughout the entire process.

**Why this priority**: Foundation of the entire system - without CTK registration, no other workflows can begin. This represents the entry point for all CTK data.

**Independent Test**: Can be fully tested by creating a CTK record with personal information and verifying it appears in the list with correct data and initial status.

**Acceptance Scenarios**:

1. **Given** Admin LPK is logged in, **When** they create a new CTK with nama lengkap, NIK, tanggal lahir, alamat, no telepon, and email, **Then** the CTK record is created with status "MCU" (stage 1) and all data is saved correctly
2. **Given** a CTK record exists, **When** Admin LPK views the CTK detail, **Then** they see all personal information, current status, and audit trail
3. **Given** a CTK record exists in stage 1 (MCU), **When** Admin LPK updates personal information, **Then** the changes are saved and logged in audit trail
4. **Given** a duplicate NIK is entered, **When** Admin LPK tries to create a new CTK, **Then** system prevents creation and shows error message
5. **Given** multiple CTK records exist, **When** Admin LPK searches by name or NIK, **Then** system returns matching records

---

### User Story 2 - Medical Checkup (MCU) Stage Management (Priority: P1)

Admin LPK/PT can record and track initial medical checkup results for CTK candidates with three possible outcomes: FIT, UNFIT, or PENDING.

**Why this priority**: First critical gate in the CTK process - determines if candidate can proceed with training and placement. Affects both training enrollment and placement eligibility.

**Independent Test**: Can be tested by advancing a CTK to MCU stage, recording MCU results, and verifying appropriate status transitions and business rule enforcement.

**Acceptance Scenarios**:

1. **Given** CTK is in stage 1 (MCU), **When** Admin marks MCU as "FIT", **Then** CTK can advance to payment stage (stage 2) and MCU result is recorded
2. **Given** CTK is in stage 1 (MCU), **When** Admin marks MCU as "UNFIT", **Then** CTK is flagged as ineligible and cannot proceed to next stages
3. **Given** CTK is in stage 1 (MCU), **When** Admin marks MCU as "PENDING", **Then** CTK remains in stage 1 waiting for final MCU result
4. **Given** CTK has MCU status recorded, **When** Admin views MCU details, **Then** they see MCU result, date recorded, and who recorded it
5. **Given** CTK is marked "UNFIT", **When** Admin tries to advance to next stage, **Then** system prevents advancement and shows eligibility error

---

### User Story 3 - Payment Tracking Management (Priority: P1)

Admin PT/Keuangan PT can track multi-stage payments from CTK candidates with amounts, bank details, and payment status throughout the process.

**Why this priority**: Critical for financial tracking and determining payment completion before advancement. Ensures financial obligations are met before placement.

**Independent Test**: Can be tested by recording payment stages for a CTK, verifying amounts and bank information are saved, and confirming payment completion gates work correctly.

**Acceptance Scenarios**:

1. **Given** CTK is past MCU stage, **When** Admin records payment stage 1 with amount and bank name, **Then** payment is logged with timestamp and recorder
2. **Given** multiple payment stages exist, **When** Admin views payment history, **Then** they see all payments with amounts, dates, bank names, and status
3. **Given** not all required payments are complete, **When** Admin tries to advance CTK to final stages, **Then** system prevents advancement until payments are complete
4. **Given** a payment record exists, **When** Admin uploads payment proof document, **Then** document is attached to payment record
5. **Given** multiple CTKs exist, **When** Keuangan PT filters by payment status, **Then** they see list of CTKs grouped by payment completion

---

### User Story 4 - Document Upload & Management (Priority: P2)

Admin LPK/PT can upload, view, and manage required documents for each CTK at various stages (soal/berkas, paspor, ijin desa, rekomendasi, working permit).

**Why this priority**: Essential for compliance and legal requirements. Documents must be properly tracked and accessible for audit. Supports multiple stages requiring document verification.

**Independent Test**: Can be tested by uploading documents at different stages, verifying they're properly stored and accessible, and confirming required document gates work.

**Acceptance Scenarios**:

1. **Given** CTK is in any stage, **When** Admin uploads soal/berkas documents, **Then** documents are stored with metadata (uploader, date, file type) and marked as "Lengkap"
2. **Given** CTK has paspor document uploaded, **When** Admin enters paspor number, **Then** paspor number is saved and linked to document
3. **Given** CTK requires ijin desa, **When** Admin uploads ijin desa document, **Then** document is categorized correctly and status marked "Ada"
4. **Given** multiple documents exist for a CTK, **When** Admin views document list, **Then** they see all documents with type, upload date, uploader, and download link
5. **Given** required documents are missing, **When** Admin tries to advance CTK to stages requiring those documents, **Then** system prevents advancement and shows missing documents

---

### User Story 5 - LPK Training Stage Tracking (Priority: P2)

Admin LPK/Instruktur can track CTK progress through LPK training with completion status and training outcomes.

**Why this priority**: Core LPK responsibility. Training completion is a critical gate before CTK can be transferred to PT for placement. Affects instructor assignment and training capacity planning.

**Independent Test**: Can be tested by enrolling CTK in training, marking training complete, and verifying appropriate status transitions and reporting.

**Acceptance Scenarios**:

1. **Given** CTK has completed payments and documents, **When** Admin LPK enrolls CTK in training, **Then** CTK status advances to "Belajar di LPK" stage
2. **Given** CTK is in training, **When** Instruktur marks training as "Selesai", **Then** CTK advances to "Screening 1" stage
3. **Given** CTK completed training, **When** Admin views training details, **Then** they see training start date, completion date, and assigned instructor
4. **Given** multiple CTKs in training, **When** Admin LPK filters by training status, **Then** they see active training participants vs completed
5. **Given** CTK training is incomplete, **When** Admin tries to advance to screening, **Then** system prevents advancement

---

### User Story 6 - Screening & Interview Process (Priority: P2)

Admin PT can manage two screening stages and user interview for CTK candidates, recording pass/fail results.

**Why this priority**: Critical quality gates before final placement. Determines CTK readiness for user interview and eventual placement. Affects placement success rates.

**Independent Test**: Can be tested by advancing CTK through screening stages, recording results, and verifying business rules for progression.

**Acceptance Scenarios**:

1. **Given** CTK completed LPK training, **When** Admin PT conducts Screening 1 and marks "Lolos", **Then** CTK advances to "Interview User" stage
2. **Given** CTK passed Screening 1, **When** Admin PT conducts Interview User and marks "Lolos", **Then** CTK advances to document collection stage (ijin desa)
3. **Given** CTK failed screening, **When** Admin reviews screening results, **Then** they see failure reason and date recorded
4. **Given** CTK passed interview, **When** user views CTK profile, **Then** they see all screening dates and results with interviewer names
5. **Given** CTK failed any screening, **When** Admin tries to advance to next stage, **Then** system prevents advancement

---

### User Story 7 - Visa Application & Processing (Priority: P2)

Admin PT/Legal PT can manage visa application process including working permit (WP) document collection, visa application submission, and visa issuance tracking.

**Why this priority**: Required for legal overseas placement. Critical compliance requirement. Directly gates final departure stage.

**Independent Test**: Can be tested by collecting WP documents, submitting visa application, and tracking through to visa issuance with proper status updates.

**Acceptance Scenarios**:

1. **Given** CTK has all prerequisite documents (ijin desa, rekomendasi), **When** Admin PT marks WP documents as "Lengkap", **Then** CTK becomes eligible for visa application
2. **Given** WP documents are complete, **When** Legal PT submits visa application and marks as "Diajukan", **Then** CTK advances to visa processing stage
3. **Given** visa application submitted, **When** Legal PT marks visa as "Terbit", **Then** CTK advances to medical full examination stage
4. **Given** visa is issued, **When** Admin views visa details, **Then** they see visa number, issuance date, expiry date, and uploaded visa document
5. **Given** visa not yet issued, **When** Admin tries to advance to final stages, **Then** system prevents advancement

---

### User Story 8 - Medical Full Examination (Priority: P2)

Admin PT can track comprehensive medical examination required before final departure, recording examination completion and results.

**Why this priority**: Final health clearance before deployment. Compliance requirement for overseas placement. Must be current (within valid period) for departure.

**Independent Test**: Can be tested by recording medical full examination, verifying results are saved, and confirming it gates OPP stage properly.

**Acceptance Scenarios**:

1. **Given** CTK has visa issued, **When** Admin PT records Medical Full as "Selesai" with examination date, **Then** medical full status is saved and CTK can advance
2. **Given** Medical Full is complete, **When** Admin uploads medical report document, **Then** document is linked to medical full record
3. **Given** Medical Full result shows health issues, **When** Admin reviews medical full details, **Then** they see examination findings and recommendations
4. **Given** Medical Full is incomplete or failed, **When** Admin tries to advance to OPP, **Then** system prevents advancement
5. **Given** Medical Full was completed over 90 days ago, **When** system validates departure eligibility, **Then** warning is shown that medical may need renewal

---

### User Story 9 - OPP (Overseas Placement Permit) & Final Departure (Priority: P1)

Admin PT can record OPP receipt and final departure (Terbang) status, marking successful placement completion.

**Why this priority**: Final stages representing successful placement. Critical for completion reporting and success metrics. Triggers data immutability.

**Independent Test**: Can be tested by advancing CTK through all stages to OPP receipt and departure, verifying all gates work and final status is immutable.

**Acceptance Scenarios**:

1. **Given** CTK has completed all prerequisites (visa, medical full), **When** Admin PT marks OPP as "Diterima" with receipt date, **Then** CTK advances to ready-for-departure status
2. **Given** CTK has OPP received, **When** Admin PT records departure date and marks as "Terbang", **Then** CTK status becomes "Terbang" (final success state)
3. **Given** CTK reaches "Terbang" status, **When** anyone tries to edit CTK core data, **Then** system prevents editing except via documented correction process
4. **Given** CTK in final stages, **When** Admin views complete timeline, **Then** they see all stages with dates, durations, and responsible persons
5. **Given** multiple CTKs exist, **When** Pimpinan views dashboard, **Then** they see count of CTKs in "Terbang" status as success metric

---

### User Story 10 - Entity-Based Access Control (Priority: P1)

System enforces entity isolation (LPK vs PT) for CTK data access while allowing proper stage-based visibility.

**Why this priority**: Core compliance and security requirement from PRD. Ensures legal entity separation. Prevents unauthorized cross-entity access.

**Independent Test**: Can be tested by logging in as different roles/entities and verifying appropriate access to CTK records based on stage and entity.

**Acceptance Scenarios**:

1. **Given** user is Admin LPK, **When** they view CTK list, **Then** they see CTKs in LPK stages (stages 1-5: MCU through Belajar di LPK) only
2. **Given** user is Admin PT, **When** they view CTK list, **Then** they see CTKs in PT stages (stages 6-15: Screening through Terbang) only
3. **Given** user is Pimpinan, **When** they view CTK list, **Then** they see all CTKs across both entities with read-only access
4. **Given** CTK is in LPK stage, **When** Admin PT tries to access the record, **Then** system shows "not found" or "no access"
5. **Given** CTK transitions from LPK to PT stages, **When** transition occurs, **Then** audit log records entity handoff with approval

---

### User Story 11 - CTK Lifecycle Audit Trail (Priority: P2)

All CTK status changes, document uploads, and data modifications are automatically logged with timestamp, user, and change details for compliance.

**Why this priority**: Core compliance requirement from PRD. Enables audit and accountability. Supports dispute resolution and process improvement.

**Independent Test**: Can be tested by performing various CTK operations and verifying all actions are logged with proper detail and accessible for audit queries.

**Acceptance Scenarios**:

1. **Given** any CTK data is modified, **When** change is saved, **Then** audit log records who made change, what changed (old vs new value), and timestamp
2. **Given** CTK advances to new stage, **When** status changes, **Then** audit log records stage transition with responsible person and reason (if provided)
3. **Given** document is uploaded, **When** upload completes, **Then** audit log records uploader, document type, filename, and upload timestamp
4. **Given** user has audit permissions, **When** they view CTK audit trail, **Then** they see chronological log of all changes with filters by date/action/user
5. **Given** compliance audit is performed, **When** auditor exports CTK history, **Then** complete audit trail is available in exportable format

---

### Edge Cases

- What happens when CTK fails Medical Full after passing all previous stages? (System should allow status reversion with approval and audit log)
- How does system handle CTK who doesn't pass Interview User after completing training? (Mark as unsuccessful placement, retain data for reporting)
- What happens when visa application is rejected? (System allows resubmission with rejection reason logged)
- How does system prevent duplicate NIK entries? (Unique constraint with clear error message and search for existing record)
- What happens when payment amounts change mid-process? (Allow amendment with approval workflow and audit trail)
- How does system handle partial document uploads when multiple documents required? (Track each document separately, show completion percentage)
- What happens when Medical Full expires before departure? (System shows warning and requires renewal)
- How does system handle CTK cancellation at any stage? (Add "Batal" status available from any stage with cancellation reason and date)
- What happens when required documents are uploaded in wrong format? (Validate file types on upload, show clear error with accepted formats)
- How does system prevent unauthorized entity crossover? (Enforce entity scoping at query level with middleware and policy checks)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow Admin LPK to create new CTK records with personal information (nama lengkap, NIK, tanggal lahir, jenis kelamin, alamat lengkap, no telepon, email)
- **FR-002**: System MUST enforce NIK uniqueness across all CTK records to prevent duplicates
- **FR-003**: System MUST track CTK through 15 sequential stages: MCU, Pembayaran, Soal/Berkas, Paspor, Belajar di LPK, Screening 1, Interview User, Ijin Desa, Rekomendasi, WP, Apply Visa, Medical Full, Visa, OPP, Terbang
- **FR-004**: System MUST record MCU results as one of three states: FIT, UNFIT, PENDING
- **FR-005**: System MUST prevent CTK advancement to next stage when MCU status is UNFIT or PENDING
- **FR-006**: System MUST track multiple payment stages with amount (in Rupiah), bank name, payment date, and payment status
- **FR-007**: System MUST allow document upload for stages requiring documents: Soal/Berkas, Paspor, Ijin Desa, Rekomendasi, WP, Visa, Medical Full, OPP
- **FR-008**: System MUST store document metadata including uploader name, upload timestamp, file size, file type, and original filename
- **FR-009**: System MUST validate uploaded document file types (allowed: PDF, JPG, JPEG, PNG, max size 10MB per file)
- **FR-010**: System MUST record paspor number separately from paspor document upload
- **FR-011**: System MUST track training completion status (Selesai/Belum Selesai) for "Belajar di LPK" stage
- **FR-012**: System MUST record screening results (Lolos/Tidak Lolos) for both Screening 1 and Interview User stages
- **FR-013**: System MUST prevent CTK advancement when required screening results are Tidak Lolos
- **FR-014**: System MUST track WP document collection status (Lengkap/Tidak Lengkap) with multiple document uploads
- **FR-015**: System MUST record visa application status (Diajukan) and visa issuance (Terbit) with visa number and expiry date
- **FR-016**: System MUST record Medical Full completion status (Selesai) with examination date and allow medical report upload
- **FR-017**: System MUST record OPP receipt status (Diterima) with receipt date
- **FR-018**: System MUST record final departure status (Terbang) with departure date
- **FR-019**: System MUST prevent editing of CTK core data once status reaches "Terbang" except through documented admin override with approval
- **FR-020**: System MUST enforce entity-based access control where LPK users see CTKs in stages 1-5 (MCU through Belajar di LPK) and PT users see CTKs in stages 6-15 (Screening 1 through Terbang)
- **FR-021**: System MUST log all CTK status changes with timestamp, user who made the change, old status, new status, and optional notes
- **FR-022**: System MUST log all document uploads with uploader, timestamp, document type, and filename
- **FR-023**: System MUST log all data modifications to CTK records with before/after values for audit trail
- **FR-024**: System MUST provide search functionality by CTK name, NIK, paspor number, current status, and date ranges
- **FR-025**: System MUST provide filtering by entity (LPK/PT), status stage, MCU result, payment status, and visa status
- **FR-026**: System MUST display CTK list with sortable columns: name, NIK, current status, current stage, last updated date
- **FR-027**: System MUST show complete CTK profile with all personal data, current status, all stage details, uploaded documents, and audit trail
- **FR-028**: System MUST allow Admin to add notes/comments at any stage with timestamp and commenter name
- **FR-029**: System MUST calculate and display days spent in each stage for process analytics
- **FR-030**: System MUST support soft delete for CTK records with restoration capability for data recovery
- **FR-031**: System MUST validate required fields per stage before allowing advancement to next stage
- **FR-032**: System MUST show visual progress indicator displaying completed stages vs remaining stages
- **FR-033**: System MUST allow bulk actions for multiple CTK records (bulk status update, bulk document request)
- **FR-034**: System MUST export CTK data and reports in PDF and Excel formats for reporting
- **FR-035**: System MUST provide dashboard statistics: CTK count by stage, average processing time per stage, success rate (Terbang/Total), pending actions count

### Key Entities

- **CTK (Calon Tenaga Kerja)**: Core entity representing prospective worker. Contains personal information (nama lengkap, NIK, tanggal lahir, jenis kelamin, alamat, no telepon, email), current status/stage, entity association (LPK/PT), created/updated timestamps. Has relationships to MCU records, payments, documents, training records, screening results, visa records, and audit logs.

- **MCU Record**: Medical checkup result for a CTK. Contains result status (FIT/UNFIT/PENDING), examination date, clinic/hospital name, examiner name, notes. Belongs to one CTK. Gates advancement from registration stage.

- **Payment Record**: Single payment transaction for a CTK. Contains payment stage number (1-5), amount in Rupiah, bank name, payment date, payment method, payment status (Pending/Lunas), payment proof document reference. Belongs to one CTK. Multiple payment records per CTK.

- **Document**: Uploaded file associated with CTK for specific stage. Contains document type/category (Soal/Berkas, Paspor, Ijin Desa, Rekomendasi, WP, Visa, Medical Full, OPP), filename, file path, file size, file type (MIME), upload timestamp, uploader user reference. Belongs to one CTK.

- **Training Record**: LPK training information for a CTK. Contains training start date, completion date, training status (Aktif/Selesai), assigned instructor reference, training location, training hours, completion notes. Belongs to one CTK. Links to EmployeeLPK as instructor.

- **Screening Result**: Outcome of screening or interview stage. Contains stage name (Screening 1, Interview User), result (Lolos/Tidak Lolos), screening date, screener/interviewer user reference, notes/feedback. Belongs to one CTK. Multiple screening results per CTK.

- **Visa Record**: Visa application and issuance tracking. Contains application status (Diajukan/Terbit), application date, visa number, visa issuance date, visa expiry date, issuing country, visa type, visa document reference. Belongs to one CTK.

- **Stage Transition Log**: Audit record of CTK moving between stages. Contains from_stage, to_stage, transition timestamp, responsible user reference, transition reason/notes, approval reference (if required). Belongs to one CTK. Immutable after creation.

- **CTK Note**: Comment or note added to CTK record. Contains note text, created timestamp, author user reference, note category (Umum/Peringatan/Penting). Belongs to one CTK. Multiple notes per CTK.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin can create and view a CTK record from registration through to final departure status within 30 seconds
- **SC-002**: System prevents duplicate NIK entries 100% of the time with clear error messaging
- **SC-003**: All CTK status changes and document uploads are logged with complete audit trail (user, timestamp, changes) with 100% accuracy
- **SC-004**: Users can search and filter CTK records by any combination of criteria (name, NIK, status, stage, dates) and receive results in under 2 seconds for datasets up to 10,000 CTK records
- **SC-005**: Entity-based access control correctly restricts LPK users to LPK-stage CTKs and PT users to PT-stage CTKs with 100% enforcement
- **SC-006**: Required document validation prevents stage advancement when documents are missing in 100% of cases
- **SC-007**: System correctly enforces all stage gates (MCU results, payment completion, screening results, visa issuance) preventing invalid progression 100% of the time
- **SC-008**: Dashboard statistics (CTK by stage, processing times, success rates) update in real-time within 5 seconds of any CTK status change
- **SC-009**: CTK records in "Terbang" or "OPP" final stages become immutable with proper audit trail for any override attempts
- **SC-010**: Admin can export CTK data and complete audit trail in PDF or Excel format within 10 seconds for up to 1000 records
- **SC-011**: System handles concurrent updates to different CTK records by multiple users without data corruption or race conditions
- **SC-012**: Users can upload documents up to 10MB in supported formats (PDF, JPG, PNG) with validation feedback within 3 seconds

## Assumptions

- CTK personal information (NIK, tanggal lahir) is provided accurately and is immutable after initial entry
- Payment amounts and stages will be configured by administrators and may vary per CTK based on placement destination
- Training duration at LPK is tracked externally; this system only records completion status
- Medical Full examination is valid for 90 days for departure eligibility (configurable)
- Visa expiry dates provided by Legal PT are accurate and entered upon visa issuance
- Document retention follows standard 7-year retention policy for employment records
- Each CTK can only be in one stage at a time (sequential process, no parallel stages)
- Stage transitions are typically unidirectional (forward progress); reversions require admin approval
- System will be accessed primarily during business hours (8 AM - 6 PM WIB) with moderate concurrent usage (up to 50 simultaneous users)
- Internet connectivity is available for document uploads; no offline mode required in initial release
- Integration with external systems (BPJS, Dukcapil, bank systems) is out of scope for this phase
- Users have been trained on the 15-stage CTK process and understand stage requirements
- Role-based permissions from User Management module (Spec 001) are properly configured before CTK module deployment
- EmployeeLPK records (from Spec 002) exist for instructors to be assigned to training stages

