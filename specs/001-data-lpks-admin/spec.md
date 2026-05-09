# Feature Specification: Data Siswa LPK Administration

**Feature Branch**: `001-data-lpks-admin`  
**Created**: 2026-05-09  
**Status**: Draft  
**Input**: User description: "Buat fitur untuk data LPKs pada admin Filament dan gunakan isian datanya dari file register data lpks.md"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Record Siswa LPK Data (Priority: P1)

An administrator records a new Siswa LPK entry using the same data structure as the existing registration sheet so student identity, enrollment, and contact details are stored in one place.

**Why this priority**: Capturing the core student record is the minimum usable slice. Without this, there is no reliable source of truth for LPK student data in the admin system.

**Independent Test**: Can be fully tested by creating a new student record with the required fields, saving it, and confirming the stored record can be viewed with the same values.

**Acceptance Scenarios**:

1. **Given** an administrator is entering a new student record, **When** they provide a unique student number, student name, gender, religion, latest education, entry date, birth place, birth date, address, phone number, and education program, **Then** the system stores the record and shows it in the admin data list.
2. **Given** an administrator is entering a new student record without an email address, **When** they save the record, **Then** the system accepts the entry and stores the record without blocking the save.

---

### User Story 2 - Find, Review, and Export Siswa LPK Data (Priority: P2)

An administrator searches, reviews, and exports Siswa LPK records to confirm registration details without reopening paper or spreadsheet-based source data.

**Why this priority**: Once data exists, staff need fast retrieval and export support for verification, follow-up, and daily administration.

**Independent Test**: Can be fully tested by opening the student list, searching by key fields, confirming the expected record details are visible, and exporting the filtered list in Excel format.

**Acceptance Scenarios**:

1. **Given** multiple student records exist, **When** an administrator searches by student number, student name, or education program, **Then** the system returns matching records only.
2. **Given** a student record exists, **When** an administrator opens its detail view, **Then** the system shows all stored registration, identity, contact, and program information in a readable format.
3. **Given** one or more student records are visible in the admin list, **When** an administrator exports the data, **Then** the system produces an Excel export file that reflects the selected dataset.

---

### User Story 3 - Correct Siswa LPK Information (Priority: P3)

An administrator updates an existing Siswa LPK record when there is a correction in personal details, contact information, or assigned education program.

**Why this priority**: Administrative data changes over time, and the system must support corrections so records remain trustworthy.

**Independent Test**: Can be fully tested by editing an existing student record, saving the change, and verifying the revised values appear in both list and detail views.

**Acceptance Scenarios**:

1. **Given** a student record already exists, **When** an administrator updates one or more editable fields and saves the change, **Then** the system persists the updated values and shows the latest data when the record is reopened.
2. **Given** an administrator attempts to update a student record using a student number already assigned to another student, **When** they save the change, **Then** the system rejects the duplicate and explains which field must be corrected.

### Edge Cases

- How does the system handle a student record where email is unavailable but all other core fields are present?
- What happens when the birth date entered is later than the enrollment date?
- How does the system guide administrators when source notes combine place of birth and birth date into one value before saving?
- What happens when an administrator tries to create or edit two records with the same student number?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide an admin-managed data area for Siswa LPK records.
- **FR-002**: The system MUST allow administrators to create a Siswa LPK record using these fields: sequence number, student number, student name, gender, religion, latest education, enrollment date, birth place, birth date, address, phone number, email address, and education program.
- **FR-003**: The system MUST preserve the field meaning and labels from the existing registration sheet so staff can map paper or spreadsheet data into the system without reinterpretation.
- **FR-004**: The system MUST require a globally unique student number for each student record, including records that have been soft-deleted.
- **FR-005**: The system MUST require student name, gender, enrollment date, birth place, birth date, address, phone number, and education program before a record can be saved.
- **FR-006**: The system MUST allow email address to be left empty.
- **FR-007**: The system MUST validate that the birth date is not later than the enrollment date.
- **FR-008**: The system MUST show a student list with at least sequence number, student number, student name, gender, phone number, email address, and education program so administrators can identify records quickly.
- **FR-009**: The system MUST allow administrators to search student records by student number, student name, and education program.
- **FR-010**: The system MUST allow administrators to open a student record and review all stored registration, identity, contact, and program fields in one place.
- **FR-011**: The system MUST allow administrators to edit existing student records.
- **FR-012**: The system MUST show a clear validation message when a record cannot be saved because required fields are missing, the student number is duplicated, or the birth date is later than the enrollment date.
- **FR-013**: The system MUST store each registration field as a separately editable and separately queryable field, including sequence number, student number, student name, gender, religion, latest education, enrollment date, birth place, birth date, address, phone number, email address, and education program.
- **FR-014**: The system MUST allow authorized administrators to export student records in Excel format from the admin list.
- **FR-015**: When source notes combine place of birth and birth date into one value, administrators MUST separate the values into the dedicated place-of-birth and birth-date fields before the record can be saved.

### Non-Functional Requirements

- **NFR-001**: Access to Siswa LPK records MUST be restricted by RBAC so only authorized roles can view, create, update, and export the data.
- **NFR-002**: Create, update, and export operations on Siswa LPK records MUST be auditable with actor and timestamp context.
- **NFR-003**: Siswa LPK records MUST support soft deletes for retention and recovery.
- **NFR-004**: Export capability MUST be available in Excel format for staff use.

### Key Entities *(include if feature involves data)*

- **Siswa LPK Record**: A single registration record for one student, containing identifying data, enrollment details, contact information, and the selected education program.
- **Education Program**: The training or study program assigned to a student, used for categorization, search, and administrative review.

### Assumptions

- The feature is intended for authenticated administrative staff only.
- Existing registration content in the source file defines the initial field structure, not the full historical migration scope.
- Sequence number is maintained for display and ordering purposes, while student number is the primary business identifier.
- Email address is optional because the provided source sample includes records without email data.
- Student number cannot be reused even if an earlier student record has been soft-deleted.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Administrative staff can create a complete student record in under 3 minutes using the standardized field set.
- **SC-002**: At least 95% of student lookups by student number or student name return the intended record within 10 seconds.
- **SC-003**: At least 90% of valid record submissions succeed on the first attempt without requiring field corrections.
- **SC-004**: Administrative staff can identify and correct duplicate student-number entries before records are finalized in 100% of tested duplicate-entry scenarios.
