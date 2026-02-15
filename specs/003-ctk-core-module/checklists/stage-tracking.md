# Stage Tracking & Workflow Visualization Checklist: CTK Core Module

**Purpose**: Validate requirements quality for CTK stage tracking visualization with automatic checkmarks and document proof monitoring  
**Created**: February 15, 2026  
**Feature**: [spec.md](../spec.md) | [plan.md](../plan.md) | [tasks.md](../tasks.md)

**Focus**: Visual workflow tracking & checkmarks in CTK profile view with priority on critical stages (MCU, Payment, Visa, Departure)

---

## Requirement Completeness

- [ ] CHK001 - Are visual workflow display requirements defined for the CTK profile view? [Gap]
- [ ] CHK002 - Are automatic checkmark trigger conditions specified for each of the 15 stages? [Gap]
- [ ] CHK003 - Are document proof requirements explicitly mapped to each stage requiring documents? [Completeness, Spec §FR-007]
- [ ] CHK004 - Are stage completion criteria defined beyond just document uploads (e.g., status changes, approvals)? [Gap]
- [ ] CHK005 - Are requirements specified for showing "current stage" vs "completed stages" vs "pending stages"? [Gap]
- [ ] CHK006 - Are visual indicator requirements defined (checkmarks, icons, colors, badges)? [Gap]
- [ ] CHK007 - Are requirements defined for displaying document proof evidence alongside each stage? [Gap]
- [ ] CHK008 - Is the relationship between document upload and stage completion checkmark clearly documented? [Gap]
- [ ] CHK009 - Are requirements specified for showing partial completion (e.g., 3/5 payments complete)? [Gap]
- [ ] CHK010 - Are monitoring/reporting requirements defined for stage completion tracking? [Gap]

## Requirement Clarity - Critical Stages

- [ ] CHK011 - Are MCU stage checkmark criteria explicitly quantified (FIT status AND examination date)? [Clarity, Spec §FR-004]
- [ ] CHK012 - Are payment stage completion requirements specific (all 5 payments with proof uploaded)? [Ambiguity, Spec §FR-006]
- [ ] CHK013 - Is "automatic checkmark" defined with exact trigger logic (on save, on upload, on status change)? [Ambiguity]
- [ ] CHK014 - Are visa stage completion criteria measurable (visa number, issuance date, document uploaded)? [Clarity, Spec §FR-015]
- [ ] CHK015 - Are departure (Terbang) stage prerequisites explicitly enumerated with checkable conditions? [Clarity, Spec §FR-018]
- [ ] CHK016 - Is "document proof required" quantified (1 document minimum, specific file types, max size)? [Clarity, Spec §FR-009]
- [ ] CHK017 - Are stage progression rules defined (can view future stages, or only show completed/current)? [Gap]

## Requirement Consistency

- [ ] CHK018 - Are checkmark conditions consistent across all document-requiring stages? [Consistency, Spec §FR-007]
- [ ] CHK019 - Do stage advancement requirements align with checkmark completion criteria? [Consistency, Spec §FR-003]
- [ ] CHK020 - Are visual indicators consistent with existing Filament UI patterns used elsewhere? [Consistency]
- [ ] CHK021 - Do entity-based access controls (LPK vs PT) align with workflow visualization scope? [Consistency, Spec §FR-020]
- [ ] CHK022 - Are document upload requirements consistent with document type validations? [Consistency, Spec §FR-009]

## Acceptance Criteria Quality

- [ ] CHK023 - Can "automatic checkmark appears" be objectively tested with specific actions? [Measurability]
- [ ] CHK024 - Can "stage completion" be objectively verified (database field, UI element, audit log)? [Measurability]
- [ ] CHK025 - Are success criteria defined for "all stages checked when Terbang reached" requirement? [Acceptance Criteria, Gap]
- [ ] CHK026 - Can "document proof visible" be measured with specific UI assertions? [Measurability]
- [ ] CHK027 - Are acceptance criteria defined for stage visualization accessibility? [Gap]

## Scenario Coverage - Critical Paths

- [ ] CHK028 - Are requirements defined for displaying incomplete payment stages (e.g., 2/5 paid)? [Coverage, Gap]
- [ ] CHK029 - Are requirements specified for MCU UNFIT scenario affecting workflow visualization? [Coverage, Spec §FR-005]
- [ ] CHK030 - Are requirements defined for showing screening failure (Tidak Lolos) in workflow? [Coverage, Spec §FR-013]
- [ ] CHK031 - Are requirements specified for visa rejection scenario display in workflow? [Coverage, Edge Case]
- [ ] CHK032 - Are requirements defined for Medical Full expiration warning in workflow? [Coverage, Edge Case]
- [ ] CHK033 - Are requirements specified for displaying CTK cancellation ("Batal") status in workflow? [Coverage, Edge Case]

## Scenario Coverage - User Interactions

- [ ] CHK034 - Are requirements defined for user clicking on a stage (expand details, show documents)? [Coverage, Gap]
- [ ] CHK035 - Are requirements specified for uploading documents directly from workflow view? [Gap]
- [ ] CHK036 - Are requirements defined for viewing document proof (preview, download) from workflow? [Gap]
- [ ] CHK037 - Are requirements specified for administrator override of checkmarks (with audit)? [Coverage, Gap]

## Visual Design & UX Requirements

- [ ] CHK038 - Are layout requirements specified (horizontal timeline, vertical list, grid, accordion)? [Gap]
- [ ] CHK039 - Are color/visual state requirements defined (completed=green, current=blue, pending=gray)? [Gap]
- [ ] CHK040 - Are spacing and visual hierarchy requirements specified for 15-stage workflow? [Gap]
- [ ] CHK041 - Are mobile/responsive requirements defined for workflow visualization? [Gap]
- [ ] CHK042 - Are icon/symbol requirements specified for each stage type (MCU, Payment, Document, etc.)? [Gap]
- [ ] CHK043 - Are animation/transition requirements defined for checkmark appearance? [Gap]

