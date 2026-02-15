# Requirements Quality Checklist: CTK Status & Progress Display

**Purpose**: Validate requirements clarity and completeness for how CTK status and progress information is displayed in the user interface, specifically addressing redundancy between "Status CTK" and "Progress Tahapan CTK" sections.

**Created**: February 15, 2026  
**Focus**: UI Requirements Clarity - status/progress display requirements  
**Scope**: Narrow - status/progress display only  
**Depth**: Standard  
**Related Spec**: [spec.md](../spec.md) §FR-026, §FR-027, §FR-032, §FR-038, §FR-039, §FR-040

---

## Requirement Completeness

- [ ] CHK001 - Are UI layout requirements defined for the "Status CTK" section (what fields to display, in what order, with what labels)? [Gap]
- [ ] CHK002 - Are UI layout requirements defined for the "📋 Progress Tahapan CTK" section (visual structure, positioning, interactions)? [Gap]
- [ ] CHK003 - Is it specified whether "Status CTK" and "Progress Tahapan CTK" should both appear on the CTK detail page, or only one? [Gap]
- [ ] CHK004 - Are requirements defined for how "current status" differs from "current stage" in the CTK entity definition? [Ambiguity, Spec §CTK Entity]
- [ ] CHK005 - Is the distinction between "status" (e.g., FIT/UNFIT) and "stage" (e.g., Stage 3, MCU) clearly documented in requirements? [Clarity, Gap]
- [ ] CHK006 - Are requirements specified for what data the "Status CTK" section should display (current status text, stage number, entity, timestamp, etc.)? [Gap]
- [ ] CHK007 - Are requirements specified for when/where the "X/15" progress format should appear (list view only, detail view, both)? [Clarity, Spec §FR-039]
- [ ] CHK008 - Are requirements defined for displaying progress in the CTK list table vs CTK detail page? [Completeness, Gap]

## Requirement Clarity

- [ ] CHK009 - Is "current status" clearly defined with all possible values and meanings (e.g., is it the stage name, or MCU result, or both)? [Ambiguity, Spec §FR-026, §FR-027]
- [ ] CHK010 - Is "current stage" clearly defined as distinct from "current status"? [Ambiguity, Spec §FR-026]
- [ ] CHK011 - Is the term "visual progress indicator" quantified with specific UI component types (progress bar, badge, percentage, etc.)? [Clarity, Spec §FR-032, §FR-040]
- [ ] CHK012 - Are the visual design requirements for the progress indicator specified (color coding, size, positioning)? [Gap, Spec §FR-040]
- [ ] CHK013 - Is "matching the structure defined in alur_ctk.md template" sufficiently detailed for UI implementation? [Clarity, Spec §FR-038]
- [ ] CHK014 - Are the checkbox indicators (`[ ]` vs `[x]`) specified as visual UI elements or just data representations? [Ambiguity, Spec §FR-038]
- [ ] CHK015 - Is it specified how "Soal/Berkas" status ("Upload / Lengkap") is displayed in the progress section? [Gap, Spec §FR-038, alur_ctk.md]
- [ ] CHK016 - Is the format for displaying "Entitas" (LPK/PT) in the Status CTK section specified? [Gap]

## Requirement Consistency

- [ ] CHK017 - Do FR-026 and FR-027 requirements for displaying "current status" align with what information is available in FR-038's visual workflow? [Consistency, Spec §FR-026, §FR-027, §FR-038]
- [ ] CHK018 - Is there consistency between FR-032 ("visual progress indicator") and FR-040 (progress bar or badge) regarding what should be displayed? [Consistency, Spec §FR-032, §FR-040]
- [ ] CHK019 - Do the completion progress requirements (FR-039, FR-040) conflict with or complement the visual workflow requirements (FR-038)? [Consistency, Spec §FR-038, §FR-039, §FR-040]
- [ ] CHK020 - Is there redundancy between "current status, current stage" (FR-026) and the comprehensive stage workflow display (FR-038)? [Redundancy, Spec §FR-026, §FR-038]
- [ ] CHK021 - Are requirements consistent about whether to show summary status info + detailed progress, or just detailed progress? [Consistency, Gap]

## Acceptance Criteria Quality

- [ ] CHK022 - Can "clearly distinguishable" (SC-017) be objectively verified with specific visual design criteria? [Measurability, Spec §SC-017]
- [ ] CHK023 - Are acceptance criteria defined for how the "Status CTK" section should appear and behave? [Gap, Coverage]
- [ ] CHK024 - Are acceptance criteria defined for user interactions with the progress display (click to expand, hover tooltips, etc.)? [Gap]
- [ ] CHK025 - Is there a measurable criterion for when current status information is redundant vs complementary to progress display? [Measurability, Gap]

## Scenario Coverage

