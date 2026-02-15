# Feature Specification: CTK Index Action Buttons

**Feature Branch**: `006-ctk-view-actions`  
**Created**: February 15, 2026  
**Status**: Draft  
**Input**: User description: "tambahkan tombol action view, kelola progress ctv, views pada index http://localhost:8000/admin/c-t-k-s"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Quick View CTK Details (Priority: P1)

As an HR administrator viewing the CTK list, I need a visible "View" action button on each row, so I can quickly access a candidate's full details without needing to know I can click the row itself.

**Why this priority**: Makes the primary action of viewing CTK details explicit and discoverable. Currently users must discover that rows are clickable, which is not intuitive for all users. An explicit button reduces training time and improves user experience.

**Independent Test**: Can be fully tested by navigating to the CTK index page, clicking the "View" action button on any CTK row, and verifying it opens the CTK detail page showing complete candidate information.

**Acceptance Scenarios**:

1. **Given** I am on the CTK index page, **When** I see a CTK row, **Then** I see a "View" action button displayed clearly on that row
2. **Given** I am viewing a CTK row, **When** I click the "View" action button, **Then** I am navigated to the CTK detail page for that specific candidate
3. **Given** I view a CTK's details, **When** the detail page loads, **Then** I see all candidate information including personal data, current stage, progress, documents, and payment history
4. **Given** I am on the CTK detail page, **When** I want to return to the list, **Then** I can use the back navigation to return to the same filtered/sorted view I was viewing
5. **Given** multiple CTK records exist, **When** I click "View" on different rows, **Then** each opens the correct corresponding CTK detail page

---

### User Story 2 - Manage CTK Stage Progress (Priority: P1)

As an authorized administrator (Admin LPK/PT, HR PT, etc.), I need a "Kelola Progress" (Manage Progress) action button on each CTK row, so I can quickly advance a candidate to the next stage or update their current stage status without navigating to the full detail page.

**Why this priority**: Streamlines the most common administrative action - updating CTK progress. Currently requires navigating to the detail/edit page, which adds unnecessary clicks. A direct action button from the index reduces workflow friction and saves time for high-volume operations.

**Independent Test**: Can be fully tested by clicking the "Kelola Progress" button on a CTK row, making progress updates in the modal/form that appears, and verifying the CTK's stage and progress are correctly updated on the index page.

**Acceptance Scenarios**:

1. **Given** I am an authorized administrator on the CTK index page, **When** I see a CTK row, **Then** I see a "Kelola Progress" action button available
2. **Given** I click the "Kelola Progress" button, **When** the action triggers, **Then** a modal or form opens showing the CTK's current stage and available progress updates
3. **Given** the progress management modal is open, **When** I update the CTK's stage (e.g., mark MCU as complete, advance to next stage), **Then** the changes are validated and saved with proper authorization checks
4. **Given** I successfully update a CTK's progress, **When** the modal closes, **Then** the CTK index page refreshes to show the updated stage and progress information without requiring a full page reload
5. **Given** I attempt to update progress for a stage outside my authorization (e.g., Admin LPK trying to update PT stages), **When** I try to save, **Then** the system prevents the change and shows an appropriate authorization error
6. **Given** a CTK requires certain prerequisites before advancing (e.g., payment completion, document upload), **When** I try to advance the stage without meeting requirements, **Then** the system prevents advancement and shows which prerequisites are missing

---

### User Story 3 - Role-Based Action Visibility (Priority: P2)

As a system user with specific role permissions, I need to see only the action buttons I'm authorized to use on each CTK row, so I'm not confused by actions I cannot perform and the interface remains clean.

**Why this priority**: Ensures users only see relevant actions based on their role and the CTK's current entity/stage. Reduces confusion and prevents unauthorized action attempts. Important for usability but lower priority than getting the core actions working.

**Independent Test**: Can be tested by logging in with different roles (Admin LPK, Admin PT, Pimpinan, etc.) and verifying only authorized action buttons appear for CTKs in stages they can manage.

**Acceptance Scenarios**:

1. **Given** I am Admin LPK viewing a CTK in LPK stages (1-5), **When** I view the row actions, **Then** I see both "View" and "Kelola Progress" buttons
2. **Given** I am Admin LPK viewing a CTK in PT stages (6-15), **When** I view the row actions, **Then** I see only the "View" button (no "Kelola Progress")
3. **Given** I am Admin PT viewing a CTK in PT stages (6-15), **When** I view the row actions, **Then** I see both "View" and "Kelola Progress" buttons
4. **Given** I am Pimpinan (view-only role) viewing any CTK, **When** I view the row actions, **Then** I see only the "View" button (no "Kelola Progress")
5. **Given** I am viewing CTKs with my role, **When** entity transfer occurs (e.g., CTK moves from LPK to PT), **Then** the action buttons update appropriately based on my role's authorization for the new entity

---

### User Story 4 - Batch Progress Actions (Priority: P3)

As an administrator managing multiple CTKs at similar stages, I need the ability to select multiple CTK rows and apply progress updates to all selected candidates simultaneously, so I can efficiently process groups of candidates that completed the same milestone (e.g., a training batch completing LPK training).

**Why this priority**: Reduces repetitive work for bulk operations but is lower priority than single-record actions. Nice-to-have efficiency feature for high-volume workflows.

**Independent Test**: Can be tested by selecting multiple CTK rows using checkboxes, clicking a bulk "Kelola Progress" action, applying the same update to all selected records, and verifying all are updated correctly with audit trails.

**Acceptance Scenarios**:

