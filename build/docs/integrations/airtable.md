# Airtable Integration

Automatically create a new record in an Airtable table whenever a Contactum form is submitted. Unlike email marketing integrations, Airtable is a database — each form submission becomes a new row in your chosen table.

---

## Requirements

- **Contactum Pro**
- An active **Airtable account** (free plan supported)
- An Airtable **Personal Access Token**
- An Airtable **Base ID** and **Table name or Table ID**

---

## 1. Prepare Your Airtable Table

Before connecting, your Airtable table must have columns whose names **exactly match** the fields you intend to map. Contactum sends data using these fixed column names:

| Contactum Field | Expected Airtable Column Name |
|---|---|
| Email Field | `Email` |
| First Name Field | `First Name` |
| Last Name Field | `Last Name` |
| Message Field | `Message` |

**Setup steps:**

1. Log in to [airtable.com](https://airtable.com) and open the Base you want to use (or create a new one).
2. Open the table you want to receive form submissions (or create a new table).
3. Add or rename columns to match the names above. You only need to create columns for the fields you plan to map — unused fields can be left out.
4. Column names are **case-sensitive** and must match exactly, including spaces (e.g. `First Name`, not `first_name` or `firstname`).

---

## 2. Get Your Airtable Personal Access Token

Airtable no longer issues legacy API keys for new accounts. You need a **Personal Access Token (PAT)**.

1. Log in to your Airtable account.
2. Go to [airtable.com/create/tokens](https://airtable.com/create/tokens) or navigate to your **Account → Developer Hub → Personal access tokens**.
3. Click **Create token**.
4. Give it a name (e.g. `Contactum`).
5. Under **Scopes**, add:
   - `data.records:write` — required to create records
   - `data.records:read` — required for the connection test
6. Under **Access**, click **Add a base** and select the specific base Contactum will write to. Limiting access to one base is recommended.
7. Click **Create token** and copy the token — it is shown only once.

> Personal Access Tokens start with `pat`. Keep the token private — it grants write access to the selected bases.

---

## 3. Find Your Base ID and Table ID

**Base ID**

1. Open your base in Airtable.
2. Look at the browser URL — it follows the pattern:
   ```
   https://airtable.com/appXXXXXXXXXXXXXX/tblXXXXXXXXXXXXXX/...
   ```
3. The segment starting with `app` is your **Base ID** (e.g. `appXXXXXXXXXXXXXX`).

Alternatively, go to [airtable.com/api](https://airtable.com/api), select your base, and the Base ID is shown at the top of the documentation page.

**Table name or Table ID**

You can use either:
- The **table name** exactly as shown in the Airtable tab (e.g. `Contacts`, `Form Submissions`). Names with spaces are supported.
- The **table ID**, which starts with `tbl` and is visible in the URL when you have that table open.

Using the table name is simpler. Use the table ID if your table name contains special characters or if you rename tables frequently.

---

## 4. Connect Airtable in Contactum

1. Go to **Contactum → Settings → Integrations → Airtable**.
2. Fill in all three fields — all are required:

| Field | Description |
|---|---|
| **Airtable API Key** | Your Personal Access Token (starts with `pat`) |
| **Airtable Base ID** | The Base ID from Step 3 (starts with `app`) |
| **Airtable Table Name or ID** | The table name (e.g. `Contacts`) or table ID (starts with `tbl`) |

3. Click **Save Settings**.

Contactum immediately verifies the credentials by fetching up to 1 record from `GET /v0/{base_id}/{table_id}?maxRecords=1`. If the request returns HTTP 200 or 201, the connection is confirmed and a **"Your Airtable connection has been verified and saved"** message appears with the status badge turning green.

If any of the three fields is empty, Contactum returns a validation error — all three are required to save.

**To disconnect**, click **Disconnect Airtable**. This clears all three stored values and stops record creation on all forms until you reconnect.

---

## 5. Enable Airtable on a Specific Form

The global connection does not create records on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Airtable** and toggle it on.

   > If the card shows **Not Connected**, complete Step 4 first.

4. Click **Configure** to open the field mapping dialog.

---

## 6. Configure Field Mapping

| Field | Required | Airtable Column | Description |
|---|---|---|---|
| **Email Field** | No* | `Email` | Map to the form field that collects the email address |
| **First Name Field** | No* | `First Name` | Map to a text or name field |
| **Last Name Field** | No* | `Last Name` | Map to a text or name field |
| **Message Field** | No* | `Message` | Map to a textarea or any other text field |

*At least one field must resolve to a non-empty value on submission, otherwise no record is created.

### Using Smart Tags

Each field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email Field      →  {email}
First Name Field →  {first_name}
Last Name Field  →  {last_name}
Message Field    →  {message}
```

Only fields with a non-empty resolved value are included in the Airtable record — unmapped or empty fields are skipped entirely.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 7. How Record Creation Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Airtable integration checks that:
   - The global connection is verified (`status: true`)
   - The form has Airtable enabled
3. Smart tags in each mapped field are resolved against the submitted entry data.
4. Any field that resolves to an empty string is dropped from the payload.
5. If all mapped fields are empty after resolution, no record is created and the process stops silently.
6. A `POST` request is sent to the Airtable API:

```
POST https://api.airtable.com/v0/{base_id}/{table_id}
Authorization: Bearer pat...
Content-Type: application/json

{
  "records": [
    {
      "fields": {
        "Email": "visitor@example.com",
        "First Name": "Jane",
        "Last Name": "Doe",
        "Message": "Hello, I'd like more information."
      }
    }
  ]
}
```

6. Airtable creates a **new record** (row) in your table. Unlike email marketing integrations, there is no upsert — each form submission always creates a separate new record, even if the same email submits multiple times.

> Record creation happens after the entry is saved. An Airtable API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 8. Troubleshooting

### "Please provide valid Airtable Base ID" error on save

- Confirm the Base ID starts with `app` and was copied from the browser URL or the Airtable API documentation page, not from the base name.
- Confirm your Personal Access Token has `data.records:read` scope on the selected base.
- Confirm the table name or ID matches exactly — table names are case-sensitive.

### Connection saves but records are not appearing in Airtable

1. Confirm the form has Airtable toggled **on** and was saved after enabling.
2. Confirm at least one field is mapped in the Configure dialog.
3. Submit a test entry and verify field values appear in **Contactum → Entries**.
4. Check that the mapped smart tags resolve to non-empty values — if all fields are empty after resolution, no record is created.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### "AUTHENTICATION_REQUIRED" or 401 error

- Your Personal Access Token may have expired or been revoked. Generate a new token in Airtable → **Developer Hub → Personal access tokens** and update it in Contactum.
- Confirm the token scope includes `data.records:write` and the base is listed under the token's **Access** section.

### "NOT_FOUND" error (HTTP 404)

- The table name or ID does not match any table in the specified base. Check the exact spelling including capitalisation.
- If using a table name with spaces (e.g. `Form Submissions`), enter it exactly as shown in the Airtable tab.

### Records are created with missing fields

- The Airtable column name must match exactly: `Email`, `First Name`, `Last Name`, or `Message`. A mismatch (e.g. `email` instead of `Email`) will cause Airtable to reject that field or create a new unintended column.
- Verify the column exists in the table and is not hidden.
- Confirm the smart tag in the mapping resolves to a non-empty value by checking the submitted entry in **Contactum → Entries**.

### "INVALID_PERMISSIONS_OR_MODEL_NOT_FOUND" error

- The Personal Access Token does not have `data.records:write` permission, or the base is not listed under the token's access.
- Edit the token in Airtable → **Developer Hub** and add the missing scope or base.

---

## 9. Notes

- **New record per submission:** Every form submission creates a new row — Airtable does not deduplicate by email or any other field. If you need deduplication, use an Airtable automation or a third-party tool to merge duplicate records.
- **Column names are fixed:** The integration maps to four predefined column names (`Email`, `First Name`, `Last Name`, `Message`). Custom Airtable columns beyond these four cannot be mapped through the standard dialog.
- **Credentials storage:** `api_key`, `base_id`, and `table_id` are stored in `wp_options` under the key `airtable`. Restrict database access and keep WordPress authentication keys strong.
- **API version:** Contactum uses Airtable's REST API v0 (`https://api.airtable.com/v0/`). Personal Access Tokens are the only supported authentication method — legacy API keys are not supported.
