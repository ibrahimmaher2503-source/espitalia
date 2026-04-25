# Feature Specification: Pharmacy Commissions

**Feature Branch**: `011-pharmacy-commissions`
**Created**: 2026-04-05
**Status**: Draft
**Input**: User description: "I need to add commissions for Pharmacy to handle financial section for admin"

## Clarifications

### Session 2026-04-05

- Q: Does the commission amount represent the platform's revenue share or the pharmacy's earning? → A: Commission = platform's cut. Pharmacy payout = order subtotal minus commission. (e.g., 10% of 200 = 20 platform keeps, pharmacy receives 180)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Configures Pharmacy Commission Rates (Priority: P1)

An admin needs to set up commission structures for pharmacies so the platform can automatically calculate its share (platform fee) on every completed pharmacy order. The admin creates a commission template specifying a percentage or fixed amount, then assigns it to one or more pharmacies.

**Why this priority**: Without commission configuration, no financial tracking can happen. This is the foundational setup that enables all other financial features.

**Independent Test**: Admin logs in, creates a commission template (e.g., 10% platform fee), assigns it to a pharmacy. Verify the commission record is saved and visible in the commission list.

**Acceptance Scenarios**:

1. **Given** an admin is on the pharmacy commissions page, **When** they create a new commission with title "Standard Platform Fee", type "percentage", value 10, **Then** the commission template is saved and appears in the list.
2. **Given** a commission template exists, **When** the admin assigns it to a specific pharmacy, **Then** that pharmacy is linked to the commission and visible in the assignment list.
3. **Given** a commission template is assigned to a pharmacy, **When** the admin edits the commission value from 10% to 12%, **Then** all future orders for that pharmacy use the updated rate.
4. **Given** a commission template exists, **When** the admin deactivates it, **Then** no new commissions are earned against it, but existing records remain intact.

---

### User Story 2 - System Automatically Calculates Commission on Delivered Orders (Priority: P2)

When a pharmacy order reaches "delivered" status, the system automatically calculates the platform commission based on the assigned commission template and records it as an earning. This happens without manual intervention.

**Why this priority**: Automatic calculation eliminates manual work and ensures every completed order generates a commission record. Depends on commission templates from US1.

**Independent Test**: Assign a 10% commission to a pharmacy, place a test order for 100.00, advance it to "delivered". Verify a commission earning of 10.00 is automatically created with status "unpaid".

**Acceptance Scenarios**:

1. **Given** a pharmacy has a 10% commission assigned, **When** an order totaling 200.00 (subtotal, excluding delivery fee) is marked as "delivered", **Then** a commission earning is created with platform_commission = 20.00 and pharmacy_receivable = 180.00, status "unpaid", linked to that order.
2. **Given** a pharmacy has a fixed-amount commission of 5.00, **When** an order is delivered, **Then** a commission earning of 5.00 is recorded regardless of order total.
3. **Given** a pharmacy has no commission assigned, **When** an order is delivered, **Then** no commission earning is created.
4. **Given** an order is cancelled after delivery (admin reversal), **When** the cancellation completes, **Then** the associated commission earning is also reversed (marked as "cancelled").
5. **Given** an order already has a commission earning, **When** the order status changes to "delivered" again (duplicate event), **Then** no duplicate commission earning is created.

---

### User Story 3 - Admin Views Pharmacy Earnings Dashboard (Priority: P3)

The admin needs a financial overview showing all pharmacy commission earnings: how much has been earned, how much is unpaid, and how much has been paid out. This provides at-a-glance financial health of the pharmacy marketplace.

**Why this priority**: Visibility into earnings is essential for financial operations but depends on commission data being generated first (US2).

**Independent Test**: With several delivered orders across multiple pharmacies, admin navigates to the pharmacy earnings page. Verify totals, filters, and per-pharmacy breakdowns display correctly.

**Acceptance Scenarios**:

1. **Given** multiple pharmacies have unpaid commissions, **When** the admin opens the pharmacy earnings page, **Then** they see a list of pharmacies with their total unpaid commission amounts.
2. **Given** the admin is viewing the earnings list, **When** they filter by a specific pharmacy, **Then** only that pharmacy's earnings are shown.
3. **Given** the admin is viewing the earnings list, **When** they filter by date range, **Then** only earnings within that period are displayed.
4. **Given** the admin clicks on a pharmacy's earnings row, **When** the detail view opens, **Then** they see a breakdown of each order's commission (order ID, date, order total, commission amount, status).

---

### User Story 4 - Admin Processes Commission Payouts (Priority: P4)

The admin needs to pay pharmacies their share (order subtotal minus platform commission) by processing payouts. This creates a payment record with the payment method and date, transitioning commission earnings from "unpaid" to "paid".

**Why this priority**: Payout processing is the final step in the financial lifecycle. Depends on earnings data from US3.

**Independent Test**: Select a pharmacy with unpaid commissions, process a payout. Verify the earnings move to "paid" status and a payment record is created.

**Acceptance Scenarios**:

1. **Given** a pharmacy has unpaid commission records across 10 orders with a total pharmacy receivable of 4,500.00 (order subtotals minus platform commissions), **When** the admin processes a payout for that pharmacy, **Then** all 10 commission earnings are marked as "paid" with the current date and selected payment method, and the payout amount = 4,500.00.
2. **Given** the admin is processing a payout, **When** they select "Bank Transfer" as the payment method, **Then** the payment record stores the method and the payout is recorded.
3. **Given** a pharmacy has both paid and unpaid commissions, **When** the admin views the pharmacy's earning history, **Then** paid and unpaid commissions are clearly distinguished.
4. **Given** a payout has been processed, **When** the admin views the payment history, **Then** they see the payout date, amount, payment method, and which orders were included.

