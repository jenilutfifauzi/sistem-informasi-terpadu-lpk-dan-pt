# Implementation Tasks: CTK Core Module

**Feature**: CTK Core Module - Single Source of Truth for Calon Tenaga Kerja  
**Branch**: `003-ctk-core-module`  
**Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

---

## Task Organization

Tasks are organized by **User Story** to enable independent implementation and testing. Each phase represents a complete, independently testable increment.

---

## Phase 1: Setup & Foundation

**Goal**: Initialize project structure, create enums, and set up base configuration

### Setup Tasks

- [X] T001 Verify branch 003-ctk-core-module is current and up to date with main
- [X] T002 Run composer install and verify all dependencies are present
- [X] T003 Create storage directories: storage/app/private/ctk-documents with subdirectories (soal-berkas, paspor, ijin-desa, rekomendasi, working-permit, visa, medical-full, opp)

### Enum Creation

- [X] T004 [P] Create CTKStatus enum in app/Enums/CTKStatus.php with 15 cases (MCU, Pembayaran, SoalBerkas, Paspor, BelajarDiLPK, Screening1, InterviewUser, IjinDesa, Rekomendasi, WP, ApplyVisa, MedicalFull, Visa, OPP, Terbang)
- [X] T005 [P] Create MCUStatus enum in app/Enums/MCUStatus.php with 3 cases (FIT, UNFIT, PENDING)
- [X] T006 [P] Create PaymentStatus enum in app/Enums/PaymentStatus.php with 2 cases (Pending, Lunas)
- [X] T007 [P] Create DocumentType enum in app/Enums/DocumentType.php with 8 cases (SoalBerkas, Paspor, IjinDesa, Rekomendasi, WorkingPermit, VisaDocument, MedicalFullReport, OPPDocument)
- [X] T008 [P] Create ScreeningStage enum in app/Enums/ScreeningStage.php with 2 cases (Screening1, InterviewUser)
- [X] T009 [P] Create ScreeningResult enum in app/Enums/ScreeningResult.php with 2 cases (Lolos, TidakLolos)

---

## Phase 2: Foundational - Database & Models

**Goal**: Create database schema and Eloquent models with relationships

### Database Migrations

- [X] T010 Create migration create_ctk_table.php with columns: id, nik (unique), nama_lengkap, tanggal_lahir, jenis_kelamin, alamat, no_telepon, email (nullable), current_status (CTKStatus enum), current_stage (int 1-15), current_entity (EntityType), created_by, updated_by, timestamps, soft_deletes
- [X] T011 Create migration create_mcu_records_table.php with columns: id, ctk_id (FK), status (MCUStatus enum), examination_date, clinic_name, examiner_name, notes (text), created_by, timestamps
- [X] T012 Create migration create_ctk_payments_table.php with columns: id, ctk_id (FK), stage_number (1-5), amount (decimal), bank_name, payment_date, payment_method, payment_status (PaymentStatus enum), payment_proof_path (nullable), created_by, timestamps
- [X] T013 Create migration create_ctk_documents_table.php with columns: id, ctk_id (FK), document_type (DocumentType enum), filename, file_path, file_size (int), mime_type, uploader_id (FK to users), upload_timestamp (timestamp)
- [X] T014 Create migration create_training_records_table.php with columns: id, ctk_id (FK), instructor_id (FK to karyawan_lpk), start_date, completion_date (nullable), training_status, training_location, training_hours (int), completion_notes (text nullable), timestamps
- [X] T015 Create migration create_screening_results_table.php with columns: id, ctk_id (FK), stage_name (ScreeningStage enum), result (ScreeningResult enum), screening_date, screener_id (FK to users), notes (text nullable), timestamps
- [X] T016 Create migration create_visa_records_table.php with columns: id, ctk_id (FK), application_status, application_date, visa_number (nullable), issuance_date (nullable), expiry_date (nullable), issuing_country, visa_type, visa_document_path (nullable), timestamps
- [X] T017 Create migration create_stage_transitions_table.php with columns: id, ctk_id (FK), from_stage (int), to_stage (int), transition_timestamp, user_id (FK), transition_reason (text nullable), approval_id (nullable), immutable after insert
- [X] T018 Create migration create_ctk_notes_table.php with columns: id, ctk_id (FK), note_text (text), note_category (enum: Umum/Peringatan/Penting), author_id (FK to users), timestamps
- [X] T019 Run php artisan migrate to create all CTK tables

### Model Creation

- [X] T020 [P] Create CTK model in app/Models/CTK.php with fillable fields, casts (enums, dates), soft deletes, LogsActivity trait
- [X] T021 [P] Create MCURecord model in app/Models/MCURecord.php with fillable fields and belongsTo CTK relationship
- [X] T022 [P] Create CTKPayment model in app/Models/CTKPayment.php with fillable fields and belongsTo CTK relationship
- [X] T023 [P] Create CTKDocument model in app/Models/CTKDocument.php with fillable fields and belongsTo CTK, belongsTo User relationships
- [X] T024 [P] Create TrainingRecord model in app/Models/TrainingRecord.php with fillable fields and relationships to CTK and EmployeeLPK
- [X] T025 [P] Create ScreeningResult model in app/Models/ScreeningResult.php with fillable fields and relationships to CTK and User
- [X] T026 [P] Create VisaRecord model in app/Models/VisaRecord.php with fillable fields and belongsTo CTK relationship
- [X] T027 [P] Create StageTransition model in app/Models/StageTransition.php with fillable fields and belongsTo CTK, belongsTo User relationships
- [X] T028 [P] Create CTKNote model in app/Models/CTKNote.php with fillable fields and relationships to CTK and User

