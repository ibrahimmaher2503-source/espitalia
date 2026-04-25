# Research: Doctor Visit Request API

**Feature**: `009-doctor-visit-api`
**Date**: 2026-04-05

---

## Decision 1: Module Name

**Decision**: `DoctorVisit`

**Rationale**: Follows nwidart PascalCase naming. Short and descriptive without prescribing future scope. Closest analogue `RequestNurse` suggests verb-first naming is acceptable but noun-first (`DoctorVisit`) better describes the domain entity.

**Alternatives considered**: `VisitRequest` (too generic — could mean any visit type), `HomeVisit` (locks in the home-only scope too explicitly in the name)

---

## Decision 2: Reference Number Generation

**Decision**: Reuse the `lockForUpdate()` boot-event pattern from `Modules/RadiologyLab/Models/TestOrder.php` (prefix `LAB-` → `VR-`).

**Rationale**: Identical requirement (year-scoped, zero-padded sequential, human-readable). The TestOrder implementation handles concurrent creation safely via `lockForUpdate()` and `withTrashed()` ensures deleted records don't reset the counter.

**Pattern** (from `TestOrder.php:boot()`):
```php
static::creating(function ($model) {
    $year = now()->format('Y');
    $last = static::withTrashed()
        ->where('reference_number', 'like', "VR-{$year}-%")
        ->orderBy('id', 'desc')
        ->lockForUpdate()
        ->first();
    $next = $last
        ? ((int) last(explode('-', $last->reference_number))) + 1
        : 1;
    $model->reference_number = 'VR-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
});
```

**Alternatives considered**: UUID (too verbose for patient-facing reference, fails SC-003 "locate by reference"), DB sequence (not supported by SQLite in-memory test database)

---

## Decision 3: Status History Tracking

**Decision**: Dedicated `doctor_visit_status_histories` table (new model, no SoftDeletes — immutable audit log).

**Rationale**: FR-012 mandates recording "timestamp and acting user for every status change". A dedicated table gives typed, queryable history without coupling to spatie/laravel-activitylog's generic `subject_type`/`subject_id` polymorphic pattern. The `Appointment` module uses `LogsActivity`, but querying filtered status-change events from the generic log is cumbersome.

**Alternatives considered**:
- `LogsActivity` (spatie) — already on `Appointment` model; rejected because extracting specific status-change events requires filtering by `properties->attributes->status` JSON path, which is fragile and slow.
- Append-only JSON column on the main table — rejected because it cannot be indexed or queried per-actor.

---

## Decision 4: Email Notification

**Decision**: New `app/Mail/DoctorVisitStatusChanged.php` Mailable, dispatched inline via `Mail::to($patient->email)->send(new DoctorVisitStatusChanged($visitRequest))` within the status-update action.

**Rationale**: `AppointmentConfirmation.php` in `app/Mail/` is the established single-purpose Mailable pattern. The `CommonNotification` system (multi-channel: mail + database + FCM + Twilio) is over-engineered for v1 email-only requirement and requires seeding `NotificationTemplate` DB records. YAGNI-compliant choice.

**Trigger events**: status → `confirmed`, `cancelled`, `completed` (not `pending` — that is the creation acknowledgement already returned in the API response).

**Alternatives considered**: `CommonNotification` facade — rejected (requires DB templates, multi-channel setup); queued Mailable — acceptable but adds complexity; email suppressed on cancel — rejected per FR-013 which covers all three status changes.

---

## Decision 5: Structural Template

**Decision**: Mirror `Modules/RequestNurse/` structure exactly.

**Rationale**: RequestNurse is the closest existing module — same domain (healthcare request workflow), same status lifecycle (pending/confirmed/completed/cancelled), same patient–provider relationship pattern. Deviating from it without reason would violate Principle II.

**Key differences from RequestNurse**:
| Aspect | RequestNurse | DoctorVisit |
|--------|-------------|-------------|
| Provider model | Nurse (new model) | Doctor (existing in Clinic module) |
| Status history | None | `doctor_visit_status_histories` table |
| Reference number | None | `VR-{YYYY}-{NNNN}` |
| Email notification | None | On status change |
| Visit type | N/A | Fixed `home_visit` enum |

---

## Decision 6: Admin Authorization for Status Updates

**Decision**: Role middleware (`admin`, `receptionist`) + runtime "assigned doctor" check in the controller.

**Rationale**: Spec clarification — admin, receptionist, and the *assigned* doctor can update status. The assigned-doctor check is per-record (cannot be expressed as a static role gate alone). Consistent with how other modules handle per-record authorization via inline checks rather than Laravel Policies (which are not used elsewhere in the project).

**Implementation**:
```php
private function canManageRequest(User $user, DoctorVisitRequest $visitRequest): bool
{
    if ($user->hasRole(['admin', 'receptionist'])) {
        return true;
    }
    if ($user->hasRole('doctor') && $visitRequest->assignedDoctor) {
        return $visitRequest->assignedDoctor->doctor_id === $user->id;
    }
    return false;
}
```

**Alternatives considered**: Laravel Gates/Policies — rejected (not used elsewhere in project, adds new pattern); separate endpoint per role — rejected (duplicates logic unnecessarily).

---

## Decision 7: Admin Notes Field

**Decision**: Single `admin_notes` text column on `doctor_visit_requests` (last-write-wins). Appended history is tracked via `DoctorVisitStatusHistory.note`.

**Rationale**: FR-010 says "add internal notes". The NurseRequest model has a single `admin_notes` field (same approach). For this v1, a single text field is sufficient; threaded notes would require a separate table (YAGNI).

---

## API Response Format (confirmed)

Standard project wrapper (from existing API controllers):
```json
{ "status": true, "data": { ... }, "message": "translation key" }
```
Paginated:
```json
{ "status": true, "data": [...], "meta": { "current_page": 1, "per_page": 15, "total": 42, "last_page": 3 } }
```
Error (422):
```json
{ "status": false, "message": "Validation error text" }
```
