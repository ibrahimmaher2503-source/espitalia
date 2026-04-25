<!--
  === Sync Impact Report ===
  Version change: (none) → 1.0.0
  Bump rationale: MAJOR — initial constitution ratification

  Modified principles: N/A (initial creation)

  Added sections:
    - Core Principles (5 principles)
    - Technology Stack Constraints
    - Development Workflow
    - Governance

  Removed sections: N/A

  Templates requiring updates:
    - .specify/templates/plan-template.md ✅ no changes needed
      (Constitution Check section is generic; gates are filled at plan time)
    - .specify/templates/spec-template.md ✅ no changes needed
      (User stories and requirements sections are compatible)
    - .specify/templates/tasks-template.md ✅ no changes needed
      (Phase structure accommodates principle-driven tasks)

  Deferred TODOs: none
-->

# Espitalia Constitution

## Core Principles

### I. Module Encapsulation

Every feature MUST be implemented as a self-contained module under
`Modules/` using `nwidart/laravel-modules`. Each module MUST own its
Models, Controllers, Routes, Views, Migrations, Seeders, and Assets.

- Cross-module model references MUST use Eloquent relationships, never
  raw SQL joins spanning module boundaries.
- Shared logic MUST reside in `app/` (BaseModel, helpers, traits,
  middleware) — not duplicated across modules.
- Module status MUST be tracked in `modules_statuses.json`; disabling
  a module MUST NOT break other modules.

**Rationale**: The 33+ module codebase is only maintainable when modules
can be developed, tested, and deployed independently. Tight coupling
between modules creates cascading failures and blocks parallel work.

### II. Convention Adherence

All code MUST follow established Laravel and project-specific
conventions. Deviations require explicit justification in the PR
description.

- Models MUST extend `App\Models\BaseModel` (includes SoftDeletes,
  MediaLibrary, audit fields).
- Admin controllers MUST implement the standard method set: `index`,
  `index_data`, `create`, `store`, `edit`, `update`, `destroy`,
  `bulk_action`, `update_status`.
- Admin routes MUST use prefix `app/`, name prefix `backend.`, and
  middleware `['auth', 'auth_check']`.
- API routes MUST use middleware `auth:sanctum`, prefix `v1`, and name
  prefix `api.*`.
- Translation keys MUST follow the `module_name.lbl_*` convention and
  be registered in `lang/en/`.
- Admin views MUST extend `backend.layouts.app`; public views MUST
  extend `frontend::layouts.master`.

**Rationale**: Consistent conventions reduce onboarding friction, make
code reviewable by pattern-matching, and allow tooling (generators,
linters) to work reliably across all modules.

### III. Role-Based Access Control

Every feature MUST respect the spatie/laravel-permission role system.
The six core roles (`admin`, `demo_admin`, `doctor`, `receptionist`,
`vendor`, `user`) define distinct access boundaries.

- New features MUST declare required permissions via a seeder.
- Web routes MUST be gated by appropriate middleware and permission
  checks.
- API endpoints MUST validate authorization before processing requests.
- Sidebar entries in `GenerateMenus` middleware MUST be conditionally
  rendered based on role.
- The `demo_admin` role MUST NOT have write access to destructive
  operations.

**Rationale**: Espitalia manages sensitive health/business data across
multiple tenants. Incorrect access control is a critical security and
compliance risk.

### IV. Test Discipline

New features MUST include PHPUnit tests. Test coverage MUST address
both the happy path and critical edge cases for each feature.

- Tests MUST run against SQLite in-memory databases for speed and
  isolation.
- Module tests reside in `Modules/*/Tests/Unit` and
  `Modules/*/Tests/Feature`.
- Core tests reside in `tests/Unit` and `tests/Feature`.
- `php artisan test` MUST pass on the default branch at all times.
- Destructive or payment-related operations MUST have explicit test
  coverage.

**Rationale**: With 33+ interconnected modules, untested changes risk
regressions that are expensive to diagnose. Automated tests are the
primary safety net for refactoring and feature additions.

### V. Simplicity and YAGNI

Implementation MUST solve the stated requirement with the minimum
viable complexity. Speculative abstractions, unused configuration
options, and premature optimization are prohibited.

- Do not add features, parameters, or extension points that are not
  required by the current task.
- Prefer inline logic over helper functions when used only once.
- Prefer existing project patterns (DataTables, Select2, Blade
  components) over introducing new libraries for equivalent
  functionality.
- New dependencies MUST be justified: document why existing tools are
  insufficient.

**Rationale**: The codebase already carries significant structural
complexity from its modular architecture and multi-role system.
Additional unnecessary complexity compounds maintenance burden
disproportionately.

## Technology Stack Constraints

The following technology choices are fixed and MUST NOT be replaced
without a constitution amendment:

| Layer | Technology | Version Lock |
|-------|-----------|-------------|
| Framework | Laravel | 11.x |
| Modules | nwidart/laravel-modules | 10.x |
| Auth | Laravel Sanctum (API), session (web) | — |
| Permissions | spatie/laravel-permission | — |
| Build | Laravel Mix (webpack) | — |
| Admin UI | Blade + jQuery + DataTables + Select2 | — |
| Reactive UI | Vue 3 + Pinia (settings/booking) | — |
| Icons | Phosphor Icons | — |
| Code Style | Laravel Pint (Laravel preset) | — |
| Testing | PHPUnit + SQLite in-memory | — |
| Payments | Stripe, Razorpay, PayPal | — |

New packages MUST be compatible with these constraints. Frontend
packages MUST work with Laravel Mix's webpack configuration.

## Development Workflow

### Code Quality Gates

1. **Lint**: `./vendor/bin/pint` MUST exit cleanly before committing.
2. **Test**: `php artisan test` MUST pass before merging to the default
   branch.
3. **Cache**: Run `php artisan optimize:clear` after structural changes
   (routes, config, service providers).
4. **Module Enable**: New modules MUST be enabled in
   `modules_statuses.json` and verified with `php artisan module:list`.

### Commit Conventions

- Commits MUST be atomic: one logical change per commit.
- Commit messages MUST be descriptive of the "why", not just the
  "what".
- Migrations MUST NOT be amended after being pushed to a shared branch;
  create a new migration instead.

### Pull Request Standards

- PRs MUST include a description of what changed and why.
- PRs that add a new module MUST follow the 11-step checklist in
  CLAUDE.md § "Adding a New Module".
- PRs MUST NOT include unrelated formatting changes or refactoring.

## Governance

This constitution is the authoritative reference for architectural
decisions and development standards in the Espitalia project. All code
contributions MUST comply with these principles.

- **Amendments** require: (1) a written proposal documenting the change
  and rationale, (2) review and approval by a project maintainer, and
  (3) a migration plan for any existing code that violates the new
  rule.
- **Versioning** follows semantic versioning: MAJOR for principle
  removals or redefinitions, MINOR for new principles or material
  expansions, PATCH for clarifications and wording fixes.
- **Compliance review**: PRs SHOULD reference which principles they
  uphold or justify deviations from. Reviewers MUST verify that new
  code aligns with Module Encapsulation (I) and Convention Adherence
  (II) at minimum.
- **Runtime guidance**: Refer to `CLAUDE.md` for detailed development
  instructions, command references, and module creation workflows.

**Version**: 1.0.0 | **Ratified**: 2026-03-09 | **Last Amended**: 2026-03-09
