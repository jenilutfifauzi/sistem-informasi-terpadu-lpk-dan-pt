<!--
Sync Impact Report

- Version change: template → 1.0.0
- Modified principles: (added) Data Integrity & Single Source of Truth; Multi-Entity Isolation; Role-Based Access Control & Least Privilege; Auditability & Compliance; Incremental Delivery & Simplicity
- Added sections: Operational Constraints; Development Workflow
- Removed sections: none (placeholders replaced)
- Templates requiring updates: 
  ✅ plan-template.md (Constitution Check section aligns with principles)
  ✅ spec-template.md (Requirements section supports principle validation)
  ✅ tasks-template.md (Phase structure supports incremental delivery principle)
  ⚠ Commands/*.md (pending review for agent-agnostic references)
- Follow-up TODOs: RATIFICATION_DATE — provide original adoption date when known
-->

# Sistem Informasi Terpadu PT PJTKI & LPK Constitution

## Core Principles

### I. Data Integrity & Single Source of Truth

The system MUST treat CTK (Calon Tenaga Kerja) records as canonical single-source entities. All CTK lifecycle changes MUST be represented as auditable state transitions on the canonical CTK record. The system MUST prevent creation of duplicate CTK records by enforcing uniqueness rules, deduplication processes, and canonical identifiers.

**Rationale**: The product goal is a single source of truth for CTK to avoid duplicates, ease auditing, and simplify reporting.

### II. Multi-Entity Isolation

Data belonging to different legal entities (PT vs LPK) MUST be isolated logically and by access controls. Cross-entity edits or direct data sharing are PROHIBITED unless an explicit, auditable transfer workflow is used (e.g., LPK → PT CTK transfer with recorded consent and change log).

**Rationale**: Compliance and regulatory separation require clear boundaries between PT and LPK data sets.

### III. Role-Based Access Control & Least Privilege

All access to application features and data MUST be governed by RBAC. Roles defined in the PRD (Admin LPK, Instruktur, HR PT, Admin PT, Legal PT, Keuangan PT/LPK, Pimpinan) MUST map to explicit permission sets. The system MUST implement least-privilege principles: default to deny, grant only necessary permissions, and require review for elevated access.

**Rationale**: Minimizes risk of unauthorized changes and supports auditability and compliance requirements.

### IV. Auditability & Compliance

All security-sensitive operations and CTK lifecycle transitions MUST be logged with sufficient context (who, when, what, why). CTK records in final/legal stages (e.g., 'Siap Berangkat', 'Terbang') MUST be immutable except via a documented, auditable correction process. Export and retention policies MUST be defined and testable.

**Rationale**: The PRD requires audit logs, compliance, and locked state for final CTK stages.

### V. Incremental Delivery & Simplicity

Development MUST favor iterative, demonstrable increments (MVP slices) that deliver value per the PRD priorities. Features MUST be implemented with simplest effective design; complexity requires explicit justification and approval. Tests (unit, integration, and where appropriate Livewire/Filament tests) MUST accompany behavior-critical changes.

**Rationale**: Reduce risk, accelerate feedback, and align with Filament-driven server-side UI approach.

## Operational Constraints

The project MUST follow the technology constraints in the repository:
- PHP 8.4.x
- Laravel 10+/11 conventions as present in the codebase
- Filament v4 for admin panel development
- Livewire v3 for reactive components
- MySQL/MariaDB for data storage
- Redis (optional) for caching/sessions

Non-functional requirements from the PRD are mandatory:
- Role-based access control (RBAC)
- Data isolation per entity (PT vs LPK)
- Audit logs for all critical operations
- Soft deletes for data retention
- PDF/Excel export capability

Deployment and build steps (Vite/NPM) MUST be documented in feature plans when frontend changes are introduced.

## Development Workflow

Development MUST follow Laravel best practices and repository conventions:

- Use `php artisan make:*` commands for scaffolding (models, requests, resources, migrations, etc.) with `--no-interaction` where appropriate
- Validate inputs via Form Request classes; do not place ad-hoc validation in controllers
- Use Eloquent relationships and eager loading to avoid N+1 queries
- Create factories and tests for new models; test coverage for critical flows is REQUIRED before merge
- Filament features (Resources, Forms, Tables, Actions) MUST be used for admin UI; reuse existing components where available
- Run `vendor/bin/pint` to format PHP changes before finalizing
- Follow the Laravel 10 structure (middleware in `app/Http/Middleware/`, service providers in `app/Providers/`)
- Check sibling files for conventions before creating new files

## Governance

Amendments to this constitution MUST follow the process below:

**Propose**: Open a dedicated PR labelled `constitution` that includes the proposed text changes, rationale, and a migration/compatibility plan if needed.

**Review**: The proposal MUST receive approval from at least two maintainers or designated approvers.

**Versioning**: Changes MUST include a version bump according to semantic rules described below and an entry in the Sync Impact Report.

**Migration**: If changes require data migration or developer actions, include a step-by-step migration plan and tests demonstrating safety.

### Versioning Policy

- **MAJOR**: Backward-incompatible changes to governance or removal/renaming of principles
- **MINOR**: New principle or material expansion of an existing principle
- **PATCH**: Wording clarifications, typos, or non-semantic refinements

### Compliance Reviews

Every quarter the maintainers MUST run a compliance check (tool-assisted where possible) to ensure plans and specs reference and follow this constitution's gates.

All PRs/reviews must verify compliance with core principles. Complexity must be justified explicitly. Use `.github/copilot-instructions.md`, `AGENTS.md`, `CLAUDE.md`, and `GEMINI.md` for runtime development guidance.

**Version**: 1.0.0 | **Ratified**: TODO(RATIFICATION_DATE): provide original adoption date | **Last Amended**: 2026-01-13
