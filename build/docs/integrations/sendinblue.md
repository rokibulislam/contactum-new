# Sendinblue (Brevo) Integration

Automatically add form submitters as contacts to a Sendinblue contact list when they submit a Contactum form.

> **Note:** Sendinblue has rebranded to **Brevo**. The product is the same — the API endpoint, API key format, and all functionality covered in this document remain unchanged. Both names refer to the same service.

---

## Requirements

- **Contactum Pro**
- An active **Sendinblue / Brevo account**
- A Sendinblue **API v3 key**

---

## 1. Get Your Sendinblue API Key

1. Log in to your Sendinblue account at [app.brevo.com](https://app.brevo.com) (or [app.sendinblue.com](https://app.sendinblue.com)).
2. Click your account name in the top-right corner and go to **SMTP & API**.
3. Open the **API Keys** tab.
4. Click **Generate a new API key**, give it a name (e.g. `Contactum`), and click **Generate**.
5. Copy the key — it is shown only once.

> API keys are long strings starting with `xkeysib-`. Keep the key private. Anyone with access to it can read and write your contacts.

---

## 2. Connect Sendinblue in Contactum

1. Go to **Contactum → Settings → Integrations → Sendinblue**.
2. Paste your API key into the **API Key** field.
3. Click **Save Settings**.

Contactum immediately validates the key by calling `GET /account` on the Sendinblue API. A successful response must contain a valid account email — if it does, a **"Your Sendinblue configuration is valid"** message is shown and the status badge turns green.

If the key fails validation, an error message from Sendinblue is displayed. Check that you copied the full `xkeysib-…` key with no leading or trailing spaces.

**To disconnect**, click **Disconnect Sendinblue**. This clears the stored key and disables all Sendinblue contact creation across every form until you reconnect.

---

## 3. Create a Contact List in Sendinblue (if needed)

Contactum adds contacts to an existing Sendinblue **Contact List**. If you do not have one yet:

1. In your Sendinblue account go to **Contacts → Lists → Create a list**.
2. Give the list a name and save it.
3. The list will appear in the Contactum integration dropdown after you click the refresh button.

---

## 4. Enable Sendinblue on a Specific Form

The global API connection does not subscribe anyone on its own — you must enable and configure the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Sendinblue** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 5. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The Sendinblue contact list to add the contact to. Click the refresh (↻) icon to load all lists from your account |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field. Stored as the `FIRSTNAME` attribute on the Sendinblue contact |
| **Last Name** | No | Map to a text or name field. Stored as the `LASTNAME` attribute on the Sendinblue contact |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button next to the input and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

You can also type a smart tag manually if you know the field name.

### Refreshing Lists

If you created a new list in Sendinblue after connecting, click the **refresh (↻)** button next to the List dropdown. Contactum fetches all lists from your account in pages of 50, iterating until all lists are loaded.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 6. How Contact Creation Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Sendinblue integration checks that:
   - The global API key is saved and verified (`status: true`)
   - The form has Sendinblue enabled
   - A **List** and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST /contacts` request is sent to the Sendinblue API with the following payload:

```json
{
  "email": "subscriber@example.com",
  "listIds": [42],
  "attributes": {
    "FIRSTNAME": "Jane",
    "LASTNAME": "Doe"
  },
  "updateEnabled": true
}
```

The `attributes` object is only included for First Name and Last Name when those fields resolve to a non-empty value.

6. Sendinblue creates the contact (HTTP 201) or updates the existing contact (HTTP 204) and adds them to the selected list.

> **Upsert behaviour:** `updateEnabled: true` is always sent. If the email address already exists in Sendinblue, the contact's `FIRSTNAME` and `LASTNAME` attributes are updated and the contact is added to the list. No duplicate contacts are created.

> A Sendinblue API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 7. Contact Attributes in Sendinblue

Sendinblue stores contact data as named attributes. Contactum writes to the following built-in attributes:

| Sendinblue Attribute | Populated from |
|---|---|
| `FIRSTNAME` | **First Name** field mapping (only if non-empty) |
| `LASTNAME` | **Last Name** field mapping (only if non-empty) |

These attribute names are uppercase as required by the Sendinblue API. If you have custom attributes defined in your Sendinblue account, they cannot currently be mapped through the standard integration dialog.

---

## 8. Contact Behaviour in Sendinblue

| Scenario | Result |
|---|---|
| New email address | Contact created and added to the list |
| Existing email, same list | `FIRSTNAME` / `LASTNAME` attributes updated; list membership unchanged |
| Existing email, different list | Contact added to the new list; existing list memberships unchanged |
| Existing email, unsubscribed from list | Sendinblue may reject re-adding to a list the contact unsubscribed from; form still completes normally |
| Empty email from smart tag | Subscription skipped silently |
| API key invalid or revoked | Subscription skipped silently; form still processes normally |

---

## 9. Troubleshooting

### API key is not validating

- Confirm you are using an **API v3** key (starts with `xkeysib-`). Classic SMTP keys are not accepted.
- Check that the key has not been deleted or regenerated in Sendinblue → **SMTP & API → API Keys**.
- Paste the key into a plain-text editor first to strip any invisible characters before copying it into Contactum.

### List dropdown is empty after clicking refresh

- Confirm you have at least one **Contact List** created in Sendinblue → **Contacts → Lists**.
- The global API key must be saved and show the green valid badge before lists can be fetched.
- If your account has many lists, the fetch paginates automatically (50 per request). Wait for it to complete before checking the dropdown.

### Contacts are not appearing in Sendinblue

1. Confirm the form has Sendinblue toggled **on** and was saved after enabling.
2. Confirm a **List** is selected in the Configure dialog — the integration silently skips if no list ID is set.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email address on submission.
4. Submit a test entry and verify the email value appears in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for any PHP exceptions during form submission.

### First Name / Last Name are not updating in Sendinblue

- Verify the smart tags used (`{first_name}`, `{last_name}`) match the **name** attribute of the corresponding form fields.
- Sendinblue attribute names are uppercase (`FIRSTNAME`, `LASTNAME`). Contactum handles the mapping automatically — no changes are needed on the Sendinblue side.
- If a mapped field is blank on submission (e.g. an optional field the user left empty), the attribute is not sent and the existing value in Sendinblue is preserved.

### Contact is blocked — "Contact already unsubscribed"

If a contact has previously clicked **Unsubscribe** in a Sendinblue email, Sendinblue blocks re-addition to lists via the API. This is a Sendinblue-level restriction and cannot be overridden from Contactum. The contact must re-subscribe through a Sendinblue-native opt-in form or be manually reactivated by an account admin under **Contacts → Blocklist**.

---

## 10. Notes

- Only one contact list can be targeted per form. To add a contact to multiple lists from a single submission, use Sendinblue's **Automation** feature to trigger list membership rules when a new contact is added.
- The API key is stored in `wp_options` under `contactum_sendinblue`. Restrict database access and keep WordPress authentication keys strong.
- Sendinblue's API is versioned at v3 (`https://api.sendinblue.com/v3/`). The API key is passed in the `api-key` request header (not as a Bearer token or query parameter).
- The Brevo rebrand does not affect API endpoints, key formats, or this integration — both `app.sendinblue.com` and `app.brevo.com` lead to the same dashboard.