### Model Relationships & Scopes

- [X] T029 Add relationships to CTK model: hasMany mcuRecords, payments, documents, trainingRecords, screeningResults, visaRecords, stageTransitions, notes
- [X] T030 Add scopes to CTK model: scopeByEntity, scopeInLPKStages (1-5), scopeInPTStages (6-15), scopeSearchByName, scopeSearchByNIK
- [X] T031 Add getActivitylogOptions method to CTK model to log changes to nik, nama_lengkap, current_status, current_stage, current_entity fields
- [X] T032 Add accessor methods to CTK model: getIsInLPKStagesAttribute, getIsInPTStagesAttribute, getCanAdvanceToNextStageAttribute

### Factory Creation

- [X] T033 [P] Create CTKFactory in database/factories/CTKFactory.php with valid test data (unique NIK generation, realistic names, addresses)
- [X] T034 [P] Create MCURecordFactory in database/factories/MCURecordFactory.php with FIT as default status
- [X] T035 [P] Create CTKPaymentFactory in database/factories/CTKPaymentFactory.php with random amounts and banks
- [X] T036 [P] Create factory states for CTK: withMCUFit(), withMCUUnfit(), withAllPaymentsComplete(), inLPKStages(), inPTStages(), readyForDeparture()

---

## Phase 3: User Story 1 - CTK Registration & Profile Management (P1)

**Goal**: Admin LPK can register and manage CTK personal information

### Permission & Policy Setup

- [X] T037 [US1] Create CTKPermissionsSeeder in database/seeders/CTKPermissionsSeeder.php with permissions: view_ctk, view_any_ctk, create_ctk, update_ctk, delete_ctk, restore_ctk, force_delete_ctk, override_ctk_immutability, view_ctk_audit
- [X] T038 [US1] Update RolesAndPermissionsSeeder to call CTKPermissionsSeeder
- [X] T039 [US1] Assign CTK permissions to roles: Admin LPK (create, view, update LPK stages), Admin PT (view, update PT stages), Pimpinan (view_any read-only), Super Admin (all)
- [X] T040 [US1] Create CTKPolicy in app/Policies/CTKPolicy.php with viewAny, view, create, update, delete, restore, forceDelete methods
- [X] T041 [US1] Implement entity scoping in CTKPolicy: LPK users can only access CTK with current_stage 1-5, PT users can only access current_stage 6-15, Pimpinan can view all
- [X] T042 [US1] Register CTKPolicy in AuthServiceProvider

### Filament Resource - Basic CRUD

- [X] T043 [US1] Create CTKResource using php artisan make:filament-resource CTK --generate --no-interaction in app/Filament/Resources/CTKResource.php
- [X] T044 [US1] Create CTKForm schema class in app/Filament/Resources/CTKResource/Schemas/CTKForm.php
- [X] T045 [US1] Implement form fields in CTKForm: TextInput for nik (required, unique, maxLength 16), nama_lengkap (required, maxLength 255), no_telepon (required, tel), email (email, nullable)
- [X] T046 [US1] Add form fields in CTKForm: DatePicker for tanggal_lahir (required, before today), Radio for jenis_kelamin (Laki-laki/Perempuan)
- [X] T047 [US1] Add form field in CTKForm: Textarea for alamat (required, maxLength 500, rows 3)
- [X] T048 [US1] Configure form to set defaults on create: current_status = MCU, current_stage = 1, current_entity = LPK, created_by = auth()->id()
- [X] T049 [US1] Create CTKTable class in app/Filament/Resources/CTKResource/Tables/CTKTable.php
- [X] T050 [US1] Implement table columns in CTKTable: TextColumn for nik (searchable, copyable), nama_lengkap (searchable, sortable)
- [X] T051 [US1] Add table columns in CTKTable: BadgeColumn for current_status (color coded), current_stage (badge with number), current_entity (color: LPK=info, PT=warning)
- [X] T052 [US1] Add table column in CTKTable: TextColumn for no_telepon, DateColumn for created_at (sortable)
- [X] T053 [US1] Add table filters in CTKTable: SelectFilter for current_entity (options from EntityType), current_stage (1-15)
- [X] T054 [US1] Add table filters in CTKTable: Filter for created_at date range
- [X] T055 [US1] Implement getEloquentQuery in CTKResource to apply entity scoping based on user's entity and permissions

### Pages Configuration

- [X] T056 [US1] Configure ListCTKs page in app/Filament/Resources/CTKResource/Pages/ListCTKs.php with default sort by created_at desc
- [X] T057 [US1] Configure CreateCTK page in app/Filament/Resources/CTKResource/Pages/CreateCTK.php with success notification and redirect to ViewCTK
- [X] T058 [US1] Configure EditCTK page in app/Filament/Resources/CTKResource/Pages/EditCTK.php with save notification
- [X] T059 [US1] Create ViewCTK page in app/Filament/Resources/CTKResource/Pages/ViewCTK.php for detailed CTK view (will add tabs later)

