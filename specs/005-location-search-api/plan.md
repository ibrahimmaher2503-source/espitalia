# Implementation Plan: Location-Based Healthcare Search API

**Branch**: `005-location-search-api` | **Date**: 2026-03-31 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/005-location-search-api/spec.md`

## Summary

Build a location-based search API for a Vizita-like healthcare platform. Creates a Governorate/City foundation for Egypt (27 governorates, bilingual AR/EN), adds location columns to existing Doctor, Clinic, and Nurse tables, creates new HomeHealthcare, Lab, and RadiologyCenter entities, and exposes 8 public API endpoints with unified response format, pagination, and Accept-Language header-driven localization.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, Laravel Sanctum
**Storage**: MySQL/MariaDB (existing project DB)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: API server (consumed by Flutter mobile app)
**Project Type**: Web service (API-only for this feature)
**Performance Goals**: <1s for governorate/city lists, <2s for paginated search results, 500 concurrent requests
**Constraints**: No Spatie Translatable — use `name_ar`/`name_en` columns with model accessor. Lang files only for error messages.
**Scale/Scope**: 27 governorates, ~200 cities, 8 API endpoints, 3 new tables, 3 altered tables, 3 new entities

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Research Gate (Phase 0)

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | New `Location` module for Governorate/City. New `HomeHealthcare` module. Lab/RadiologyCenter added to existing `RadiologyLab` module. |
| II. Convention Adherence | PASS | Models extend BaseModel. API routes use standard prefix/middleware patterns. Translation keys follow `module_name.lbl_*` convention. |
| III. Role-Based Access Control | PASS | All search endpoints are public (no auth required) — this is intentional for app discovery/browsing. No permission seeder needed. |
| IV. Test Discipline | PASS | PHPUnit tests planned for all endpoints and search services using SQLite in-memory. |
| V. Simplicity and YAGNI | PASS | One service class per entity (no generic abstraction). Direct Eloquent queries with `when()` clauses. No unnecessary patterns. |

### Post-Design Gate (Phase 1)

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | Location module owns governorates/cities. Each entity's search service lives in its own module. Cross-module references use Eloquent relationships only. |
| II. Convention Adherence | PASS | API routes registered per-module in `Routes/api.php`. Resources in `Transformers/`. Response format uses existing `sendResponse()`/`sendError()` helpers. |
| III. Role-Based Access Control | PASS | Public endpoints — no auth middleware. Consistent with existing public routes (e.g., `get-doctor-list`). |
| IV. Test Discipline | PASS | Tests for: models, seeder data, middleware locale switching, each search endpoint, edge cases (empty results, invalid IDs, missing required params). |
| V. Simplicity and YAGNI | PASS | No repository pattern, no search abstraction layer, no caching layer. Direct service → query → paginate. Complexity tracking: no violations. |

## Project Structure

### Documentation (this feature)

```text
specs/005-location-search-api/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: research decisions
├── data-model.md        # Phase 1: entity definitions
├── quickstart.md        # Phase 1: setup instructions
├── contracts/
│   └── api-endpoints.md # Phase 1: API contract definitions
├── checklists/
│   └── requirements.md  # Spec quality checklist
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
Modules/Location/                          # NEW MODULE
├── Config/config.php
├── Http/Controllers/API/
│   └── LocationApiController.php          # GET /governorates, GET /cities
├── Models/
│   ├── Governorate.php                    # name accessor by locale
│   └── GovernorateCity.php                # name accessor by locale
├── Transformers/
│   ├── GovernorateResource.php
│   └── GovernorateCityResource.php
├── Routes/api.php
├── Providers/
│   ├── LocationServiceProvider.php
│   └── RouteServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── create_governorates_table.php
│   │   └── create_governorate_cities_table.php
│   └── seeders/
│       └── GovernorateSeeder.php          # 27 governorates + cities
└── Tests/
    ├── Unit/GovernorateTest.php
    └── Feature/LocationApiTest.php

Modules/HomeHealthcare/                     # NEW MODULE
├── Config/config.php
├── Http/Controllers/API/
│   └── HomeHealthcareSearchApiController.php
├── Models/
│   └── HomeHealthcare.php
├── Services/
│   └── HomeHealthcareSearchService.php
├── Transformers/
│   └── HomeHealthcareResource.php
├── Http/Requests/
│   └── HomeHealthcareSearchRequest.php
├── Routes/api.php
├── Providers/
│   ├── HomeHealthcareServiceProvider.php
│   └── RouteServiceProvider.php
├── database/migrations/
│   └── create_home_healthcares_table.php
└── Tests/
    └── Feature/HomeHealthcareSearchTest.php

Modules/Clinic/                             # MODIFIED
├── Http/Controllers/API/
│   ├── DoctorSearchApiController.php      # NEW
│   └── ClinicSearchApiController.php      # NEW
├── Services/
│   ├── DoctorSearchService.php            # NEW
│   └── ClinicSearchService.php            # NEW
├── Transformers/
│   ├── DoctorSearchResource.php           # NEW
│   └── ClinicSearchResource.php           # NEW
├── Http/Requests/
│   ├── DoctorSearchRequest.php            # NEW
│   └── ClinicSearchRequest.php            # NEW
├── database/migrations/
│   ├── add_governorate_city_to_doctors.php    # NEW
│   └── add_governorate_city_to_clinic.php     # NEW
└── Tests/
    └── Feature/
        ├── DoctorSearchTest.php           # NEW
        └── ClinicSearchTest.php           # NEW

Modules/RequestNurse/                       # MODIFIED
├── Http/Controllers/API/
│   └── NurseSearchApiController.php       # NEW
├── Services/
│   └── NurseSearchService.php             # NEW
├── Transformers/
│   └── NurseSearchResource.php            # NEW
├── Http/Requests/
│   └── NurseSearchRequest.php             # NEW
├── database/migrations/
│   └── add_governorate_city_to_nurses.php # NEW
└── Tests/
    └── Feature/NurseSearchTest.php        # NEW

Modules/RadiologyLab/                       # MODIFIED
├── Http/Controllers/API/
│   ├── LabSearchApiController.php         # NEW
│   └── RadiologySearchApiController.php   # NEW
├── Models/
│   ├── Lab.php                            # NEW
│   └── RadiologyCenter.php                # NEW
├── Services/
│   ├── LabSearchService.php               # NEW
│   └── RadiologySearchService.php         # NEW
├── Transformers/
│   ├── LabSearchResource.php              # NEW
│   └── RadiologyCenterResource.php        # NEW
├── Http/Requests/
│   ├── LabSearchRequest.php               # NEW
│   └── RadiologySearchRequest.php         # NEW
├── database/migrations/
│   ├── create_labs_table.php              # NEW
│   └── create_radiology_centers_table.php # NEW
└── Tests/
    └── Feature/
        ├── LabSearchTest.php              # NEW
        └── RadiologySearchTest.php        # NEW

app/Http/Middleware/
└── localization.php                       # MODIFIED (add Accept-Language header support)

lang/en/
├── location.php                           # NEW (validation/error messages)
└── (existing files unchanged)

lang/ar/
├── location.php                           # NEW (Arabic validation/error messages)
└── (existing files unchanged)
```

**Structure Decision**: Follows the existing modular architecture. New `Location` module for foundational governorate/city data. New `HomeHealthcare` module for a completely new entity. Existing `Clinic`, `RequestNurse`, and `RadiologyLab` modules extended with search functionality. No new patterns introduced — all code follows established project conventions.

## Complexity Tracking

> No constitution violations detected. No entries needed.