- [ ] CHK026 - Are requirements defined for how status/progress appears in the CTK list view vs detail view? [Coverage, Spec §FR-039, §FR-038]
- [ ] CHK027 - Are requirements specified for displaying status/progress on mobile or responsive layouts? [Coverage, Gap]
- [ ] CHK028 - Are requirements defined for what happens when CTK is at different stages (early stage, mid-process, final stage)? [Coverage, Gap]
- [ ] CHK029 - Are requirements specified for showing status when CTK has unusual states (UNFIT, failed screening, cancelled)? [Edge Case, Gap]
- [ ] CHK030 - Are requirements defined for real-time updates to status/progress display when data changes? [Coverage, Gap]

## Edge Case Coverage

- [ ] CHK031 - Are display requirements defined for when CTK has partial stage completion (e.g., 3/5 payments complete)? [Edge Case, Spec §FR-041]
- [ ] CHK032 - Are requirements specified for displaying status when required data is missing or incomplete? [Edge Case, Gap]
- [ ] CHK033 - Is fallback behavior defined when "alur_ctk.md template" structure changes or is unavailable? [Edge Case, Spec §FR-038]
- [ ] CHK034 - Are requirements defined for displaying very long status text or stage names (text truncation, wrapping)? [Edge Case, Gap]

## Non-Functional Requirements

- [ ] CHK035 - Are performance requirements defined for rendering the progress display (relates to SC-015's 2-second load time)? [Completeness, Spec §SC-015]
- [ ] CHK036 - Are accessibility requirements specified for the status/progress display (screen reader labels, keyboard navigation)? [Gap]
- [ ] CHK037 - Are requirements defined for the visual hierarchy between status summary and detailed progress sections? [Gap]
- [ ] CHK038 - Are localization requirements specified for status/stage labels displayed in the UI? [Gap]

## Dependencies & Assumptions

- [ ] CHK039 - Is the dependency on alur_ctk.md template structure explicitly documented as a requirement? [Dependency, Spec §FR-038]
- [ ] CHK040 - Is it documented whether the "Status CTK" section is a legacy requirement or serves a distinct purpose from progress display? [Assumption, Gap]
- [ ] CHK041 - Are assumptions documented about what information users need at a glance vs in detail? [Assumption, Gap]

## Ambiguities & Conflicts

- [ ] CHK042 - Is there ambiguity about whether users should see both "Status CTK" (summary) AND "Progress Tahapan CTK" (detailed) simultaneously? [Ambiguity, Conflict]
- [ ] CHK043 - Is the relationship between "Status Saat Ini" field and the 15-stage progress workflow clearly defined? [Ambiguity]
- [ ] CHK044 - Are there conflicting requirements about displaying current stage number vs stage name vs completion status? [Conflict, Spec §FR-026]
- [ ] CHK045 - Is it clear whether "Tahap" (Stage) in "Status CTK" should show stage number (1-15) or stage name (MCU, Pembayaran, etc.)? [Ambiguity]
- [ ] CHK046 - Does the spec clarify whether removing "Status CTK" section would violate any stated requirements? [Gap, Decision Point]

## Traceability

- [ ] CHK047 - Are all status display requirements traceable back to user stories or business needs? [Traceability]
- [ ] CHK048 - Is there a clear mapping between UI requirements (FR-038, FR-039, FR-040) and acceptance criteria (SC-015, SC-017)? [Traceability, Spec §FR-038-040, §SC-015, §SC-017]
- [ ] CHK049 - Are requirements identifiers used consistently when referring to status/progress display? [Traceability]
- [ ] CHK050 - Is the UI redundancy issue (Status CTK vs Progress Tahapan CTK) documented as a known gap or design decision? [Gap, Documentation]

---

## Summary

**Total Items**: 50  
**Key Quality Dimensions Tested**: Completeness (16), Clarity (12), Consistency (5), Measurability (3), Coverage (11), Dependencies (3)

**Critical Findings to Address**:
1. **Redundancy Not Addressed**: Requirements do not explicitly state whether "Status CTK" and "Progress Tahapan CTK" should coexist or consolidate (CHK003, CHK020, CHK042, CHK046, CHK050)
2. **Ambiguous Terminology**: "current status" vs "current stage" distinction is unclear (CHK004, CHK005, CHK009, CHK010, CHK043, CHK045)
3. **Missing UI Specifications**: Layout, visual design, and component structure requirements are lacking (CHK001, CHK002, CHK006, CHK012, CHK037)
4. **Context-Specific Requirements Gap**: Requirements don't clarify when/where each display type should appear (CHK003, CHK007, CHK008, CHK026)
5. **Measurability Issues**: Visual design terms like "clearly distinguishable" lack objective criteria (CHK022, CHK025)
