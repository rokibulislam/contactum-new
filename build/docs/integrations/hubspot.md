# HubSpot Integration

Automatically create or update contacts in HubSpot CRM when a visitor submits a Contactum form. Optionally add the contact to a HubSpot static list at the same time.

---

## Requirements

- **Contactum Pro**
- An active **HubSpot account** (free CRM plan supported)
- A HubSpot **Private App access token**

---

## Understanding HubSpot Authentication

HubSpot deprecated API keys in 2022. The current and only supported authentication method is a **Private App access token**. Private Apps are created inside your HubSpot account and grant scoped access to your CRM data. The access token starts with `pat-`.

---

## 1. Create a HubSpot Private App and Get an Access Token

1. Log in to your HubSpot account at [app.hubspot.com](https://app.hubspot.com).
2. Click the **Settings** (gear) icon in the top navigation bar.
3. In the left sidebar go to **Integrations → Private Apps**.
4. Click **Create a private app**.
5. On the **Basic Info** tab, give the app a name (e.g. `Contactum`) and an optional description.
6. Switch to the **Scopes** tab and add the following scopes:
   - `crm.objects.contacts.read` — required to verify the token and read contact data
   - `crm.objects.contacts.write` — required to create and update contacts
   - `crm.lists.read` — required to load the list dropdown
   - `crm.lists.write` — required to add contacts to static lists
7. Click **Create app**, then confirm in the dialog.
8. On the app detail page, click **Show token** under the **Access Token** section.
9. Copy the token — it starts with `pat-`.

> Keep the access token private — it grants API access scoped to whatever permissions you configured. You can revoke or regenerate it from the Private App settings at any time.

---

## 2. Connect HubSpot in Contactum

1. Go to **Contactum → Settings → Integrations → HubSpot**.
2. Paste the access token into the **Access Token** field.
3. Click **Save Settings**.

Contactum validates the token by calling `GET /crm/v3/objects/contacts` (fetching 1 contact) on the HubSpot API. If the request succeeds without an error message in the response, the token is confirmed. A **"Your HubSpot access token has been verified and saved"** message appears and the status badge turns green.

If the token is invalid or lacks the required scopes, the error message returned by HubSpot is shown.

**To disconnect**, click **Disconnect HubSpot**. This clears the access token and stops all HubSpot contact syncing until you reconnect.

---

## 3. Enable HubSpot on a Specific Form

The global connection does not sync anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **HubSpot** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | No | A HubSpot static list to add the contact to after creation. Click the refresh (↻) icon to load all static lists. Leave blank to create/update the contact without list membership |
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the contact's first name |
| **Last Name** | No | Map to a text or name field for the contact's last name |

> **List is optional.** A contact is always created or updated in HubSpot CRM regardless of whether a list is selected. The list controls whether the contact is also added to a HubSpot static list.

> **Only static lists appear in the dropdown.** HubSpot's active (smart) lists are filter-based and cannot be managed via API. Only manually managed static lists are shown.

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Contact Syncing Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The HubSpot integration checks that:
   - The global access token is saved and verified (`status: true`)
   - The form has HubSpot enabled
   - An **Email** mapping is configured
3. The email address is resolved from the mapped smart tag. If empty, the sync is skipped silently.
4. First Name and Last Name are resolved from their mapped fields.
5. A batch upsert request is sent to the HubSpot CRM API:

```
POST https://api.hubapi.com/crm/v3/objects/contacts/batch/upsert
Authorization: Bearer pat-...
Content-Type: application/json

{
  "inputs": [
    {
      "idProperty": "email",
      "id": "subscriber@example.com",
      "properties": {
        "email": "subscriber@example.com",
        "firstname": "Jane",
        "lastname": "Doe"
      }
    }
  ]
}
```

The `idProperty: email` tells HubSpot to match on the email address — if a contact with that email already exists, it is updated; otherwise a new contact record is created.

6. If a **List** was selected and the upsert returns a contact ID, a second request adds the contact to the list:

```
POST https://api.hubapi.com/contacts/v1/lists/{list_id}/add
Authorization: Bearer pat-...
Content-Type: application/json

{
  "vids": [12345]
}
```

> Contact syncing happens after the entry is saved. A HubSpot API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Contact Behaviour in HubSpot

| Scenario | Result |
|---|---|
| New email address | Contact created in HubSpot CRM; added to list if selected |
| Existing email address | Contact properties (firstname, lastname) updated; added to list if selected |
| Existing email, already in selected list | HubSpot accepts the request silently — no duplicate membership created |
| Empty email from smart tag | Sync skipped silently |
| List not selected | Contact created/updated in CRM only; no list membership change |
| Invalid or expired access token | Sync skipped silently; form still processes normally |
| Token lacks required scopes | Sync skipped silently; error logged if `WP_DEBUG_LOG` is enabled |

---

## 7. Troubleshooting

### Access token is not validating

- Confirm the token starts with `pat-` and was copied from HubSpot → **Settings → Private Apps** with no extra spaces.
- Confirm the Private App includes the `crm.objects.contacts.read` and `crm.objects.contacts.write` scopes — the auth test reads one contact, so read access is required.
- If the token was recently rotated in HubSpot, update it in Contactum.

### List dropdown is empty after clicking refresh

- Confirm the global access token shows the green valid badge before refreshing.
- Confirm the Private App includes the `crm.lists.read` scope.
- Confirm your HubSpot account has at least one **static** list. Go to **HubSpot → Contacts → Lists** and create a static list if none exist. Active (smart) lists do not appear in the dropdown.

### Contacts are not appearing in HubSpot

1. Confirm the form has HubSpot toggled **on** and was saved after enabling.
2. Confirm the **Email** field is mapped in the Configure dialog.
3. Confirm the mapped field contains a valid email on submission — check **Contactum → Entries**.
4. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for exceptions during submission.
5. In HubSpot, go to **Contacts → All Contacts** and search by the test email address.

### Contact is created but not added to the list

- Confirm the **List** dropdown has a list selected in the Configure dialog.
- Confirm the selected list is a **static** list, not an active list — only static lists support manual membership via API.
- Confirm the Private App includes the `crm.lists.write` scope.
- The list add step only runs if the upsert request returns a contact ID. If the upsert failed silently, the list step is skipped. Check `wp-content/debug.log` for API errors.

### "This app hasn't been granted all required scopes" error

- Edit the Private App in HubSpot → **Settings → Private Apps**, go to the **Scopes** tab, and add the missing scopes listed in Step 1.
- After saving the scope changes, HubSpot issues a new access token — copy it and update it in Contactum.

---

## 8. Notes

- **API versions used:** Contact upsert uses CRM API v3 (`/crm/v3/objects/contacts/batch/upsert`); list membership uses the older Contacts API v1 (`/contacts/v1/lists/{id}/add`). Both are active and supported.
- **Upsert by email:** The `idProperty: email` field in the upsert payload tells HubSpot to deduplicate by email address — the same email submitted twice will update the existing record rather than create a duplicate.
- **HubSpot property names:** HubSpot's built-in name properties are `firstname` and `lastname` (lowercase, no underscore). These are the exact property keys sent in the API payload.
- **Static lists only:** The list dropdown fetches only static (manually managed) lists. HubSpot active lists are filter-driven and cannot be populated via API.
- **API key deprecation:** HubSpot removed support for API keys (hapikey) in 2022. Only Private App access tokens are used. If you previously connected using an API key in an older version, you must reconnect using a Private App token.
- **Credentials storage:** `accessToken` and `status` are stored in `wp_options` under the key `contactum_hubspot`. Restrict database access and keep WordPress authentication keys strong.
