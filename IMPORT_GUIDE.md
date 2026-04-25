# Postman Import Guide

## Import files

1. Open Postman (desktop).
2. Click **Import** (top-left).
3. Drop in both files from the repo root:
   - `postman_collection_full.json`
   - `postman_environment.json`
4. In the top-right environment selector, choose **KiviCare Local**.

## Configure variables

Open the environment (eye icon) and set:

- `base_url` — your app URL, e.g. `http://localhost:8000`
- `auth_token` — a Sanctum token from `POST /api/v1/login` (or your auth endpoint). Paste the raw token only; the `Bearer ` prefix is already added by each request.

## Try it

1. Expand the **KiviCare API** collection.
2. Pick any `GET` request under, e.g., **Clinic** or **Appointment**.
3. Click **Send**. You should get `200 OK` (after setting a valid `auth_token`).

## Notes

- All requests send `Authorization: Bearer {{auth_token}}` and `Accept: application/json`.
- POST/PUT/PATCH bodies are pre-filled from Laravel `FormRequest::rules()` when one was detected; otherwise the body is `{}` — adjust before sending.
- Path parameters such as `{id}` are preserved as Postman path variables.