### Testing

- [X] T060 [US1] Create feature test CTKManagementTest in tests/Feature/CTKManagementTest.php
- [X] T061 [US1] Write test: Admin LPK can create CTK with valid data and defaults are set correctly
- [X] T062 [US1] Write test: System prevents duplicate NIK entries with clear error message
- [X] T063 [US1] Write test: Admin LPK can view CTK details with all personal information displayed
- [X] T064 [US1] Write test: Admin LPK can update CTK personal information and changes are logged
- [X] T065 [US1] Write test: Admin LPK can search CTK by name or NIK and get correct results
- [X] T066 [US1] Run php artisan test --filter=CTKManagementTest to verify all tests pass

---

## Phase 4: User Story 2 - MCU Stage Management (P1)

**Goal**: Record and track medical checkup results with stage gates

### MCU Form Section

- [X] T067 [US2] Create MCUSection schema class in app/Filament/Resources/CTKResource/Schemas/MCUSection.php
- [X] T068 [US2] Implement MCU form fields: Radio for status (FIT/UNFIT/PENDING, required), DatePicker for examination_date (required, maxDate today)
- [X] T069 [US2] Add MCU form fields: TextInput for clinic_name (required), examiner_name (required), Textarea for notes (nullable, rows 3)
- [X] T070 [US2] Create repeater or relation manager for MCU records on EditCTK/ViewCTK page
- [X] T071 [US2] Add validation: MCU record required before advancing from stage 1
- [X] T072 [US2] Add conditional visibility: Show "Cannot advance - MCU status is UNFIT/PENDING" message when applicable

### Stage Advancement Logic

- [X] T073 [US2] Create AdvanceStageAction in app/Filament/Resources/CTKResource/Actions/AdvanceStageAction.php
- [X] T074 [US2] Implement gate check in AdvanceStageAction: Verify MCU status is FIT before allowing advancement from stage 1
- [X] T075 [US2] Implement stage transition: Update current_stage from 1 to 2, log transition in stage_transitions table with user_id and timestamp
- [X] T076 [US2] Add success notification on advancement: "CTK advanced to stage 2 (Pembayaran)"
- [X] T077 [US2] Add error notification when gate fails: "Cannot advance - MCU status must be FIT"

### Testing

- [X] T078 [US2] Create feature test CTKMCUStageTest in tests/Feature/CTKMCUStageTest.php
- [X] T079 [US2] Write test: Admin can record MCU result as FIT and CTK can advance to payment stage
- [X] T080 [US2] Write test: Admin marks MCU as UNFIT, system prevents advancement with error message
- [X] T081 [US2] Write test: Admin marks MCU as PENDING, CTK remains in current stage
- [X] T082 [US2] Write test: MCU details are visible with date, clinic, examiner, and recorder
- [X] T083 [US2] Write test: Stage transition is logged in stage_transitions table with correct data
- [X] T084 [US2] Run php artisan test --filter=CTKMCUStageTest to verify all tests pass

---

## Phase 5: User Story 3 - Payment Tracking Management (P1)

**Goal**: Track multi-stage payments with amounts and bank details

### Payment Form Section

- [ ] T085 [US3] Create PaymentSection schema class in app/Filament/Resources/CTKResource/Schemas/PaymentSection.php
- [ ] T086 [US3] Implement payment form with Repeater component for multiple payment stages (1-5)
- [ ] T087 [US3] Add payment form fields per stage: TextInput for stage_number (disabled, auto-numbered 1-5), amount (numeric, required, prefix 'Rp')
- [ ] T088 [US3] Add payment form fields: TextInput for bank_name (required), DatePicker for payment_date (required, maxDate today)
- [ ] T089 [US3] Add payment form fields: Select for payment_status (Pending/Lunas), FileUpload for payment_proof (PDF/JPG, max 10MB, nullable)
- [ ] T090 [US3] Create relation manager for payments on ViewCTK page showing payment history table
- [ ] T091 [US3] Add calculated field: Show total paid vs total required in payment section

### Payment Validation & Gates

- [ ] T092 [US3] Add validation in AdvanceStageAction: Check if at least 1 payment is marked "Lunas" before advancing from stage 2
- [ ] T093 [US3] Implement payment completion check: Show completion percentage (e.g., "3/5 payments complete")
- [ ] T094 [US3] Add notification when payment proof is uploaded: "Payment proof uploaded for stage X"

### Keuangan PT/LPK View

- [ ] T095 [US3] Add payment status filter in CTKTable: Filter by payment completion (All Paid/Partial/None)
- [ ] T096 [US3] Add payment summary widget on dashboard (future enhancement placeholder)

### Testing

