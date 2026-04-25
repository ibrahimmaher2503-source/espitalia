# Implementation Plan: Pharmacy Commissions

**Branch**: `011-pharmacy-commissions` | **Date**: 2026-04-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/011-pharmacy-commissions/spec.md`

## Summary

Add commission tracking and payout management for the Pharmacy Marketplace module. Admin configures platform commission rates (percentage or fixed) per pharmacy. On order delivery, the system auto-calculates the platform's commission and the pharmacy's receivable. Admin views earnings dashboard and processes payouts. Follows existing Commission/Earning module patterns (morphable relationships, status workflow) but implemented within the Pharmacy module for encapsulation.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, spatie/laravel-medialibrary
**Storage**: MySQL (existing database)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: Web application (admin panel)
**Project Type**: Web service — Laravel modular monolith
**Performance Goals**: Earnings dashboard loads in < 3s for 1,000 pharmacies; commission calculation < 1s on status change
**Constraints**: Must follow existing Commission/Earning module patterns; admin-only access; no new external dependencies
**Scale/Scope**: Up to 1,000 pharmacies, thousands of orders per month

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|-----------|--------|----------|
| I. Module Encapsulation | PASS | All commission code lives within Modules/Pharmacy (models, controllers, migrations, views, routes). Cross-module reference only to User model via Eloquent relationship. |
| II. Convention Adherence | PASS | Admin controllers follow standard method set (index, index_data, create, store, edit, update, destroy, bulk_action, update_status). Routes use prefix `app/`, name `backend.*`. Views extend `backend.layouts.app`. |
| III. Role-Based Access Control | PASS | New permissions declared via seeder (view_pharmacy_commission, add_pharmacy_commission, edit_pharmacy_commission, delete_pharmacy_commission, view_pharmacy_earning, edit_pharmacy_earning). Routes gated by permission middleware. Sidebar entries conditional on role. |
| IV. Test Discipline | PASS | Feature tests for commission calculation, reversal, and payout processing. Tests use SQLite in-memory. |
| V. Simplicity and YAGNI | PASS | Reuses existing DataTable, Blade, jQuery patterns. No new libraries. Commission tables within Pharmacy module (no new module). No speculative multi-tier or category-level commissions. |

All gates pass. No violations require justification.

## Project Structure

### Documentation (this feature)

```text
specs/011-pharmacy-commissions/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (no external API — admin-only views)
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (within existing Pharmacy module)

```text
Modules/Pharmacy/
├── Http/
│   ├── Controllers/
│   │   ├── PharmacyCommissionController.php     # Commission template CRUD
│   │   └── PharmacyEarningController.php        # Earnings dashboard + payouts
│   └── Requests/
│       ├── PharmacyCommissionRequest.php         # Commission template validation
│       └── PharmacyPayoutRequest.php             # Payout processing validation
├── Models/
│   ├── PharmacyCommission.php                    # Commission template
│   ├── PharmacyCommissionEarning.php             # Per-order earning record
│   └── PharmacyPayout.php                        # Payout payment record
├── Services/
│   └── PharmacyCommissionService.php             # Calculation + reversal logic
├── Resources/views/backend/
│   ├── commissions/
│   │   ├── index.blade.php                       # Commission list (DataTable)
│   │   └── create.blade.php                      # Create/edit form
│   └── earnings/
│       ├── index.blade.php                       # Earnings dashboard
│       └── show.blade.php                        # Per-pharmacy earning detail + payout
├── database/
│   └── migrations/
│       ├── 2026_04_05_100001_create_pharmacy_commissions_table.php
│       ├── 2026_04_05_100002_create_pharmacy_commission_earnings_table.php
│       └── 2026_04_05_100003_create_pharmacy_payouts_table.php
└── database/seeders/
    └── PharmacyCommissionPermissionSeeder.php     # Permissions for commission/earning
```

**Structure Decision**: All commission code is added to the existing Pharmacy module rather than creating a new module. This maintains module encapsulation (Constitution I) while keeping pharmacy financial logic co-located with pharmacy order logic. The commission calculation service hooks into the existing OrderService when order status changes to "delivered".
