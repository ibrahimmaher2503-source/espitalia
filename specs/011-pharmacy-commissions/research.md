# Research: Pharmacy Commissions

**Branch**: `011-pharmacy-commissions` | **Date**: 2026-04-05

---

## R1: Reuse Existing Commission Module vs. Pharmacy-Specific Tables

**Decision**: Create pharmacy-specific commission tables within the Pharmacy module.

**Rationale**: The existing Commission module (Modules/Commission) uses a generic morphable pattern with types like "doctor_commission" and "vendor_commission". While we could extend it, creating pharmacy-specific tables within the Pharmacy module provides:
1. Better module encapsulation (Constitution Principle I) — no cross-module migration dependencies
2. Clear ownership — pharmacy commission schema changes don't risk breaking doctor/vendor commissions
3. Simpler queries — no need to filter by `type` or `commissionable_type` polymorphism
4. Pharmacy-specific fields — `pharmacy_receivable` amount doesn't exist in the generic model

**Alternatives considered**:
- Extend existing Commission tables with pharmacy type: Rejected because it creates cross-module coupling and the existing CommissionEarning schema lacks pharmacy_receivable field
- Create a new PharmacyCommission module: Rejected because it violates YAGNI — a dedicated module for 3 tables + 2 controllers is over-scoped when the logic belongs with pharmacy orders

---

## R2: Commission Calculation Hook Point

**Decision**: Add commission calculation directly in `OrderService::cancelOrder` and in the pharmacy panel's `PharmacyPanelOrderController::advance_status` when status becomes "delivered". Also add it to the admin `PharmacyOrderController::update_status`.

**Rationale**: Commission must be calculated at every point where an order transitions to "delivered" status. There are three places this can happen:
1. Pharmacy staff advances status to "delivered" (PharmacyPanelOrderController)
2. Admin changes status to "delivered" (PharmacyOrderController)
3. (Never directly — orders don't start as delivered)

For reversal, it happens when:
1. Admin cancels a delivered order (OrderService::cancelOrder with isAdmin=true)

A dedicated `PharmacyCommissionService` encapsulates the calculation logic and is called from both controllers and the OrderService.

**Alternatives considered**:
- Eloquent model observer on PharmacyOrder: Rejected because observers fire on any update, not just status changes. Would require diffing old vs new status in the observer, adding fragile implicit logic.
- Event/Listener pattern: Rejected because the project doesn't use Laravel events for business logic elsewhere (YAGNI, Constitution V). Direct service calls are simpler and explicit.

---

## R3: Payout Data Model

**Decision**: Create a `pharmacy_payouts` table that acts as a payment event record. Each payout links to multiple `pharmacy_commission_earnings` records via a `payout_id` foreign key on the earning.

**Rationale**: When the admin processes a payout, all unpaid earnings for a pharmacy get a `payout_id` set and their status changes to "paid". The payout record stores the total amount, payment method, and date. This matches the existing EmployeeEarning pattern from the Earning module.

**Alternatives considered**:
- Pivot table (payout_id, earning_id): Rejected as over-normalized. A simple FK on the earning is sufficient since each earning can only be paid once.
- No payout record (just flip status): Rejected because it loses payment method, date grouping, and audit trail.

---

## R4: Commission on Subtotal vs. Total

**Decision**: Commission is calculated on order `subtotal` (excluding delivery fee), as confirmed in the spec clarification.

**Rationale**: The delivery fee goes to the delivery service/logistics, not the pharmacy. The platform commission should only apply to the product revenue. This is standard marketplace practice (Uber Eats, DoorDash).

**Alternatives considered**:
- Commission on total (including delivery): Rejected because delivery fee is a pass-through cost, not pharmacy revenue.

---

## R5: Admin Sidebar Placement

**Decision**: Add "Commissions" and "Pharmacy Earnings" as children under the existing "Pharmacy Marketplace" parent menu in the admin sidebar.

**Rationale**: Keeps all pharmacy-related admin features grouped together. No need for a separate financial section since the commission/earning features are pharmacy-specific.

**Alternatives considered**:
- Separate "Financial" sidebar section: Rejected because it would duplicate navigation patterns and the existing doctor/vendor earnings are already under their respective module sections.

---

## R6: Pharmacy Assignment Model

**Decision**: Store commission assignment directly on the `pharmacies` table as a `commission_id` nullable FK column, rather than a separate pivot/assignment table.

**Rationale**: The spec requires exactly one active commission per pharmacy. A nullable FK is the simplest representation of this constraint — no pivot table, no uniqueness enforcement logic, no "active" flag management. Setting a pharmacy's commission is just `UPDATE pharmacies SET commission_id = ? WHERE id = ?`.

**Alternatives considered**:
- Separate `pharmacy_employee_commissions` table: Rejected because it adds unnecessary complexity for a 1:1 relationship. The pivot pattern is useful for many-to-many (like doctor-to-commission), but pharmacies have exactly one commission.
- commission_id on pharmacy_inventory: Rejected because commission is per-pharmacy, not per-product.
