# Admin Web Routes Contract: Specializations

**Prefix**: `app/specializations` | **Name prefix**: `backend.specializations.` | **Middleware**: `['auth', 'auth_check']`

## Routes

| Method | URI | Action | Name | Permission |
|--------|-----|--------|------|------------|
| GET | `app/specializations` | `index` | `backend.specializations.index` | `view_specialization` |
| GET | `app/specializations/index_data` | `index_data` | `backend.specializations.index_data` | `view_specialization` |
| GET | `app/specializations/create` | `create` | `backend.specializations.create` | `add_specialization` |
| POST | `app/specializations` | `store` | `backend.specializations.store` | `add_specialization` |
| GET | `app/specializations/{id}/edit` | `edit` | `backend.specializations.edit` | `edit_specialization` |
| PUT | `app/specializations/{id}` | `update` | `backend.specializations.update` | `edit_specialization` |
| DELETE | `app/specializations/{id}` | `destroy` | `backend.specializations.destroy` | `delete_specialization` |
| POST | `app/specializations/bulk-action` | `bulk_action` | `backend.specializations.bulk_action` | `delete_specialization` |
| POST | `app/specializations/update-status/{id}` | `update_status` | `backend.specializations.update_status` | `edit_specialization` |

## index_data Response (DataTable JSON)

```json
{
  "draw": 1,
  "recordsTotal": 50,
  "recordsFiltered": 25,
  "data": [
    {
      "id": 1,
      "check": "<input type='checkbox' ...>",
      "name": "Cardiology",
      "parent": "Internal Medicine",
      "status": "<span class='badge bg-success'>Active</span>",
      "updated_at": "2026-04-21 ...",
      "action": "<edit/delete buttons>"
    }
  ]
}
```

## store/update Request Body

```
name_ar: string (required)
name_en: string (required)
description_ar: string (optional)
description_en: string (optional)
parent_id: integer (optional, must exist in specializations)
icon: string (optional, Phosphor icon class)
status: 0|1 (required)
```

## bulk_action Request Body

```
ids[]: array of integer (required)
action_type: "delete" | "restore" | "change-status" | "permanently-delete"
status: 0|1 (required when action_type is "change-status")
```