---

### Edge Cases

- What happens when a commission template is deleted while pharmacies still have it assigned? (Commission template is soft-deleted; existing earnings remain, no new earnings are generated)
- What happens when an order total is zero (e.g., fully discounted)? (Percentage commission results in 0.00; fixed commission still applies)
- What happens when a pharmacy is deactivated but has unpaid commissions? (Unpaid commissions remain visible and can still be paid out)
- What happens when the admin changes a commission rate mid-month? (Only future delivered orders use the new rate; existing earnings are unchanged)
- What happens when two commission templates are assigned to the same pharmacy? (Only one active commission template per pharmacy is allowed; assigning a new one replaces the previous)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow admins to create commission templates with a title, type (percentage or fixed), and value
- **FR-002**: System MUST allow admins to assign exactly one active commission template to each pharmacy
- **FR-003**: System MUST allow admins to edit commission templates (title, type, value, active status)
- **FR-004**: System MUST allow admins to deactivate commission templates without deleting historical data
- **FR-005**: System MUST automatically calculate commission when a pharmacy order status changes to "delivered"
- **FR-006**: Commission calculation for percentage type MUST use the order subtotal (excluding delivery fee) as the base
- **FR-007**: Commission calculation for fixed type MUST use the configured fixed amount regardless of order total
- **FR-008**: System MUST NOT create duplicate commission earnings for the same order
- **FR-009**: System MUST reverse (cancel) commission earnings when a delivered order is subsequently cancelled by admin
- **FR-010**: System MUST display a list of all pharmacies with their unpaid commission totals on the earnings page
- **FR-011**: System MUST allow filtering the earnings list by pharmacy name, date range, and payment status (paid/unpaid)
- **FR-012**: System MUST display a detailed breakdown of individual order commissions per pharmacy
- **FR-013**: System MUST allow admins to process bulk payouts for a pharmacy (mark all unpaid commission earnings as paid, payout amount = sum of pharmacy receivables)
- **FR-014**: System MUST record payout details including payment method, date, total amount, and which earnings were included
- **FR-015**: System MUST enforce that only users with the appropriate admin permissions can access commission and earning features
- **FR-016**: System MUST support the following payment methods for payouts: Cash, Bank Transfer
- **FR-017**: System MUST display commission summary statistics on the earnings page: total platform commission earned, total pharmacy receivable unpaid, total pharmacy receivable paid

### Key Entities

- **Pharmacy Commission**: A template defining the commission structure (title, type, value). Assigned to pharmacies. Only one active assignment per pharmacy.
- **Pharmacy Commission Earning**: An individual commission record generated per delivered order. Links to a specific order and pharmacy. Tracks both the platform commission amount (platform's cut) and pharmacy receivable (order subtotal minus commission). Payment status: unpaid, paid, cancelled.
- **Pharmacy Employee Commission**: The assignment linking a pharmacy to a commission template. Tracks which pharmacy uses which commission rate.
- **Pharmacy Payout**: A payment record created when the admin pays the pharmacy its receivable share. Groups multiple earnings into a single payout event. Records total payout amount (sum of pharmacy receivables), payment method, and date.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin can create a commission template and assign it to a pharmacy in under 2 minutes
- **SC-002**: Commission earnings are automatically generated within 1 second of an order reaching "delivered" status
- **SC-003**: The earnings dashboard loads with accurate totals for up to 1,000 pharmacies within 3 seconds
- **SC-004**: Admin can process a payout for a pharmacy (marking all unpaid commissions as paid) in under 1 minute
- **SC-005**: 100% of delivered pharmacy orders with an assigned commission template generate a corresponding commission earning
- **SC-006**: Commission reversal on order cancellation occurs automatically with zero manual intervention
- **SC-007**: Financial totals (earned, unpaid, paid) are accurate to the cent across all pharmacies at any point in time

## Assumptions

- The existing commission and earning module patterns (morphable relationships, status workflow) are the established approach and should be followed for consistency
- Commission is calculated on order subtotal only (excluding delivery fee), which is the standard marketplace platform fee model
- Only one commission template can be active per pharmacy at a time to avoid calculation ambiguity
- Commission is earned on "delivered" status only, not on "confirmed" or "preparing"
- The pharmacy owner (the user linked via pharmacy_id) is the recipient of the commission-related communications/payouts
- Payment method options are limited to Cash and Bank Transfer for MVP; additional methods can be added later
- The admin role is the only role with access to commission configuration and payout processing
- Existing delivered pharmacy orders (before this feature) will not retroactively generate commission earnings

## Scope

### In Scope
- Commission template CRUD for pharmacy orders
- Commission assignment to pharmacies
- Automatic commission calculation on order delivery
- Commission reversal on order cancellation
- Admin earnings dashboard with filtering
- Payout processing and payment records
- Admin sidebar integration

### Out of Scope
- Pharmacy self-service commission viewing (pharmacy panel showing their own earnings)
- Automatic bank transfer integration (payouts are recorded manually)
- Tax calculation on commissions
- Multi-tier commission structures (e.g., different rates per product category)
- Commission reports/exports (PDF, CSV)
- Commission notifications to pharmacy owners
- Wallet integration for commission payouts
