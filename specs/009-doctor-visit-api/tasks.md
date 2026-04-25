# Tasks: Doctor Visit Request API

**Input**: Design documents from `specs/009-doctor-visit-api/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/api-endpoints.md ✅, quickstart.md ✅

**Tests**: Included — Constitution Principle IV mandates PHPUnit coverage for all new features.

**Organization**: Tasks grouped by user story (US1 → US2 → US3) in spec priority order.

## Format: `[ID] [P?] [Story?] Description`

- **[P]**: Parallelizable — touches different files, no dependency on in-progress tasks
- **[USn]**: Maps to user story from spec.md (US1/US2/US3)

## Path Conventions

```
Modules/DoctorVisit/                         ← new module root
app/Mail/                                    ← shared Mail classes (edit existing dir)
resources/views/emails/                      ← email Blade templates
lang/en/                                     ← translation files
app/Http/Middleware/GenerateMenus.php        ← existing file (add sidebar entry)
lang/en/sidebar.php                          ← existing file (add key)
```

---

## Phase 1: Setup (Module Scaffold & Migrations)

**Purpose**: Create the module skeleton and database tables. No user story can be tested until migrations exist and the module is registered.

- [x] T001 Run `php artisan module:make DoctorVisit` — verify `Modules/DoctorVisit/` is created and `modules_statuses.json` contains the entry
- [x] T002 Create migration `Modules/DoctorVisit/database/migrations/2026_04_05_000001_create_doctor_visit_requests_table.php` — all columns from data-model.md: id, reference_number (string 20, unique), patient_id (FK→users), preferred_doctor_id (nullable FK→doctors), assigned_doctor_id (nullable FK→doctors), visit_type (string default 'home_visit'), visit_reason (text), preferred_date (date), contact_phone (string 20), additional_notes (text nullable), status (string default 'pending'), admin_notes (text nullable), cancellation_reason (text nullable), completed_at (timestamp nullable), created_by/updated_by/deleted_by (integer unsigned nullable), timestamps, softDeletes; add indexes on patient_id, status, preferred_date, assigned_doctor_id
- [x] T003 Create migration `Modules/DoctorVisit/database/migrations/2026_04_05_000002_create_doctor_visit_status_histories_table.php` — columns: id, visit_request_id (FK→doctor_visit_requests), old_status (string), new_status (string), changed_by (FK→users), note (text nullable), timestamps; no softDeletes (audit log is immutable); index on visit_request_id

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core models, transformer, translations, and permissions — shared across all three user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [x] T004 Create `Modules/DoctorVisit/Models/DoctorVisitRequest.php` — extend `App\Models\BaseModel`; protected $table = 'doctor_visit_requests'; add STATUS_PENDING/CONFIRMED/CANCELLED/COMPLETED constants, STATUSES array, VALID_TRANSITIONS array (per data-model.md), VISIT_TYPE_HOME constant; boot() method with reference-number generation using lockForUpdate() pattern from research.md Decision 2 (`VR-YYYY-NNNN`); relationships: patient() belongsTo User patient_id, preferredDoctor() belongsTo Doctor preferred_doctor_id, assignedDoctor() belongsTo Doctor assigned_doctor_id, statusHistories() hasMany DoctorVisitStatusHistory
- [x] T005 [P] Create `Modules/DoctorVisit/Models/DoctorVisitStatusHistory.php` — extend `Illuminate\Database\Eloquent\Model` (NOT BaseModel — no SoftDeletes, no audit fields); protected $table = 'doctor_visit_status_histories'; $fillable: visit_request_id, old_status, new_status, changed_by, note; relationships: visitRequest() belongsTo DoctorVisitRequest, changedBy() belongsTo User changed_by
- [x] T006 [P] Create `Modules/DoctorVisit/Transformers/DoctorVisitRequestResource.php` — extend `Illuminate\Http\Resources\Json\JsonResource`; toArray() returns: id, reference_number, visit_type, visit_reason, preferred_date, contact_phone, additional_notes, status, admin_notes, cancellation_reason, completed_at (ISO8601 or null), created_at (ISO8601), updated_at (ISO8601); nest patient (id, name, email) inline; nest preferredDoctor/assignedDoctor (id, name) using whenLoaded() or optional(); include statusHistories as array when loaded
- [x] T007 [P] Create `lang/en/doctor_visit.php` — keys: singular_title, plural_title; lbl_reference_number, lbl_patient_name, lbl_preferred_doctor, lbl_assigned_doctor, lbl_visit_type, lbl_visit_reason, lbl_preferred_date, lbl_contact_phone, lbl_additional_notes, lbl_status, lbl_admin_notes, lbl_cancellation_reason, lbl_completed_at, lbl_action; status_pending, status_confirmed, status_cancelled, status_completed; request_submitted_success, request_not_found, status_updated_success, doctor_assigned_success, notes_updated_success, invalid_status_transition, unauthorized_status_update
- [x] T008 [P] Create `Modules/DoctorVisit/database/seeders/DoctorVisitDatabaseSeeder.php` — create permissions: `doctor_visit_request.view`, `doctor_visit_request.create`, `doctor_visit_request.update_status`, `doctor_visit_request.assign_doctor`; assign all four to `admin` and `receptionist` roles; assign view + update_status to `doctor` role; assign view + create to `user` role; assign view-only to `demo_admin` role

**Checkpoint**: `php artisan module:migrate DoctorVisit && php artisan module:seed DoctorVisit` — both complete without errors.

---

## Phase 3: User Story 1 — Submit Doctor Visit Request (Priority: P1) 🎯 MVP

**Goal**: Authenticated mobile-app patient submits a home visit request and receives a `VR-YYYY-NNNN` reference number with `status: pending`.

**Independent Test**: `POST /api/v1/doctor-visit/requests` with valid payload → 201 with `reference_number` matching `VR-\d{4}-\d{4}` regex and `status: "pending"`.

- [x] T009 [US1] Create `Modules/DoctorVisit/Http/Requests/DoctorVisitStoreRequest.php` — authorize() returns true for auth users; rules(): visit_reason required string max:1000; preferred_date required date after_or_equal:today; contact_phone required string max:20; preferred_doctor_id nullable integer exists:doctors,id; additional_notes nullable string max:2000; wrapFailedValidation() returns JSON with status:false
- [x] T010 [US1] Create `Modules/DoctorVisit/Http/Controllers/API/DoctorVisitApiController.php` — implement store(): validate via DoctorVisitStoreRequest; create DoctorVisitRequest with patient_id=auth()->id(), visit_type=home_visit, all form fields; create initial DoctorVisitStatusHistory (old_status=null, new_status=pending, changed_by=auth()->id()); return new DoctorVisitRequestResource($request) with HTTP 201 and message `__('doctor_visit.request_submitted_success')`
- [x] T011 [US1] Add patient API routes to `Modules/DoctorVisit/Routes/api.php` — wrap in Route::middleware('auth:sanctum')->prefix('v1')->group: Route::post('doctor-visit/requests', store)->name('api.doctor_visit.store')
- [x] T012 [US1] Create `Modules/DoctorVisit/Tests/Feature/DoctorVisitApiTest.php` — US1 test cases using RefreshDatabase + SQLite: (a) authenticated patient submits valid payload → 201 with reference_number format `VR-YYYY-NNNN`; (b) unauthenticated request → 401; (c) missing visit_reason → 422 with field error; (d) preferred_date in the past → 422 with date error; (e) all required fields missing → 422 with all errors in one response; (f) preferred_doctor_id for inactive doctor is still accepted (201); (g) two sequential submissions have consecutive reference numbers

**Checkpoint**: `php artisan test --filter=DoctorVisitApiTest` — US1 tests pass.

---

## Phase 4: User Story 2 — View Own Visit Requests (Priority: P2)

**Goal**: Authenticated patient can list all their own requests (newest first, paginated) and retrieve full details of a specific request by reference number.

**Independent Test**: After submitting a request, `GET /api/v1/doctor-visit/requests` returns list with that request; `GET /api/v1/doctor-visit/requests/VR-2026-0001` returns full detail including `admin_notes`. Another patient's token cannot access the first patient's request (403).

- [x] T013 [US2] Add `index()` and `show()` methods to `Modules/DoctorVisit/Http/Controllers/API/DoctorVisitApiController.php` — index(): scope to patient_id=auth()->id(), orderBy created_at desc, paginate(15), return ResourceCollection with meta (current_page, per_page, total, last_page); show($reference): find by reference_number where patient_id=auth()->id() → 403 if found but wrong patient, 404 if not found; eager-load statusHistories; return full DoctorVisitRequestResource
- [x] T014 [US2] Add GET routes to `Modules/DoctorVisit/Routes/api.php` — Route::get('doctor-visit/requests', index)->name('api.doctor_visit.index'); Route::get('doctor-visit/requests/{reference}', show)->name('api.doctor_visit.show')
- [x] T015 [P] [US2] Add US2 test cases to `Modules/DoctorVisit/Tests/Feature/DoctorVisitApiTest.php`: (a) list returns own requests ordered newest-first with pagination meta; (b) list returns empty array (no error) when no requests; (c) list does NOT return other patients' requests; (d) show returns full details including admin_notes and status_histories; (e) show returns 403 when reference exists but belongs to different patient; (f) show returns 404 for non-existent reference

**Checkpoint**: US1 + US2 tests all pass. Patient submission and tracking flow is end-to-end functional.

---

## Phase 5: User Story 3 — Admin Views and Manages Visit Requests (Priority: P3)

**Goal**: Admin/receptionist/assigned-doctor can list all requests with filtering, update status with transition enforcement, assign doctors, and add notes. Admin dashboard Blade view surfaces all requests in a DataTable.

**Independent Test**: Seed 3 requests (pending/confirmed/completed). Admin `GET /api/v1/admin/doctor-visit/requests?status=pending` → only pending returned. `PUT .../VR-YYYY-0001/status` with `{"status":"confirmed"}` → status updated, history entry created, email dispatched. `PUT .../VR-YYYY-0001/status` again with `{"status":"pending"}` → 422 invalid transition.

### Admin API

- [x] T016 [US3] Create `Modules/DoctorVisit/Http/Requests/DoctorVisitStatusUpdateRequest.php` — authorize() returns true; rules(): status required in:confirmed,cancelled,completed; cancellation_reason nullable string max:1000; note nullable string max:1000
- [x] T017 [US3] Create `Modules/DoctorVisit/Http/Controllers/API/AdminDoctorVisitApiController.php` — add private `canManageRequest(User $user, DoctorVisitRequest $req): bool`: returns true if user hasRole(['admin','receptionist']); OR if user hasRole('doctor') AND $req->assignedDoctor AND $req->assignedDoctor->doctor_id === $user->id; else returns false
- [x] T018 [US3] Implement `index()` in `Modules/DoctorVisit/Http/Controllers/API/AdminDoctorVisitApiController.php` — query all DoctorVisitRequests; apply optional filters: ?status= (exact match), ?date_from= (preferred_date >=), ?date_to= (preferred_date <=), ?assigned_doctor_id= (exact match); eager-load patient, preferredDoctor, assignedDoctor; orderBy created_at desc; paginate(15); return collection with meta
- [x] T019 [P] [US3] Implement `updateStatus()` in `Modules/DoctorVisit/Http/Controllers/API/AdminDoctorVisitApiController.php` — resolve request by reference_number (404 if missing); check canManageRequest (403 if fail); validate via DoctorVisitStatusUpdateRequest; check VALID_TRANSITIONS[$currentStatus] contains new status (422 with invalid_status_transition if not); update status; set completed_at=now() if→completed; store cancellation_reason if→cancelled; create DoctorVisitStatusHistory entry (old_status, new_status, changed_by=auth()->id(), note from request); if new status in [confirmed, cancelled, completed]: Mail::to($visitRequest->patient->email)->send(new DoctorVisitStatusChanged($visitRequest)); return updated DoctorVisitRequestResource with status_updated_success message
- [x] T020 [P] [US3] Implement `assignDoctor()` in `Modules/DoctorVisit/Http/Controllers/API/AdminDoctorVisitApiController.php` — resolve request by reference_number; check auth user hasRole(['admin','receptionist']) else 403; validate doctor_id required exists:doctors,id; update assigned_doctor_id; return updated resource with doctor_assigned_success message
- [x] T021 [P] [US3] Implement `addNote()` in `Modules/DoctorVisit/Http/Controllers/API/AdminDoctorVisitApiController.php` — resolve request; check canManageRequest (403 if fail); validate admin_notes required string max:2000; update admin_notes; return updated resource with notes_updated_success message
- [x] T022 [US3] Add admin API routes to `Modules/DoctorVisit/Routes/api.php` — group: middleware auth:sanctum, prefix v1/admin/doctor-visit/requests; Route::get('/', index)->name('api.admin.doctor_visit.index'); Route::put('{reference}/status', updateStatus)->name('api.admin.doctor_visit.update_status'); Route::put('{reference}/assign-doctor', assignDoctor)->name('api.admin.doctor_visit.assign_doctor'); Route::post('{reference}/notes', addNote)->name('api.admin.doctor_visit.add_note')

### Email Notification

- [x] T023 [US3] Create `app/Mail/DoctorVisitStatusChanged.php` — extends Mailable; use Queueable, SerializesModels; public DoctorVisitRequest $visitRequest; constructor(DoctorVisitRequest $visitRequest); build(): subject("[Espitalia] Visit Request {$this->visitRequest->reference_number} — " . ucfirst($this->visitRequest->status)); markdown('emails.doctor_visit_status_changed'); with(['visitRequest' => $this->visitRequest])
- [x] T024 [P] [US3] Create `resources/views/emails/doctor_visit_status_changed.blade.php` — markdown mail component (@component('mail::message')): greeting "Dear {{ $visitRequest->patient->full_name }}"; state new status with human-readable label; show reference_number and preferred_date; show cancellation_reason paragraph (only when status=cancelled); Espitalia footer; @endcomponent

### Admin Dashboard (Blade)

- [x] T025 [US3] Create `Modules/DoctorVisit/Http/Controllers/Backend/DoctorVisitRequestController.php` — extends Controller; constructor sets $this->module_title='Doctor Visit Requests', $this->module_name='doctor_visit_request'; implement index() passing module vars to view; implement index_data() returning Datatables::of(DoctorVisitRequest::with(['patient','assignedDoctor'])) with columns: id, reference_number, patient name, preferred_date, assigned_doctor name, status (badge), created_at, action; implement show($id) loading request with statusHistories and passing to view; implement update_status($id) for AJAX inline status toggle; implement bulk_action() for bulk delete
- [x] T026 [US3] Add admin web routes to `Modules/DoctorVisit/Routes/web.php` — middleware ['auth','auth_check']; prefix app/doctor-visit; name prefix backend.doctor_visit.*; GET / → index; POST /index-data → index_data; GET /{id} → show; POST /update-status/{id} → update_status; POST /bulk-action → bulk_action
- [x] T027 [US3] Create `Modules/DoctorVisit/Resources/views/backend/doctor_visit_request/index.blade.php` — extend backend.layouts.app; use <x-backend.section-header> with title and breadcrumb; DataTable with server-side source index_data route; columns: reference_number, patient, preferred_date, assigned_doctor, status (colored badge), created_at, action; status filter dropdown above table; bulk action toolbar
- [x] T028 [P] [US3] Create `Modules/DoctorVisit/Resources/views/backend/doctor_visit_request/show.blade.php` — extend backend.layouts.app; detail card showing all fields; status history timeline (old→new, changed_by, timestamp, note); admin notes card with textarea + save button (POST to add_note API); status update card with dropdown of valid next transitions and submit button; assign doctor card (visible only to admin/receptionist) with Select2 doctor picker
- [x] T029 [P] [US3] Create `Modules/DoctorVisit/Resources/views/backend/doctor_visit_request/datatable/action_column.blade.php` — view button linking to backend.doctor_visit.show route; status badge using status_* translation keys; Phosphor icon `ph ph-eye` for view
- [x] T030 [US3] Add Doctor Visits sidebar entry to `app/Http/Middleware/GenerateMenus.php` — in admin/receptionist/doctor blocks: add entry with icon `ph ph-house-line`, label `__('sidebar.doctor_visit')`, route `backend.doctor_visit.index`; place under Requests section (near nurse request entry)
- [x] T031 [P] [US3] Add key to `lang/en/sidebar.php`: `'doctor_visit' => 'Doctor Visits'`

### Tests for User Story 3

- [x] T032 [US3] Add US3 test cases to `Modules/DoctorVisit/Tests/Feature/DoctorVisitApiTest.php`: (a) admin lists all requests with pagination; (b) ?status=pending filter returns only pending; (c) ?date_from= and ?date_to= filters work correctly; (d) valid transition pending→confirmed returns 200 with updated status and creates history entry; (e) invalid transition completed→confirmed returns 422 with invalid_status_transition message; (f) completed→anything is blocked (terminal state); (g) non-assigned doctor returns 403 on status update; (h) assigned doctor can update status; (i) email sent when status→confirmed; (j) email NOT sent when status stays pending (create only); (k) receptionist can assign doctor; (l) patient token returns 403 on admin endpoints

**Checkpoint**: All 3 user stories fully tested. `php artisan test --filter=DoctorVisit` — all pass.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Enable module in production config, verify routes, lint, confirm full test suite passes.

- [x] T033 Run `php artisan module:enable DoctorVisit` — verify `"DoctorVisit": true` in `modules_statuses.json`; run `php artisan optimize:clear`
- [ ] T034 [P] Run `./vendor/bin/pint Modules/DoctorVisit app/Mail/DoctorVisitStatusChanged.php` — fix any code-style violations
- [x] T035 Run `php artisan route:list --path=doctor-visit` — confirm 9 routes registered (3 patient API + 4 admin API + web routes)
- [ ] T036 Run `php artisan test --filter=DoctorVisit` — all tests pass; 0 failures, 0 errors

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1**: No dependencies — start immediately
- **Phase 2**: Requires Phase 1 (module must exist before models/migrations can be created)
- **Phase 3 (US1)**: Requires Phase 2 — specifically T004 (model) and T006 (transformer)
- **Phase 4 (US2)**: Requires Phase 3 (extends same DoctorVisitApiController)
- **Phase 5 (US3)**: Requires Phase 2 — does NOT require US1/US2 (independently testable with seeded data)
- **Phase 6**: Requires all user story phases complete

### User Story Dependencies

| Story | Depends On | Can Parallel With |
|-------|-----------|------------------|
| US1 (P1) | Foundation (Phase 2) | US3 (different controller) |
| US2 (P2) | US1 (same controller file) | — |
| US3 (P3) | Foundation (Phase 2) | US1 (different controller) |

### Within Each Phase

- T004 before T005/T006 (transformer imports the model)
- T009 (FormRequest) before T010 (controller uses it)
- T017 (canManageRequest) before T018/T019/T020/T021 (all call it)
- T023 (Mailable) before T019's dispatch call
- T025 (Backend controller) before T026/T027/T028/T029 (views need controller to exist)

---

## Parallel Execution Examples

### Phase 2 — after T004 completes

```
T005  DoctorVisitStatusHistory model
T006  DoctorVisitRequestResource transformer
T007  lang/en/doctor_visit.php translation file
T008  DoctorVisitDatabaseSeeder permissions
```

### Phase 5 — Admin API after T017 completes

```
T018  AdminDoctorVisitApiController@index
T019  AdminDoctorVisitApiController@updateStatus
T020  AdminDoctorVisitApiController@assignDoctor
T021  AdminDoctorVisitApiController@addNote
```

### Phase 5 — Blade views after T025 completes

```
T027  index.blade.php (DataTable list)
T028  show.blade.php (detail + history)
T029  datatable/action_column.blade.php
```

---

## Implementation Strategy

### MVP First (US1 only — 11 tasks)

1. Phase 1: T001 → T002 → T003
2. Phase 2: T004 → T005/T006/T007/T008 (parallel)
3. Phase 3: T009 → T010 → T011 → T012
4. **STOP and VALIDATE**: `php artisan test --filter=DoctorVisitApiTest` — US1 tests pass; manually POST to `/api/v1/doctor-visit/requests` and confirm `VR-YYYY-NNNN` reference returned

### Incremental Delivery

1. Foundation + US1 → patients can submit requests (MVP)
2. + US2 → patients can track status of their own requests
3. + US3 API → staff can manage requests via REST
4. + US3 Blade → staff can use the admin dashboard
5. Each increment is independently testable and deployable

### Parallel Team Strategy (2 developers)

After Foundation completes:
- **Dev A**: US1 → US2 (patient-facing API, sequential in same controller)
- **Dev B**: US3 API layer (AdminDoctorVisitApiController, independent controller)
- Both merge → Dev A or B: US3 Blade admin views

---

## Notes

- `[P]` = different files, no in-flight dependency — safe to parallelize
- `[USn]` label = traceability to spec.md user story for independent delivery
- `VALID_TRANSITIONS` is the single source of truth — API enforcement and tests both reference `DoctorVisitRequest::VALID_TRANSITIONS`
- Email dispatch is synchronous (`Mail::to()->send()`) — no queue needed for v1 per research.md Decision 4
- `canManageRequest()` covers three positive cases (admin ✅, receptionist ✅, assigned-doctor ✅) and two negative cases (non-assigned doctor ❌, patient ❌) — all five must be covered in T032
- Migrations must be SQLite-compatible (no `fullText` indexes, no `enum()` — use `string()` for status fields)
- Commit at each phase checkpoint minimum; prefer per-task commits