- [ ] T097 [US3] Create feature test CTKPaymentTrackingTest in tests/Feature/CTKPaymentTrackingTest.php
- [ ] T098 [US3] Write test: Admin can record payment stage 1 with amount and bank, payment is logged with timestamp
- [ ] T099 [US3] Write test: Admin views payment history and sees all payments with amounts, dates, banks, status
- [ ] T100 [US3] Write test: System prevents advancement when no payments are complete
- [ ] T101 [US3] Write test: Admin uploads payment proof document and document is attached to payment record
- [ ] T102 [US3] Write test: Keuangan PT can filter CTK list by payment status and see correct groups
- [ ] T103 [US3] Run php artisan test --filter=CTKPaymentTrackingTest to verify all tests pass

---

## Phase 6: User Story 4 - Document Upload & Management (P2)

**Goal**: Upload and manage required documents for compliance

### Document Upload Infrastructure

- [ ] T104 [US4] Create DocumentSection schema class in app/Filament/Resources/CTKResource/Schemas/DocumentSection.php
- [ ] T105 [US4] Implement document upload form: FileUpload field for each document type (SoalBerkas, Paspor, IjinDesa, Rekomendasi, WP, Visa, MedicalFull, OPP)
- [ ] T106 [US4] Configure FileUpload: acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']), maxSize(10240 KB), directory('ctk-documents/{document_type}')
- [ ] T107 [US4] Set file visibility to 'private' for all CTK documents
- [ ] T108 [US4] Add uploader_id and upload_timestamp automatically on upload using Filament lifecycle hooks
- [ ] T109 [US4] Create document list table on ViewCTK page showing all uploaded documents with type, filename, upload date, uploader name, download button

### Paspor Specific Fields

- [ ] T110 [US4] Add TextInput for paspor number in DocumentSection (required when paspor document uploaded, maxLength 20)
- [ ] T111 [US4] Link paspor number to paspor document in database

### Document Download

- [ ] T112 [US4] Create CTKDocumentController in app/Http/Controllers/CTKDocumentController.php with download method
- [ ] T113 [US4] Implement download method: Verify user is authenticated, verify user has permission to view CTK, return file response with original filename
- [ ] T114 [US4] Add route in web.php: Route::get('/ctk/documents/{document}/download', [CTKDocumentController::class, 'download'])->middleware('auth')
- [ ] T115 [US4] Add download action button in document list table

### Document Gates

- [ ] T116 [US4] Update AdvanceStageAction to check required documents: Stage 3 requires SoalBerkas, Stage 4 requires Paspor, Stage 8 requires IjinDesa, etc.
- [ ] T117 [US4] Show missing documents list when advancement is blocked: "Missing documents: Ijin Desa, Rekomendasi"

### Testing

- [ ] T118 [US4] Create feature test CTKDocumentUploadTest in tests/Feature/CTKDocumentUploadTest.php
- [ ] T119 [US4] Write test: Admin uploads soal/berkas document, document is stored with metadata and marked Lengkap
- [ ] T120 [US4] Write test: Admin uploads paspor document and enters paspor number, both are saved and linked
- [ ] T121 [US4] Write test: Admin uploads ijin desa document, document is categorized correctly and status marked Ada
- [ ] T122 [US4] Write test: Admin views document list and sees all documents with type, date, uploader, download link
- [ ] T123 [US4] Write test: System prevents advancement when required documents are missing with clear message
- [ ] T124 [US4] Write test: Authenticated user can download document, unauthenticated user gets 401
- [ ] T125 [US4] Run php artisan test --filter=CTKDocumentUploadTest to verify all tests pass

---

## Phase 7: User Story 5 - LPK Training Stage Tracking (P2)

**Goal**: Track CTK training with instructor assignment

### Training Form Section

- [ ] T126 [US5] Create TrainingSection schema class in app/Filament/Resources/CTKResource/Schemas/TrainingSection.php (placeholder for future Pelatihan module)
- [ ] T127 [US5] Implement training form fields: DatePicker for start_date (required), Select for instructor_id (relationship to karyawan_lpk where jabatan=Instruktur, searchable, preload)
- [ ] T128 [US5] Add training form fields: TextInput for training_location (required), training_hours (numeric), Textarea for completion_notes (nullable)
- [ ] T129 [US5] Add training form fields: DatePicker for completion_date (nullable, after start_date), Radio for training_status (Aktif/Selesai)
- [ ] T130 [US5] Create relation manager for training record on ViewCTK page showing training details and assigned instructor name

### Training Gate

- [ ] T131 [US5] Update AdvanceStageAction: Check training_status is "Selesai" before advancing from stage 5 (Belajar di LPK)
- [ ] T132 [US5] Implement entity handoff: When advancing from stage 5 to 6, update current_entity from LPK to PT and log in stage_transitions with transition_reason "Training completed - handoff to PT"
- [ ] T133 [US5] Add notification on handoff: "CTK transferred to PT entity for placement process"

### Training Filters & Views

- [ ] T134 [US5] Add filter in CTKTable: Filter by training status (Aktif/Selesai)
- [ ] T135 [US5] Create dashboard widget for LPK showing active training count (future enhancement placeholder)

### Testing

