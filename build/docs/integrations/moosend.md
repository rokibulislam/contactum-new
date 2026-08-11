# Moosend Integration

Automatically add form submitters as subscribers to a Moosend mailing list when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **Moosend account**
- A Moosend **API key**

---

## 1. Get Your Moosend API Key

1. Log in to your Moosend account at [app.moosend.com](https://app.moosend.com).
2. Click your account name in the bottom-left corner and go to **Settings**.
3. Open the **API key** tab.
4. Copy your API key. If none exists, click **Create** to generate one.

> The API key is a short alphanumeric string. It is passed as a query parameter on every API request — keep it private and do not share it publicly.

---

## 2. Connect Moosend in Contactum

1. Go to **Contactum → Settings → Integrations → Moosend**.
2. Paste your API key into the **API Key** field.
3. Click **Save Settings**.

Contactum immediately validates the key by calling the Moosend API (`GET /lists.json`). If valid, a **"Your MooSend API Key is valid"** confirmation is shown and the integration status turns green.

If the key is invalid, an error is returned from Moosend — double-check you copied the key exactly with no spaces.

**To disconnect**, click **Disconnect Moosend**. This clears the stored key and stops all Moosend subscriptions across every form until you reconnect.

---

## 3. Create a Mailing List in Moosend (if needed)

Contactum subscribes contacts to an existing Moosend **Mailing List**. If you do not have one yet:

1. In your Moosend account go to **Audience → Mailing Lists → Create new**.
2. Give the list a name and save it.
3. The list will appear in the Contactum integration dropdown after you refresh it.

---

## 4. Enable Moosend on a Specific Form

The global connection does not subscribe anyone on its own — you must enable and map the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Moosend** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 5. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The Moosend mailing list to subscribe the contact to. Click the refresh (↻) icon to load your lists from Moosend |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **Name** | No | Map to a text or name field for the subscriber's display name in Moosend |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button next to the input and pick a form field. The value is inserted as `{field_name}`. Example:

```
Email  →  {email}
Name   →  {your_name}
```

You can also type a smart tag manually if you know the field name.

### Refreshing Lists

If you added a new mailing list in Moosend after connecting, click the **refresh (↻)** button next to the List dropdown. Contactum fetches up to **999 mailing lists** from your account.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 6. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Moosend integration checks that:
   - The global API key is saved and verified (`status: true`)
   - The form has Moosend enabled
   - A **List** and an **Email** mapping are both set
3. Smart tags in the Email and Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST` request is sent to `https://api.moosend.com/v3/subscribers/{list_id}/subscribe.json` with the following payload:

```json
{
  "Email": "subscriber@example.com",
  "Name": "Jane Doe",
  "HasExternalDoubleOptIn": true
}
```

6. The subscriber is added to the selected mailing list.

> **Double opt-in is always enabled.** The `HasExternalDoubleOptIn` flag is hardcoded to `true`, which tells Moosend that the subscriber has already confirmed their opt-in on your side (via the form). Moosend will not send a separate confirmation email. Contacts are added directly to **Active** status.

> Subscription happens after the entry is saved. A Moosend API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 7. Subscriber Behaviour in Moosend

| Scenario | Result |
|---|---|
| New email address | Contact created and added to the mailing list as **Active** |
| Existing email in the same list | Contact's Name is updated; list membership unchanged |
| Existing email, unsubscribed | Moosend prevents re-subscription — the API call returns an error silently; form submission continues normally |
| Empty email from smart tag | Subscription skipped silently |
| API key invalid or revoked | Subscription skipped silently; form still processes normally |

---

## 8. Troubleshooting

### API key is not validating

- Confirm the API key is copied exactly from **Moosend → Settings → API key** with no spaces.
- Check that your Moosend account is active and not suspended.
- Moosend API keys are account-level — there are no per-list or restricted-scope keys.

### List dropdown is empty after clicking refresh

- Confirm you have at least one **Mailing List** created in Moosend → **Audience → Mailing Lists**.
- Make sure the global API key is saved and shows the green **"valid"** badge before refreshing the list — Contactum uses the saved key to fetch lists.

### Subscribers are not appearing in Moosend

1. Confirm the form has Moosend toggled **on** and was saved after enabling.
2. Confirm a **List** is selected — the integration silently skips if no list ID is set.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email on submission.
4. Submit a test entry and verify the email value appears in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for PHP errors during submission.

### Contact shows as unsubscribed immediately

This can happen if the email address previously unsubscribed from the selected list. Moosend enforces unsubscribe status and the API will not reactivate the contact. The contact must manually re-opt-in or be reactivated by an admin in the Moosend dashboard.

### Name field is not populated on the subscriber

The Name mapping uses a single combined name field — make sure the smart tag you mapped resolves to a non-empty value on submission. If your form uses separate first/last name fields, map the first name field to the **Name** input (e.g. `{first_name}`) since Moosend's subscriber record has a single `Name` property.

---

## 9. Notes

- Only one mailing list can be targeted per form. To subscribe a contact to multiple lists from a single submission, add the contact to additional lists using Moosend's automation rules triggered by a new subscriber event.
- The API key is stored in `wp_options` under the key `moosend`. Restrict database access and keep WordPress authentication keys strong.
- Moosend's API is versioned at `v3` (`https://api.moosend.com/v3/`). All requests pass the API key as a URL query parameter (`?apikey=…`).
