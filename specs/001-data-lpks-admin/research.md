# Research: Data Siswa LPK Administration

## Decision 1: Use a dedicated `SiswaLPK` model and `siswa_lpk` table

- **Decision**: Implement Siswa LPK registration records in a dedicated model and table named `SiswaLPK` and `siswa_lpk`.
- **Rationale**: The source sheet uses student-oriented terminology, the feature is strictly for LPK administration, and a separate table preserves entity isolation while keeping the resource conceptually simpler than the CTK lifecycle model.
- **Alternatives considered**:
  - Reuse `CTK`: rejected because CTK is the canonical cross-lifecycle placement entity and would overload this feature with unrelated stages and PT concerns.
  - Use a generic `Participant` model: rejected because the repo already uses explicit Indonesian business naming and the source data is clearly student-oriented.

## Decision 2: Keep the admin UI as a standard Filament resource in the existing admin panel

- **Decision**: Build the feature as a standard Filament resource discovered by `AdminPanelProvider` under the existing admin panel.
- **Rationale**: The repository already auto-discovers resources from `app/Filament/Resources`, and the feature needs standard create, list, view, and edit interactions that fit Filament directly.
- **Alternatives considered**:
  - Custom Livewire pages: rejected because CRUD resource behavior is already covered by Filament resource patterns.
  - Separate panel: rejected because the feature belongs inside the current admin panel and does not justify a new panel boundary.

## Decision 3: Keep `program_pendidikan` as a string in the first slice

- **Decision**: Store the education program as a required string field rather than a separate program master table.
- **Rationale**: The spec only requires search, display, create, and update against one source sheet. A separate master table would add migration, relationship, and management overhead without direct user value for the MVP.
- **Alternatives considered**:
  - Normalize to a `program_pendidikan` table: rejected for the initial slice because no program management workflow exists in the spec.
  - Use a hard-coded enum: rejected because source values may evolve and are better handled as admin-entered strings in the first iteration.

## Decision 4: Apply soft deletes, activity logging, and creator/updater attribution

- **Decision**: Mirror repository patterns by using soft deletes, Spatie activity logging, and `created_by`/`updated_by` relationships.
- **Rationale**: The constitution requires retention and auditability for critical operations, and existing LPK-facing models already use this pattern.
- **Alternatives considered**:
  - Hard deletes only: rejected because it conflicts with retention expectations.
  - Activity logging without actor fields: rejected because it weakens audit clarity in admin workflows.

## Decision 5: Enforce uniqueness and cross-field validation at both UI and database levels

- **Decision**: Validate `nomor_induk` uniqueness and ensure `tanggal_lahir <= tanggal_masuk` in the Filament resource flow, backed by a database unique index for `nomor_induk`.
- **Rationale**: The spec explicitly calls out duplicate prevention and date consistency. Database constraints protect integrity even if UI validation is bypassed.
- **Alternatives considered**:
  - UI validation only: rejected because it does not fully protect data integrity.
  - Database constraint only: rejected because it produces a worse admin experience without earlier feedback.

## Decision 6: Start with monolithic resource form and table definitions

- **Decision**: Keep `form()` and `table()` definitions inside `SiswaLPKResource.php` for the first implementation slice.
- **Rationale**: Existing resources such as `EmployeeLPKResource` use this pattern successfully, and the feature has a moderate field count that does not require extra schema classes yet.
- **Alternatives considered**:
  - Split dedicated schema and table classes immediately: rejected as unnecessary complexity for one CRUD resource.

## Decision 7: Cover behavior with focused PHPUnit and Livewire tests

- **Decision**: Add one feature test suite for Filament CRUD/search/validation behavior and one unit test suite for model defaults, soft deletes, and activity logging.
- **Rationale**: This matches existing repository testing practice and satisfies the constitution requirement for behavior-critical test coverage.
- **Alternatives considered**:
  - Manual verification only: rejected because repository rules require automated testing.
  - Browser/E2E-only coverage: rejected because the existing suite already relies on faster PHPUnit and Livewire tests.

## Decision 8: Provide Excel export through the Filament resource

- **Decision**: Provide an Excel export action from the Siswa LPK list using the existing export approach already used in Filament resources in this repository.
- **Rationale**: Excel export capability is constitution-mandated and matches administrative usage for tabular data review and follow-up.
- **Alternatives considered**:
  - Defer export: rejected because it leaves a constitution-mandated requirement uncovered.
  - Build a separate export-only page: rejected because the list resource is the simplest effective place for export.