- [ ] T136 [US5] Create feature test CTKTrainingStageTest in tests/Feature/CTKTrainingStageTest.php
- [ ] T137 [US5] Write test: Admin LPK enrolls CTK in training with instructor assignment, training record is created
- [ ] T138 [US5] Write test: Instruktur marks training as Selesai, CTK can advance to Screening 1 stage
- [ ] T139 [US5] Write test: Admin views training details and sees start date, completion date, assigned instructor name
- [ ] T140 [US5] Write test: Admin LPK filters by training status and sees correct CTK groups (active vs completed)
- [ ] T141 [US5] Write test: System prevents advancement when training is incomplete with error message
- [ ] T142 [US5] Write test: Entity handoff from LPK to PT is logged correctly in stage_transitions
- [ ] T143 [US5] Run php artisan test --filter=CTKTrainingStageTest to verify all tests pass

---

## Phase 8: User Story 6 - Screening & Interview Process (P2)

**Goal**: Manage screening and interview stages for PT

### Screening Form Section

- [X] T144 [US6] Create ScreeningSection schema class in app/Filament/Resources/CTKResource/Schemas/ScreeningSection.php
- [X] T145 [US6] Implement screening form with Repeater for multiple screening stages (Screening1, InterviewUser)
- [X] T146 [US6] Add screening form fields: Select for stage_name (Screening1/InterviewUser, required), Radio for result (Lolos/TidakLolos, required)
- [X] T147 [US6] Add screening form fields: DatePicker for screening_date (required, maxDate today), Select for screener_id (relationship to users, searchable)
- [X] T148 [US6] Add screening form field: Textarea for notes (nullable, rows 3, label "Feedback/Catatan")
- [X] T149 [US6] Create relation manager for screening results on ViewCTK page showing screening history table

### Screening Gates

- [X] T150 [US6] Update AdvanceStageAction: Check Screening1 result is "Lolos" before advancing from stage 6 to 7
- [X] T151 [US6] Update AdvanceStageAction: Check InterviewUser result is "Lolos" before advancing from stage 7 to 8
- [X] T152 [US6] Add error notification when screening fails: "Cannot advance - Screening result is Tidak Lolos"
- [X] T153 [US6] Show screening history timeline on ViewCTK with all dates, results, and screener names

### Testing

- [X] T154 [US6] Create feature test CTKScreeningProcessTest in tests/Feature/CTKScreeningProcessTest.php
- [X] T155 [US6] Write test: Admin PT conducts Screening 1 and marks Lolos, CTK advances to Interview User stage
- [X] T156 [US6] Write test: Admin PT conducts Interview User and marks Lolos, CTK advances to document collection (Ijin Desa)
- [X] T157 [US6] Write test: CTK fails screening, Admin reviews results and sees failure reason with date
- [X] T158 [US6] Write test: CTK passes interview, user views profile and sees all screening dates, results, interviewer names
- [X] T159 [US6] Write test: System prevents advancement when screening result is Tidak Lolos
- [X] T160 [US6] Run php artisan test --filter=CTKScreeningProcessTest to verify all tests pass

---

## Phase 9: User Story 7 - Visa Application & Processing (P2)

**Goal**: Manage visa workflow from application to issuance

### Visa Form Section

- [X] T161 [US7] Create VisaSection schema class in app/Filament/Resources/CTKResource/Schemas/VisaSection.php
- [X] T162 [US7] Implement visa form fields: Radio for application_status (Diajukan/Terbit, required), DatePicker for application_date (required, maxDate today)
- [X] T163 [US7] Add visa form fields (visible when status=Terbit): TextInput for visa_number (required, maxLength 50), DatePicker for issuance_date (required)
- [X] T164 [US7] Add visa form fields: DatePicker for expiry_date (required, after issuance_date), TextInput for issuing_country (required), visa_type (required)
- [X] T165 [US7] Add visa form field: FileUpload for visa_document (PDF/JPG, max 10MB, required when status=Terbit)
- [X] T166 [US7] Create relation manager for visa record on ViewCTK page showing visa details

### Working Permit (WP) Document Collection

- [ ] T167 [US7] Add WP document upload in DocumentSection: Multiple file upload for working_permit documents (can upload multiple files)
- [ ] T168 [US7] Add WP status indicator: Show "WP Documents: X files uploaded" with Lengkap badge when all required
- [ ] T169 [US7] Update AdvanceStageAction: Check WP documents are marked Lengkap before allowing visa application (stage 10 to 11)

### Visa Gates

- [X] T170 [US7] Update AdvanceStageAction: Check visa status is "Terbit" before advancing from stage 13 to 14
- [X] T171 [US7] Add visa expiry warning: If visa expires within 30 days, show warning badge on CTK list

### Testing

- [X] T172 [US7] Create feature test CTKVisaProcessTest in tests/Feature/CTKVisaProcessTest.php
- [X] T173 [US7] Write test: Admin PT marks WP documents as Lengkap, CTK becomes eligible for visa application
- [X] T174 [US7] Write test: Legal PT submits visa application and marks Diajukan, CTK advances to visa processing stage
- [X] T175 [US7] Write test: Legal PT marks visa as Terbit, CTK advances to Medical Full stage
- [X] T176 [US7] Write test: Admin views visa details and sees visa number, issuance date, expiry date, uploaded document
- [X] T177 [US7] Write test: System prevents advancement when visa not yet issued
- [X] T178 [US7] Run php artisan test --filter=CTKVisaProcessTest to verify all tests pass

