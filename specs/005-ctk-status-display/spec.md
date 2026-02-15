# Feature Specification: CTK Index Status Display Simplification

**Feature Branch**: `005-ctk-status-display`  
**Created**: February 15, 2026  
**Status**: Draft  
**Input**: User description: "pada index ctk ubah untuk status menjadi (lengkap/belum lengkap) , tahap hapus, hanya Progress Tahapan CTK aja"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View CTK Status at a Glance (Priority: P1)

As an HR administrator viewing the CTK (Calon Tenaga Kerja/Prospective Worker) list, I need to quickly identify which candidates have completed all required steps versus those who still have incomplete requirements, so I can prioritize follow-up actions and track overall recruitment progress.

**Why this priority**: This is the primary use case for the CTK list page - enabling quick status assessment of all candidates. Without clear status visibility, administrators waste time drilling into individual records to determine completion status.

**Independent Test**: Can be fully tested by navigating to the CTK list page and verifying that each record displays either "Lengkap" (Complete) or "Belum Lengkap" (Incomplete) in the Status column, delivering immediate visibility into candidate completion status without requiring further clicks.

**Acceptance Scenarios**:

1. **Given** I am viewing the CTK list page, **When** a candidate has completed all required CTK steps, **Then** the Status column displays "Lengkap" (Complete)
2. **Given** I am viewing the CTK list page, **When** a candidate has one or more incomplete steps, **Then** the Status column displays "Belum Lengkap" (Incomplete)
3. **Given** I am viewing the CTK list page, **When** I scan the Status column, **Then** I can immediately distinguish between complete and incomplete candidates without additional information

---

### User Story 2 - Monitor Detailed Progress (Priority: P2)

As an HR administrator, I need to see the detailed progress of each CTK candidate through their stages, so I can understand exactly how far along they are in the process and identify candidates who may be stuck at specific stages.

**Why this priority**: While overall status provides quick filtering, detailed progress tracking helps identify exactly where candidates are in the pipeline and which specific steps remain incomplete.

**Independent Test**: Can be fully tested by examining the Progress column on the CTK list and verifying it shows the stage number and completion percentage (e.g., "1/15 7%"), providing granular tracking independent of the binary status.

**Acceptance Scenarios**:

1. **Given** I am viewing the CTK list page, **When** I look at the Progress column, **Then** I see the current step number, total steps, and completion percentage (format: "X/Y Z%")
2. **Given** I am viewing a candidate's progress, **When** they have completed 1 out of 15 steps, **Then** the Progress column shows "1/15 7%"
3. **Given** I am comparing multiple candidates, **When** I sort or filter by progress, **Then** I can identify candidates at similar stages in their CTK journey

---

### User Story 3 - Simplified Table Navigation (Priority: P3)

As an HR administrator, I need a streamlined CTK list view with only essential columns, so I can quickly scan relevant information without being overwhelmed by redundant data fields.

**Why this priority**: Removing unnecessary columns improves usability and reduces cognitive load, but is lower priority than ensuring status and progress information are correctly displayed.

**Independent Test**: Can be fully tested by verifying the Tahap (Stage) column is completely removed from the table view and does not appear in any table display mode (default view, filtered view, etc.).

**Acceptance Scenarios**:

1. **Given** I am viewing the CTK list page, **When** the page loads, **Then** the Tahap (Stage) column does not appear in the table
2. **Given** I am viewing the table columns, **When** I review available information, **Then** I see NIK, Nama Lengkap, Status (Lengkap/Belum Lengkap), Entitas, Progress, No. Telepon, and Dibuat columns only
3. **Given** I need to understand a candidate's current stage, **When** I look at the Progress column, **Then** the information previously in Tahap is sufficiently represented by the progress indicator

---

### Edge Cases

- What happens when a CTK record has 0/15 progress (0%)? Status should display "Belum Lengkap" (Incomplete)
- What happens when a CTK record has 15/15 progress (100%)? Status should display "Lengkap" (Complete)
- What happens when displaying the table on mobile or smaller screens? The simplified column structure should improve responsive layout
- What happens when users have existing filters or sorts applied to the Tahap column? These filters should be removed or migrated to use equivalent Progress-based filtering
- What happens when exporting the CTK list? The export should reflect the new column structure (Status as Lengkap/Belum Lengkap, no Tahap column)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST replace the existing Status column display logic to show "Lengkap" (Complete) when all CTK steps are completed
- **FR-002**: System MUST display "Belum Lengkap" (Incomplete) in the Status column when one or more CTK steps remain incomplete
- **FR-003**: System MUST completely remove the Tahap (Stage) column from the CTK list table display
- **FR-004**: System MUST retain the Progress column showing the format "X/Y Z%" where X is completed steps, Y is total steps, and Z is completion percentage
- **FR-005**: System MUST preserve all other existing columns in the CTK list (NIK, Nama Lengkap, Entitas, No. Telepon, Dibuat)
- **FR-006**: System MUST ensure the Status determination logic accurately reflects the underlying CTK completion state
- **FR-007**: System MUST maintain existing search, filter, and sort functionality for all remaining columns
- **FR-008**: System MUST update any column-specific filters or toggles to reflect the new column structure
- **FR-009**: System MUST preserve existing row actions and table functionality (view, edit, delete, etc.)
- **FR-010**: Status column MUST be sortable to allow grouping by complete/incomplete candidates

### Key Entities

- **CTK (Calon Tenaga Kerja)**: Prospective worker record containing personal information, completion status, progress through required stages, and contact details
  - Status calculation: Based on completion of all required CTK steps
  - Progress tracking: Numerator (completed steps), denominator (total steps), percentage
  - Related to Entity (Entitas): The organization unit associated with the candidate (e.g., LPK)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR administrators can determine if a candidate is complete or incomplete within 1 second of viewing the CTK list
- **SC-002**: The CTK list table displays only 7 columns (reduced from 8), improving scanability and reducing horizontal scrolling
- **SC-003**: 100% of CTK records display accurate completion status matching their underlying progress state
- **SC-004**: Users can successfully sort and filter the CTK list by the new Status column (Lengkap/Belum Lengkap)
- **SC-005**: The Progress column continues to provide granular step-by-step tracking for all candidates
- **SC-006**: Page load time for the CTK list remains unchanged or improves compared to the previous implementation
- **SC-007**: Zero user interface errors or broken layouts when viewing the CTK list on desktop and mobile devices

## Assumptions

- The existing Progress column calculation accurately represents the completion state needed for Status determination
- "Lengkap" (Complete) means 100% progress (all steps completed), while "Belum Lengkap" (Incomplete) means <100% progress
- Removing the Tahap (Stage) column will not impact any critical reporting or audit requirements
- Users do not rely on the separate Tahap column for critical decision-making since Progress provides equivalent information
- The Status column will appear in the same position as the previous Status column for visual consistency
- Existing permissions and access controls for viewing the CTK list remain unchanged
- The CTK data model underlying the list view does not need modification - only the display layer changes
