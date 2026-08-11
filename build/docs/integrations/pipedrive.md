# Pipedrive Integration

Automatically create a new Person (contact) in Pipedrive CRM when a visitor submits a Contactum form. Unlike email marketing integrations, Pipedrive is a sales CRM — each form submission creates a new contact record in your Pipedrive People directory.

---

## Requirements

- **Contactum Pro**
- An active **Pipedrive account**
- A Pipedrive **API Token**

---

## 1. Get Your Pipedrive API Token

1. Log in to your Pipedrive account at [app.pipedrive.com](https://app.pipedrive.com).
2. Click your avatar or name in the top-right corner and select **Personal preferences**.
3. Go to the **API** tab.
4. Your **API Token** is displayed here. Copy it.

> Keep your API Token private — it grants full access to your Pipedrive account data including all contacts, deals, and pipelines.

---

## 2. Connect Pipedrive in Contactum

1. Go to **Contactum → Settings → Integrations → Pipedrive**.
2. Paste your API Token into the **API Token** field.
3. Click **Save Settings**.

Contactum validates the token by calling `GET /users/me` on the Pipedrive v1 API. If the response indicates success, the token is confirmed. A **"Your Pipedrive API token has been verified and saved"** message appears and the status badge turns green.

If the token is invalid, the error returned by Pipedrive is shown.

**To disconnect**, click **Disconnect Pipedrive**. This clears the stored token and stops all Pipedrive contact creation until you reconnect.

---

## 3. Enable Pipedrive on a Specific Form

The global connection does not create any records on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Pipedrive** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the contact's first name |
| **Last Name** | No | Map to a text or name field for the contact's last name |
| **Phone** | No | Map to a phone or text field for the contact's phone number |

> **No list or pipeline selection.** Pipedrive is a CRM — there are no mailing lists to choose from. The Person is created in your Pipedrive People directory. You can assign them to deals and pipelines manually inside Pipedrive after they are created.

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
Phone      →  {phone}
```

### Name Handling

Pipedrive stores a single **Name** field for each Person. Contactum joins the resolved First Name and Last Name with a space:

```
"Jane" + " " + "Doe"  →  "Jane Doe"
```

If both First Name and Last Name resolve to empty, the email address is used as the Person's name instead.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Person Creation Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Pipedrive integration checks that:
   - The global API Token is saved and verified (`status: true`)
   - The form has Pipedrive enabled
   - An **Email** mapping is configured
3. The email address is resolved from the mapped smart tag. If empty, the sync is silently skipped.
4. First Name, Last Name, and Phone are resolved from their mapped fields.
5. First Name and Last Name are joined into a single name. If both are empty, the email is used as the name.
6. A `POST` request is sent to the Pipedrive API:

```
POST https://api.pipedrive.com/v1/persons
Authorization: Bearer {apiToken}
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": [
    { "value": "subscriber@example.com", "primary": true }
  ],
  "phone": [
    { "value": "+1 555 000 0000", "primary": true }
  ]
}
```

The `phone` field is only included if a phone value was mapped and resolved to a non-empty value.

7. Pipedrive creates a new Person record in your contacts directory.

> Person creation happens after the entry is saved. A Pipedrive API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Person Behaviour in Pipedrive

| Scenario | Result |
|---|---|
| New email address | New Person created in Pipedrive |
| Existing email address | Pipedrive creates another new Person — no duplicate check is performed |
| Empty email from smart tag | Person creation skipped silently |
| Phone not mapped or empty | Person created without a phone number |
| Name fields both empty | Email address is used as the Person's name |
| Invalid or expired API Token | Person creation skipped silently; form still processes normally |

> **Pipedrive does not deduplicate by email automatically.** If the same email address submits the form twice, two separate Person records are created. To merge duplicates, use Pipedrive's built-in **Merge duplicate contacts** tool under **Contacts → People → Duplicates**.

---

## 7. Troubleshooting

### "Authentication failed" or "Unauthorized" error on save

- Confirm the API Token was copied from Pipedrive → **Personal preferences → API** with no extra spaces.
- If you recently reset your API Token in Pipedrive, the old token is invalid. Copy the new token and update it in Contactum.

### Persons are not appearing in Pipedrive after form submission

1. Confirm the form has Pipedrive toggled **on** and was saved after enabling.
2. Confirm the **Email** field is mapped in the Configure dialog.
3. Submit a test entry and verify the email value appears in **Contactum → Entries**.
4. Log in to Pipedrive and go to **Contacts → People**. Search for the test email or sort by date added.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API exceptions during submission.

### Person is created with the wrong name or no name

- Confirm the First Name and Last Name smart tags are correctly mapped in the Configure dialog.
- If both name fields resolve to empty, the email address is used as the Person's name automatically.
- Submit a test entry and check what values appear in **Contactum → Entries** for the name fields.

### Duplicate persons appearing in Pipedrive

Pipedrive does not check for existing email addresses when creating new Persons via the API. Every form submission creates a new record. To clean up duplicates:

1. In Pipedrive, go to **Contacts → People**.
2. Look for a **Duplicates** or **Merge** option (availability depends on your Pipedrive plan).
3. Alternatively, use a Pipedrive automation or third-party tool to merge records matching the same email.

### Phone number is not appearing on the Person

- Confirm the Phone field is mapped in the Configure dialog and points to a form field that collects a phone number.
- Check that the mapped field contains a value in **Contactum → Entries** for the test submission.
- If the mapped field is empty on submission, the phone is silently omitted from the Person record.

---

## 8. Notes

- **CRM, not email marketing:** Pipedrive creates **Person** records (contacts) in your CRM — not email marketing subscribers. There are no lists, audiences, or campaigns involved.
- **API version:** Contactum uses Pipedrive API v1 (`https://api.pipedrive.com/v1/`).
- **Auth method:** `Authorization: Bearer {apiToken}` — standard Bearer token authentication.
- **Email and phone format:** Pipedrive stores email and phone as arrays of objects with `value` and `primary` flags. Contactum always sends them marked as `primary: true`.
- **No pipeline assignment:** The Person is created in Pipedrive's contact directory without being assigned to any deal or pipeline. Pipeline assignment must be done manually or via a Pipedrive automation after the Person is created.
- **No upsert:** Every form submission creates a new Person — there is no lookup or update of existing records by email. Deduplication must be handled manually inside Pipedrive.
- **Credentials storage:** `apiToken` and `status` are stored in `wp_options` under the key `contactum_pipedrive`. Restrict database access and keep WordPress authentication keys strong.