---

## Phase 10: User Story 8 - Medical Full Examination (P2)

**Goal**: Track comprehensive medical before departure

### Medical Full Form Section

- [ ] T179 [US8] Add medical full fields in VisaSection or create separate MedicalFullSection
- [ ] T180 [US8] Implement medical full form fields: Radio for status (Selesai/Belum, required), DatePicker for examination_date (required, maxDate today)
- [ ] T181 [US8] Add medical full field: FileUpload for medical report (PDF, max 10MB, required when status=Selesai)
- [ ] T182 [US8] Add medical full field: Textarea for examination_findings (nullable, rows 4, label "Hasil Pemeriksaan")

### Medical Full Gates & Validation

- [ ] T183 [US8] Update AdvanceStageAction: Check medical full status is "Selesai" before advancing from stage 12 to 13
- [ ] T184 [US8] Implement medical expiry warning: If medical full date is > 90 days ago, show warning "Medical examination may need renewal"
- [ ] T185 [US8] Add notification when medical full uploaded: "Medical full report uploaded successfully"

### Testing

- [ ] T186 [US8] Create feature test CTKMedicalFullTest in tests/Feature/CTKMedicalFullTest.php
- [ ] T187 [US8] Write test: Admin PT records Medical Full as Selesai with date, status is saved and CTK can advance
- [ ] T188 [US8] Write test: Admin uploads medical report document, document is linked to medical full record
- [ ] T189 [US8] Write test: Medical Full result shows health issues, Admin reviews details and sees findings
- [ ] T190 [US8] Write test: System prevents advancement when Medical Full incomplete or failed
- [ ] T191 [US8] Write test: Medical Full completed over 90 days ago shows renewal warning
- [ ] T192 [US8] Run php artisan test --filter=CTKMedicalFullTest to verify all tests pass

---

## Phase 11: User Story 9 - OPP & Final Departure (P1)

**Goal**: Record final stages and trigger immutability

### OPP & Departure Form

- [ ] T193 [US9] Add OPP fields in form: Radio for opp_status (Diterima/Belum, required), DatePicker for opp_receipt_date (required when Diterima)
- [ ] T194 [US9] Add OPP field: FileUpload for opp_document (PDF, max 10MB, required when status=Diterima)
- [ ] T195 [US9] Add Terbang fields: DatePicker for departure_date (required, after opp_receipt_date), TextInput for flight_number (nullable)
- [ ] T196 [US9] Add visual indicator: Badge showing "Final Stage - Record Locked" when status=Terbang

### Immutability Enforcement

- [ ] T197 [US9] Create CTKObserver in app/Observers/CTKObserver.php
- [ ] T198 [US9] Implement updating method in CTKObserver: Check if CTK current_stage >= 14 (OPP) or status=Terbang
- [ ] T199 [US9] If immutable stage and user lacks override_ctk_immutability permission, throw ValidationException with message "CTK record is locked - final stage"
- [ ] T200 [US9] If user has override permission, log override action in activity log with justification field
- [ ] T201 [US9] Register CTKObserver in AppServiceProvider boot method

### Success Metrics & Timeline

- [ ] T202 [US9] Add CTK timeline view in ViewCTK page showing all stages with dates, durations, responsible persons
- [ ] T203 [US9] Calculate days spent in each stage and total processing time from registration to Terbang
- [ ] T204 [US9] Add success badge on CTK list: Green "✈️ Terbang" badge for completed placements

### Testing

- [ ] T205 [US9] Create feature test CTKFinalStageTest in tests/Feature/CTKFinalStageTest.php
- [ ] T206 [US9] Write test: Admin PT marks OPP as Diterima with receipt date, CTK advances to ready-for-departure
- [ ] T207 [US9] Write test: Admin PT records departure date and marks Terbang, CTK status becomes Terbang
- [ ] T208 [US9] Write test: CTK reaches Terbang status, system prevents editing without override permission
- [ ] T209 [US9] Write test: User with override permission can edit Terbang CTK and action is logged
- [ ] T210 [US9] Write test: Admin views complete timeline and sees all stages with dates, durations, persons
- [ ] T211 [US9] Write test: Pimpinan views dashboard and sees count of CTKs in Terbang status
- [ ] T212 [US9] Run php artisan test --filter=CTKFinalStageTest to verify all tests pass

---

## Phase 12: User Story 10 - Entity-Based Access Control (P1)

**Goal**: Enforce entity isolation between LPK and PT

### Policy Implementation

- [ ] T213 [US10] Enhance CTKPolicy viewAny method: Apply entity scope based on user's entity
- [ ] T214 [US10] Implement scope in CTKPolicy: LPK users see only CTK where current_stage 1-5, PT users see only current_stage 6-15
- [ ] T215 [US10] Add exception for Pimpinan role: Can view all CTKs regardless of entity
- [ ] T216 [US10] Update view method in CTKPolicy: Verify user entity matches CTK current_entity or user is Pimpinan
- [ ] T217 [US10] Update update method in CTKPolicy: Verify user entity matches CTK current_entity before allowing edits

### Filament Query Scoping