1. **Given** I am on the CTK index page, **When** I select multiple CTK checkboxes, **Then** a bulk actions toolbar appears with a "Kelola Progress" option
2. **Given** I have selected multiple CTKs at the same stage, **When** I click bulk "Kelola Progress", **Then** a modal opens allowing me to apply the same stage update to all selected candidates
3. **Given** I select CTKs at different stages/entities, **When** I attempt bulk progress update, **Then** the system either groups them by compatible stage or warns that bulk update is only available for CTKs at the same stage
4. **Given** I complete a bulk progress update, **When** the action finishes, **Then** all selected CTKs are updated and I see a success message indicating how many records were updated
5. **Given** some selected CTKs fail validation (missing prerequisites), **When** bulk update runs, **Then** successful updates complete and failures are reported with specific error details for each failed record

---

### Edge Cases

- What happens when a user with read-only permissions (e.g., Pimpinan) accesses the CTK index? Only "View" action should be visible, "Kelola Progress" should be hidden
- What happens when a CTK is in a transition state (entity handoff from LPK to PT)? Actions should be disabled or restricted until transition completes
- What happens when clicking "View" on a CTK that has been deleted by another user in a concurrent session? System should show appropriate "record not found" error
- What happens when attempting to update progress on a CTK that reached final stage (Terbang)? System should prevent further updates as final stages are immutable
- What happens when network connectivity fails during a progress update? System should show error and allow retry without data loss
- What happens on mobile or tablet screens? Action buttons should remain accessible, possibly in a dropdown menu for space efficiency
- What happens when a CTK has validation errors preventing stage advancement? The "Kelola Progress" action should show clear error messages indicating which fields need correction
- What happens when audit trail logging fails during progress update? The update should still complete but an alert should be logged for system administrators

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST display a "View" action button on each CTK row in the index table
- **FR-002**: System MUST display a "Kelola Progress" action button on each CTK row for users with appropriate permissions
- **FR-003**: Clicking "View" MUST navigate to the CTK detail page (ViewCTK route)
- **FR-004**: Clicking "Kelola Progress" MUST open a modal or form allowing stage and progress updates for that specific CTK
- **FR-005**: System MUST enforce role-based authorization for the "Kelola Progress" action based on user role and CTK entity/stage
- **FR-006**: Users with only LPK permissions MUST only see "Kelola Progress" for CTKs in LPK stages (1-5)
- **FR-007**: Users with only PT permissions MUST only see "Kelola Progress" for CTKs in PT stages (6-15)
- **FR-008**: Pimpinan role MUST only see "View" action (read-only access)
- **FR-009**: Super Admin MUST see both "View" and "Kelola Progress" for all CTKs
- **FR-010**: System MUST validate prerequisites and enforce business rules before allowing stage updates (no backward or skipped stages, prerequisites must be met, stage 15 "Terbang" is immutable)
- **FR-011**: Progress updates MUST create audit trail entries recording user, timestamp, old stage, new stage, and any related changes
- **FR-012**: After successful progress update, system MUST refresh the index table to show updated status and progress without full page reload
- **FR-013**: Action buttons MUST meet visibility standards: icon + text label, minimum 44px touch target, contrast ratio ≥4.5:1 (WCAG AA compliance)
- **FR-014**: System MUST handle concurrent updates using optimistic locking with version checking; when conflict detected, show notification "This CTK was updated by [User] at [Time]. Please reload and reapply changes."
- **FR-015**: System MUST provide bulk selection capability for applying progress updates to multiple CTKs at once
- **FR-016**: Bulk progress updates MUST validate each selected CTK individually and report successes/failures separately
- **FR-017**: Action buttons MUST be responsive and accessible on mobile devices

### Key Entities

- **CTK (Calon Tenaga Kerja)**: Prospective worker record with current stage, entity, progress tracking, and authorization requirements
  - Current Stage: Determines which actions are available and which users can modify
  - Current Entity: LPK or PT, used for role-based access control
  - Completion Progress: Visual indicator updated after stage changes
- **User Roles**: Admin LPK, Admin PT, HR PT, Legal PT, Keuangan PT, Keuangan LPK, Pimpinan, Super Admin
  - Determines action button visibility and progress update authorization
- **Stage Prerequisites**: Business rules defining required conditions before stage advancement (e.g., payments complete, documents uploaded)
  - Validated before allowing progress updates

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Administrators can navigate from CTK index to CTK detail page in 1 click using the "View" action button
- **SC-002**: Administrators can update CTK stage progress in under 10 seconds from the index page without navigating to edit page
- **SC-003**: 100% of action buttons correctly respect role-based authorization rules
- **SC-004**: Progress updates complete within 2 seconds and index table reflects changes within 3 seconds
- **SC-005**: Users report reduced clicks for common workflows (qualitative feedback, aspirational target: 50% reduction vs navigating to edit page)
- **SC-006**: Zero unauthorized progress updates due to proper role enforcement on action buttons
- **SC-007**: Bulk progress updates process at least 10 CTKs per second with proper validation and error reporting
- **SC-008**: Action buttons remain fully functional and accessible on mobile devices with screen widths down to 375px

## Assumptions

- The existing CTK authorization logic in the Resource (getEloquentQuery) is sufficient for determining action button visibility
- Users are familiar with the CTK stage workflow and understand which stages they are authorized to manage
- The CTK model has existing methods or can add methods to validate stage advancement prerequisites
- Filament's table action system supports role-based visibility and modal-based actions
- The progress update modal should show current stage, next available stages, and relevant stage-specific fields (MCU result, payment status, document uploads, etc.)
- The existing ViewCTK page provides sufficient detail for the "View" action target
- Concurrent update handling can use optimistic locking or last-write-wins strategy with conflict detection
- Bulk actions should be limited to CTKs the user is authorized to modify (filtered by getEloquentQuery)
- Mobile users primarily need "View" action easily accessible, "Kelola Progress" can be in a secondary menu if needed for space
- The action buttons will appear in the standard Filament table actions column (typically right side of table)
