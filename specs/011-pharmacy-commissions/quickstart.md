# Quickstart: Pharmacy Commissions

**Branch**: `011-pharmacy-commissions` | **Date**: 2026-04-05

---

## Prerequisites

- Pharmacy module fully operational with seeded data (pharmacies, products, inventory)
- At least one pharmacy with delivered orders (or ability to create and advance orders)
- Admin user with appropriate permissions

## Scenario 1: Create Commission Template and Assign to Pharmacy

1. Log in as admin
2. Navigate to sidebar → Pharmacy Marketplace → Commissions
3. Click "Create"
4. Fill form: Title = "Standard 10%", Type = "Percentage", Value = 10
5. Save — verify it appears in the commission list
6. Navigate to sidebar → Pharmacy Marketplace → Pharmacies
7. Edit a pharmacy → select "Standard 10%" from the Commission dropdown
8. Save — verify the pharmacy now shows the assigned commission

**Expected**: Commission template created and assigned to pharmacy.

## Scenario 2: Automatic Commission Calculation on Order Delivery

1. Ensure the pharmacy from Scenario 1 has an active order (place one via API if needed)
2. Log in as pharmacy staff → navigate to Orders
3. Accept the order (pending → confirmed)
4. Advance status: confirmed → preparing → on_the_way → delivered
5. Navigate to admin panel → Pharmacy Marketplace → Pharmacy Earnings
6. Verify the pharmacy appears with an unpaid earning

**Expected**: For an order with subtotal = 100.00 and 10% commission:
- Commission earning created: platform_commission = 10.00, pharmacy_receivable = 90.00, status = "unpaid"

## Scenario 3: View Earnings Dashboard

1. Log in as admin
2. Navigate to sidebar → Pharmacy Marketplace → Pharmacy Earnings
3. Verify summary stats: total platform commission, total unpaid receivable, total paid
4. Filter by pharmacy name — verify filtering works
5. Filter by status (unpaid/paid) — verify filtering works
6. Click on a pharmacy row — verify per-order commission breakdown

**Expected**: Dashboard shows accurate aggregated data with working filters.

## Scenario 4: Process Payout

1. From Scenario 3, click on a pharmacy with unpaid earnings
2. Click "Process Payout" button
3. Select payment method: "Bank Transfer"
4. Confirm payout
5. Verify all unpaid earnings for that pharmacy are now "paid"
6. Verify a payout record is created with the total amount and payment method
7. Return to earnings dashboard — verify the pharmacy's unpaid amount is now 0

**Expected**: Payout processed, earnings marked as paid, payout record saved.

## Scenario 5: Commission Reversal on Order Cancellation

1. Ensure a pharmacy has a delivered order with a commission earning (from Scenario 2)
2. Log in as admin → navigate to Pharmacy Marketplace → Orders
3. Change the order status to "Cancelled"
4. Navigate to Pharmacy Earnings
5. Verify the commission earning for that order is now "cancelled"
6. Verify the summary stats reflect the reduced amounts

**Expected**: Commission earning reversed automatically when order is cancelled.

## Scenario 6: Edge Cases

### 6a: No Commission Assigned
1. Remove commission assignment from a pharmacy (set to none)
2. Deliver an order for that pharmacy
3. Check Pharmacy Earnings — no earning should be created

### 6b: Fixed Commission
1. Create a fixed commission template: Title = "Flat Fee", Type = "Fixed", Value = 5.00
2. Assign to a pharmacy
3. Deliver an order with subtotal = 200.00
4. Verify earning: platform_commission = 5.00, pharmacy_receivable = 195.00

### 6c: Zero Subtotal Order
1. Deliver an order with subtotal = 0.00 for a pharmacy with 10% commission
2. Verify earning: platform_commission = 0.00, pharmacy_receivable = 0.00

### 6d: Duplicate Prevention
1. After an order is delivered and earning created, trigger delivery status again
2. Verify no duplicate earning is created