- [ ] T218 [US10] Implement getEloquentQuery in CTKResource: Apply entity filter based on auth user
- [ ] T219 [US10] Test entity scoping: LPK user logs in and only sees stages 1-5, PT user only sees 6-15
- [ ] T220 [US10] Add visual indicator on CTK detail: Show "Entity: LPK" or "Entity: PT" badge prominently

### Testing

- [ ] T221 [US10] Create feature test CTKEntityIsolationTest in tests/Feature/CTKEntityIsolationTest.php
- [ ] T222 [US10] Write test: Admin LPK views CTK list and sees only CTKs in LPK stages (1-5)
- [ ] T223 [US10] Write test: Admin PT views CTK list and sees only CTKs in PT stages (6-15)
- [ ] T224 [US10] Write test: Pimpinan views CTK list and sees all CTKs across both entities (read-only)
- [ ] T225 [US10] Write test: CTK in LPK stage, Admin PT tries to access record and gets 403/not found
- [ ] T226 [US10] Write test: CTK transitions from stage 5 to 6, audit log records entity handoff
- [ ] T227 [US10] Run php artisan test --filter=CTKEntityIsolationTest to verify all tests pass

---

## Phase 13: User Story 11 - CTK Lifecycle Audit Trail (P2)

**Goal**: Complete audit logging for compliance

### Audit Trail Display

- [ ] T228 [US11] Create AuditTrailSection infolist on ViewCTK page using Spatie ActivityLog
- [ ] T229 [US11] Display activity log entries: Show who, what changed (old vs new value), when (timestamp)
- [ ] T230 [US11] Add stage transitions table: Show from_stage, to_stage, user, timestamp, transition_reason
- [ ] T231 [US11] Add document upload log: Show document type, uploader name, upload timestamp
- [ ] T232 [US11] Implement timeline view: Visual timeline showing all CTK lifecycle events in chronological order

### Audit Trail Action

- [ ] T233 [US11] Create ViewAuditTrailAction in app/Filament/Resources/CTKResource/Actions/ViewAuditTrailAction.php
- [ ] T234 [US11] Implement modal showing complete audit trail with filters by date, action type, user
- [ ] T235 [US11] Add permission check: Only users with view_ctk_audit permission can access full audit trail

### Testing

- [ ] T236 [US11] Create feature test CTKAuditTrailTest in tests/Feature/CTKAuditTrailTest.php
- [ ] T237 [US11] Write test: CTK data modified, audit log records who, what changed (old vs new), timestamp
- [ ] T238 [US11] Write test: CTK advances to new stage, audit log records stage transition with responsible person, reason
- [ ] T239 [US11] Write test: Document uploaded, audit log records uploader, document type, filename, timestamp
- [ ] T240 [US11] Write test: User with audit permission views CTK audit trail and sees chronological log with filters
- [ ] T241 [US11] Write test: Compliance auditor exports CTK history and complete audit trail is available in export
- [ ] T242 [US11] Run php artisan test --filter=CTKAuditTrailTest to verify all tests pass

---

## Phase 14: Advanced Features & Polish

**Goal**: Bulk actions, exports, and UI enhancements

### Bulk Actions

- [ ] T243 Create bulk action for multiple CTK selection: BulkAdvanceStageAction (with validation per CTK)
- [ ] T244 Create bulk action: BulkSendDocumentRequestNotification (send email/notification to CTKs)
- [ ] T245 Add bulk filters: Select multiple CTKs by stage, by entity, by date range

### Export Functionality

- [ ] T246 Install maatwebsite/excel and barryvdh/laravel-dompdf if not present
- [ ] T247 Create CTK export action: ExportCTKsAction for Excel format with all CTK data
- [ ] T248 Create PDF export action: ExportCTKDetailPDFAction including complete audit trail
- [ ] T249 Add export options: Filter data to export (active only, by date range, by stage)
- [ ] T250 Configure export templates with formatting and branding

### CTK Notes/Comments

- [ ] T251 Create notes section on ViewCTK page: Allow adding comments/notes to CTK
- [ ] T252 Implement notes form: Textarea for note_text, Select for note_category (Umum/Peringatan/Penting)
- [ ] T253 Display notes list: Show all notes with author, timestamp, category badge
- [ ] T254 Add notification when important note added: Notify relevant users based on note category

### Visual Enhancements

- [ ] T255 Create StageProgressSection: Visual progress bar showing completed vs remaining stages
- [ ] T256 Add stage completion indicators: Checkmarks for completed stages, current stage highlighted
- [ ] T257 Implement loading states for file uploads: Show progress bar during upload
- [ ] T258 Add validation messages: Clear, user-friendly messages for all form validations
- [ ] T259 Add success/error notifications: Toast notifications for all CRUD operations
- [ ] T260 Add tooltips and help text: Explain each stage requirement and document type

### Dashboard Widgets

- [ ] T261 Create CTK statistics widget: Total CTK, by stage breakdown, success rate (Terbang/Total)
- [ ] T262 Create processing time widget: Average days per stage, total processing time
- [ ] T263 Create pending actions widget: CTKs waiting for documents, payments, screening
- [ ] T264 Configure widgets per role: LPK sees LPK-relevant widgets, PT sees PT-relevant widgets

