# Web Route Contracts: Labs & Radiology Centers

**Feature Branch**: `006-admin-labs-radiology-ui`
**Date**: 2026-04-02

All routes are registered in `Modules/RadiologyLab/Routes/web.php` under:
- Prefix: `app/`
- Name prefix: `backend.`
- Middleware: `['auth', 'auth_check']`

---

## Labs Routes

| Method | URI | Name | Controller Method | Permission |
|--------|-----|------|-------------------|------------|
| GET | /app/labs | backend.labs.index | LabsController@index | view_labs |
| GET | /app/labs/create | backend.labs.create | LabsController@create | add_labs |
| GET | /app/labs/{id} | backend.labs.show | LabsController@create | edit_labs |
| POST | /app/labs | backend.labs.store | LabsController@store | add_labs / edit_labs |
| DELETE | /app/labs/{id} | backend.labs.destroy | LabsController@destroy | delete_labs |
| GET | /app/labs/labs-index-data | backend.labs.index_data | LabsController@index_data | view_labs |
| POST | /app/labs/labs-bulk-action | backend.labs.bulk_action | LabsController@bulk_action | delete_labs |
| POST | /app/labs/labs-action | backend.labs.action | LabsController@action | delete_labs |
| POST | /app/labs/update-status/{id} | backend.labs.update_status | LabsController@update_status | edit_labs |

---

## Radiology Centers Routes

| Method | URI | Name | Controller Method | Permission |
|--------|-----|------|-------------------|------------|
| GET | /app/radiology-centers | backend.radiology-centers.index | RadiologyCentersController@index | view_radiology_centers |
| GET | /app/radiology-centers/create | backend.radiology-centers.create | RadiologyCentersController@create | add_radiology_centers |
| GET | /app/radiology-centers/{id} | backend.radiology-centers.show | RadiologyCentersController@create | edit_radiology_centers |
| POST | /app/radiology-centers | backend.radiology-centers.store | RadiologyCentersController@store | add_radiology_centers / edit_radiology_centers |
| DELETE | /app/radiology-centers/{id} | backend.radiology-centers.destroy | RadiologyCentersController@destroy | delete_radiology_centers |
| GET | /app/radiology-centers/radiology-centers-index-data | backend.radiology-centers.index_data | RadiologyCentersController@index_data | view_radiology_centers |
| POST | /app/radiology-centers/radiology-centers-bulk-action | backend.radiology-centers.bulk_action | RadiologyCentersController@bulk_action | delete_radiology_centers |
| POST | /app/radiology-centers/radiology-centers-action | backend.radiology-centers.action | RadiologyCentersController@action | delete_radiology_centers |
| POST | /app/radiology-centers/update-status/{id} | backend.radiology-centers.update_status | RadiologyCentersController@update_status | edit_radiology_centers |

---

## Existing Location API (Used for dependent dropdown)

| Method | URI | Purpose |
|--------|-----|---------|
| GET | /api/v1/governorates | List all governorates |
| GET | /api/v1/cities?governorate_id={id} | List cities for a governorate |
