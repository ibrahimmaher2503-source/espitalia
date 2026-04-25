# Data Model: Pharmacy Commissions

**Branch**: `011-pharmacy-commissions` | **Date**: 2026-04-05

---

## Entities

### PharmacyCommission

Commission template defining the platform's revenue share structure.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint unsigned | PK, auto-increment | |
| title | string(255) | required | Human-readable name (e.g., "Standard 10%") |
| commission_type | string(20) | required, in: percentage, fixed | Calculation method |
| commission_value | decimal(8,2) | required, min: 0 | Percentage (0-100) or fixed amount |
| status | boolean | default: true | Active/inactive toggle |
| created_by | bigint unsigned | nullable, FK → users | Audit |
| updated_by | bigint unsigned | nullable, FK → users | Audit |
| deleted_by | bigint unsigned | nullable, FK → users | Audit |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Relationships**:
- hasMany → Pharmacy (via pharmacies.commission_id)

**Validation Rules**:
- title: required, string, max 255
- commission_type: required, in [percentage, fixed]
- commission_value: required, numeric, min 0. If percentage: max 100
- status: boolean

**Indexes**: status

---

### Pharmacy (modified — existing table)

Add commission assignment to the existing `pharmacies` table.

| Field (new) | Type | Constraints | Description |
|-------------|------|-------------|-------------|
| commission_id | bigint unsigned | nullable, FK → pharmacy_commissions ON DELETE SET NULL | Assigned commission template |

**Relationships** (new):
- belongsTo → PharmacyCommission

**Index**: commission_id

---

### PharmacyCommissionEarning

Individual commission record generated per delivered pharmacy order.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint unsigned | PK, auto-increment | |
| order_id | bigint unsigned | required, FK → pharmacy_orders | The delivered order |
| pharmacy_id | bigint unsigned | required, FK → pharmacies | The pharmacy |
| commission_id | bigint unsigned | nullable, FK → pharmacy_commissions | Snapshot of which template was used |
| order_subtotal | decimal(10,2) | required | Order subtotal at time of calculation |
| commission_type | string(20) | required | Snapshot: percentage or fixed |
| commission_value | decimal(8,2) | required | Snapshot: rate/amount at time of calculation |
| commission_amount | decimal(10,2) | required | Platform's cut (calculated) |
| pharmacy_receivable | decimal(10,2) | required | Pharmacy's share = order_subtotal - commission_amount |
| status | string(20) | default: unpaid, in: unpaid, paid, cancelled | Payment status |
| payout_id | bigint unsigned | nullable, FK → pharmacy_payouts | Set when paid |
| created_by | bigint unsigned | nullable | Audit |
| updated_by | bigint unsigned | nullable | Audit |
| deleted_by | bigint unsigned | nullable | Audit |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Relationships**:
- belongsTo → PharmacyOrder (order_id)
- belongsTo → Pharmacy (pharmacy_id)
- belongsTo → PharmacyCommission (commission_id)
- belongsTo → PharmacyPayout (payout_id)

**Validation Rules**:
- order_id: required, exists, unique (no duplicate earnings per order)
- pharmacy_id: required, exists
- commission_amount: required, numeric, min 0
- pharmacy_receivable: required, numeric, min 0
- status: required, in [unpaid, paid, cancelled]

**Indexes**:
- order_id (unique — enforces no duplicate earnings per order)
- pharmacy_id
- status
- composite (pharmacy_id, status) — for dashboard aggregation queries
- payout_id

**State Transitions**:
```
unpaid → paid       (admin processes payout)
unpaid → cancelled  (order cancelled after delivery)
paid → (terminal)   (no further transitions)
cancelled → (terminal)
```

---

### PharmacyPayout

Payment event record created when admin pays a pharmacy its receivable.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint unsigned | PK, auto-increment | |
| pharmacy_id | bigint unsigned | required, FK → pharmacies | Which pharmacy was paid |
| total_amount | decimal(10,2) | required | Sum of pharmacy_receivable for all included earnings |
| total_commission | decimal(10,2) | required | Sum of commission_amount for all included earnings |
| payment_method | string(50) | required, in: cash, bank_transfer | How payment was made |
| payment_date | date | required | When payment was made |
| notes | text | nullable | Optional admin notes |
| created_by | bigint unsigned | nullable | Audit (admin who processed) |
| updated_by | bigint unsigned | nullable | Audit |
| deleted_by | bigint unsigned | nullable | Audit |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Relationships**:
- belongsTo → Pharmacy (pharmacy_id)
- hasMany → PharmacyCommissionEarning (via earnings.payout_id)

**Indexes**: pharmacy_id, payment_date

---

## Entity Relationship Diagram

```
PharmacyCommission (1) ──→ (*) Pharmacy.commission_id
                                    │
                                    ↓
PharmacyOrder (1) ──→ (0..1) PharmacyCommissionEarning
                                    │
                                    ↓
PharmacyPayout (1) ──→ (*) PharmacyCommissionEarning.payout_id
```

**Key Design Decisions**:
1. Commission template values are **snapshotted** into the earning record (commission_type, commission_value, order_subtotal). This ensures historical accuracy even if the template is later modified.
2. `order_id` has a **unique index** on PharmacyCommissionEarning to prevent duplicate earnings (FR-008).
3. `commission_id` on pharmacies uses **ON DELETE SET NULL** — deleting a template doesn't break pharmacies, just unassigns them.
4. `payout_id` is **nullable** — unpaid earnings have no payout yet. Set when payout is processed.