### Testing

- [ ] T265 Create feature test CTKBulkActionsTest in tests/Feature/CTKBulkActionsTest.php for bulk operations
- [ ] T266 Create feature test CTKExportTest in tests/Feature/CTKExportTest.php for PDF/Excel exports
- [ ] T267 Write test: Export 1000 CTK records to Excel and verify completion time < 10 seconds
- [ ] T268 Write test: Export CTK detail to PDF with audit trail included
- [ ] T269 Run all feature tests: php artisan test --testsuite=Feature to verify complete functionality

---

## Phase 15: Performance Optimization & Final QA

**Goal**: Optimize queries and ensure production readiness

### Database Optimization

- [ ] T270 Add database indexes: Index on ctk.nik, ctk.current_status, ctk.current_stage, ctk.current_entity
- [ ] T271 Add indexes on foreign keys: ctk_id in all related tables, user_id in audit tables
- [ ] T272 Add indexes for search: ctk.nama_lengkap, ctk.no_telepon for faster searches
- [ ] T273 Implement eager loading in CTKResource: Eager load relationships (mcuRecords, payments, documents, etc.) to avoid N+1

### Query Performance

- [ ] T274 Optimize CTK list query: Use select specific columns, paginate results, limit includes
- [ ] T275 Implement lazy loading for tabs: Load tab content only when tab is clicked (ViewCTK page)
- [ ] T276 Add caching for dashboard statistics: Cache widget data for 5 minutes
- [ ] T277 Test query performance: Seed 10k+ CTK records and verify list page loads < 2 seconds

### Code Quality

- [ ] T278 Run vendor/bin/pint on all CTK files to ensure code formatting standards
- [ ] T279 Review all validation rules: Ensure consistent, comprehensive validation across forms
- [ ] T280 Review error messages: Ensure all messages are user-friendly and actionable
- [ ] T281 Review permission checks: Verify all actions have proper authorization

### Final Testing

- [ ] T282 Run complete test suite: php artisan test --testsuite=Feature --testsuite=Unit
- [ ] T283 Manual QA testing: Test all user stories end-to-end with real user accounts
- [ ] T284 Test entity isolation: Verify LPK/PT separation works correctly across all scenarios
- [ ] T285 Test immutability: Verify final stage CTKs cannot be edited without override
- [ ] T286 Performance test: Verify all success criteria from spec (SC-001 through SC-012)

### Documentation

- [ ] T287 Update quickstart.md with final setup instructions and common tasks
- [ ] T288 Document all CTK permissions and their purposes
- [ ] T289 Document stage progression rules and gates for each stage
- [ ] T290 Create user guide: Step-by-step guide for Admin LPK and Admin PT workflows

---

## Dependencies

### Completed Modules (Available)
- ✅ User Management & RBAC (Spec 001)
- ✅ Karyawan LPK (Spec 002)

### External Packages (To Verify/Install)
- intervention/image v3
- maatwebsite/excel v3
- barryvdh/laravel-dompdf v2

---

## Task Summary

**Total Tasks**: 290  
**Phase 1 (Setup)**: 9 tasks  
**Phase 2 (Foundation)**: 28 tasks  
**Phase 3 (US1)**: 27 tasks  
**Phase 4 (US2)**: 18 tasks  
**Phase 5 (US3)**: 19 tasks  
**Phase 6 (US4)**: 22 tasks  
**Phase 7 (US5)**: 18 tasks  
**Phase 8 (US6)**: 17 tasks  
**Phase 9 (US7)**: 18 tasks  
**Phase 10 (US8)**: 14 tasks  
**Phase 11 (US9)**: 20 tasks  
**Phase 12 (US10)**: 15 tasks  
**Phase 13 (US11)**: 15 tasks  
**Phase 14 (Advanced)**: 27 tasks  
**Phase 15 (Polish)**: 23 tasks

---

## Parallel Execution Opportunities

Tasks marked with **[P]** can be executed in parallel as they work on independent files:

- **Phase 1**: T004-T009 (all enums can be created in parallel)
- **Phase 2**: T010-T018 (migrations can be created in parallel)
- **Phase 2**: T020-T028 (models can be created in parallel after migrations)
- **Phase 2**: T033-T036 (factories can be created in parallel)

---

## MVP Scope Recommendation

**Minimum Viable Product (MVP)** - Phases 1-5:
- Setup & Foundation (Phases 1-2)
- User Story 1: CTK Registration (Phase 3)
- User Story 2: MCU Stage (Phase 4)
- User Story 3: Payment Tracking (Phase 5)

This MVP delivers:
- ✅ CTK registration and management
- ✅ First critical gate (MCU)
- ✅ Payment tracking
- ✅ Entity isolation
- ✅ Basic audit trail
- ✅ Foundation for remaining stages

**Estimated MVP completion**: ~60% of total tasks (Phases 1-5 = 101 tasks)

---

## Next Steps

1. Review and approve this task breakdown
2. Begin implementation with Phase 1 (Setup)
3. Work through phases sequentially, testing after each phase
4. Mark tasks complete using checkboxes as you progress
5. Run tests frequently to catch issues early
6. Use parallel execution opportunities for faster progress