## Non-Functional Requirements

- [ ] CHK044 - Are loading performance requirements specified for workflow visualization with documents? [Gap]
- [ ] CHK045 - Are accessibility requirements defined for keyboard navigation through stages? [Gap, NFR]
- [ ] CHK046 - Are screen reader requirements specified for stage status announcements? [Gap, NFR]
- [ ] CHK047 - Are requirements defined for workflow visualization with poor network (offline indicators)? [Gap, NFR]

## Dependencies & Assumptions

- [ ] CHK048 - Is the dependency on existing CTKDocument model and file storage documented? [Dependency, Spec §FR-008]
- [ ] CHK049 - Is the assumption that "Terbang = all stages complete" validated as a requirement? [Assumption]
- [ ] CHK050 - Are requirements specified for backward compatibility with existing CTK records lacking documents? [Dependency, Gap]
- [ ] CHK051 - Is the integration with stage_transitions table for audit trail documented? [Dependency, Spec §FR-019]

## Entity & Permission Requirements

- [ ] CHK052 - Are requirements defined for LPK users viewing only stages 1-5 in workflow visualization? [Coverage, Spec §FR-020]
- [ ] CHK053 - Are requirements defined for PT users viewing only stages 6-15 in workflow visualization? [Coverage, Spec §FR-020]
- [ ] CHK054 - Are requirements specified for Pimpinan viewing complete 15-stage workflow (read-only)? [Coverage, Spec §FR-020]
- [ ] CHK055 - Are permission requirements defined for who can mark stages complete/upload documents? [Gap]

## Data Integrity & Business Rules

- [ ] CHK056 - Are requirements defined for preventing checkmark manipulation (only via proper stage advancement)? [Gap]
- [ ] CHK057 - Are requirements specified for checkmark immutability once "Terbang" status reached? [Completeness, Spec §FR-019]
- [ ] CHK058 - Are requirements defined for handling missing/deleted document files affecting checkmarks? [Coverage, Exception Flow]
- [ ] CHK059 - Are requirements specified for re-verification when documents expire (e.g., Medical Full)? [Coverage, Edge Case]

## Audit & Traceability

- [ ] CHK060 - Are requirements defined for logging automatic checkmark changes to audit trail? [Gap]
- [ ] CHK061 - Are requirements specified for showing who uploaded proof documents in workflow view? [Traceability, Spec §FR-008]
- [ ] CHK062 - Are requirements defined for displaying timestamps of stage completion in workflow? [Gap]

## Critical Stage Gating - Payment

- [ ] CHK063 - Are requirements explicitly defined for 5 payment stages with separate checkmarks? [Completeness, Spec §FR-006]
- [ ] CHK064 - Are requirements specified for payment proof (bukti pembayaran) display in workflow? [Gap]
- [ ] CHK065 - Are requirements defined for preventing advancement beyond Payment stage without 5/5 complete? [Clarity, Spec §FR-006]
- [ ] CHK066 - Are requirements specified for showing payment amounts and bank names in workflow visualization? [Gap]

## Critical Stage Gating - MCU

- [ ] CHK067 - Are requirements defined for visual distinction between FIT/UNFIT/PENDING MCU status? [Gap, Spec §FR-004]
- [ ] CHK068 - Are requirements specified for blocking workflow progression when MCU = UNFIT/PENDING? [Clarity, Spec §FR-005]
- [ ] CHK069 - Are requirements defined for displaying MCU examination date and clinic in workflow? [Gap]

## Critical Stage Gating - Visa

- [ ] CHK070 - Are requirements defined for showing visa application vs issuance status in workflow? [Gap, Spec §FR-015]
- [ ] CHK071 - Are requirements specified for displaying visa number and expiry date in workflow view? [Gap, Spec §FR-015]
- [ ] CHK072 - Are requirements defined for preventing OPP stage checkmark without visa issuance? [Clarity, Spec §FR-015]

## Critical Stage Gating - Departure

- [ ] CHK073 - Are requirements defined for "all stages checked" validation before Terbang status? [Gap]
- [ ] CHK074 - Are requirements specified for displaying departure date prominently in workflow? [Gap, Spec §FR-018]
- [ ] CHK075 - Are requirements defined for visual indicator showing CTK successfully departed (final success state)? [Gap]

## Edge Cases & Error Scenarios

- [ ] CHK076 - Are requirements defined for displaying stages when documents uploaded but status not advanced? [Coverage, Edge Case]
- [ ] CHK077 - Are requirements specified for showing error states (e.g., incomplete documents, expired visa)? [Coverage, Exception Flow]
- [ ] CHK078 - Are requirements defined for displaying manual corrections/overrides in workflow? [Coverage, Edge Case]
- [ ] CHK079 - Are requirements specified for handling stage reversions (e.g., failed Medical Full after passing previous stages)? [Coverage, Edge Case]
- [ ] CHK080 - Are requirements defined for displaying partial document uploads (3/5 WP documents uploaded)? [Coverage, Gap]

---

## Notes

- **Traceability**: 52/80 items (65%) reference spec sections, gaps, or quality markers - consider adding more specific spec references
- **Priority Focus**: CHK011-CHK017, CHK028-CHK033, CHK063-CHK075 address critical stages (MCU, Payment, Visa, Departure)
- **Next Steps**: Review spec.md and plan.md to add explicit requirements for gaps identified (especially CHK001-CHK010, CHK034-CHK043)
- **Implementation Note**: This checklist validates REQUIREMENTS quality, not implementation. Use for requirements review before development.
