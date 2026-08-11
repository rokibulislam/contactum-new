# ActiveCampaign Integration

Automatically add form submitters as contacts to an ActiveCampaign list when they submit a Contactum form. Contactum uses ActiveCampaign's **legacy v1 API**, which is available on all plan types including Lite.

---

## Requirements

- **Contactum Pro**
- An active **ActiveCampaign account**
- Your ActiveCampaign **API URL** and **API Key** (both are required)

---

## 1. Find Your API URL and API Key

ActiveCampaign requires two credentials — an account-specific URL and an API key. Both are found in the same place.

1. Log in to your ActiveCampaign account.
2. Go to **Settings** (gear icon, bottom-left) → **Developer**.
3. Under the **API Access** section you will find:
   - **API URL** — your account endpoint, in the format `https://YOURACCOUNTNAME.api-us1.com`
   - **API Key** — a long alphanumeric string

4. Copy both values. Keep the API key private — anyone with the key and URL can access and modify your contacts.

> Do not add a trailing slash to the API URL. Contactum strips it automatically, but entering it cleanly avoids confusion.

---

## 2. Connect ActiveCampaign in Contactum

1. Go to **Contactum → Settings → Integrations → ActiveCampaign**.
2. Enter your **API URL** in the first field (e.g. `https://myaccount.api-us1.com`).
3. Enter your **API Key** in the second field.
4. Click **Save Settings**.

Contactum validates both credentials by calling `list_paginator` on your API URL. Validation checks two things:

- The URL responds with JSON — if not, the URL is invalid.
- The `result_code` in the response is `1` — if it is `0`, the API key is invalid.

If both pass, a **"Your ActiveCampaign configuration is valid"** message appears and the status badge turns green.

**To disconnect**, click **Disconnect ActiveCampaign**. This clears both the API URL and key and disables all ActiveCampaign contact syncing until you reconnect.

---

## 3. Enable ActiveCampaign on a Specific Form

The global connection does not add anyone to a list on its own — you must enable and map the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **ActiveCampaign** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The ActiveCampaign list to subscribe the contact to. Click the refresh (↻) icon to load all lists from your account |
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the contact's first name |
| **Last Name** | No | Map to a text or name field for the contact's last name |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button next to the input and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

You can also type a smart tag manually if you know the field name.

### Refreshing Lists

If you created a new list in ActiveCampaign after connecting, click the **refresh (↻)** button next to the List dropdown. Contactum calls `list_list` on your API and returns all available lists.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Contact Syncing Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The ActiveCampaign integration checks that:
   - The global API URL and API key are saved and verified (`status: true`)
   - The form has ActiveCampaign enabled
   - A **List** and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the sync is silently skipped.
5. A `contact_sync` request is sent to your ActiveCampaign API with the following data:

```
email       = subscriber@example.com
first_name  = Jane
last_name   = Doe
p[42]       = 42
```

`p[{list_id}]` is the ActiveCampaign legacy API syntax for subscribing a contact to a list. The value equals the list ID.

6. `contact_sync` performs an **upsert** — if the email already exists, the contact record is updated and they are added to the list. If the email is new, a contact record is created.

> Contact syncing happens after the entry is saved. An ActiveCampaign API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Contact Behaviour in ActiveCampaign

| Scenario | Result |
|---|---|
| New email address | Contact created and subscribed to the list |
| Existing email, same list | Contact's first/last name updated; list subscription unchanged |
| Existing email, different list | Contact added to the new list; existing subscriptions unchanged |
| Existing email, unsubscribed from list | ActiveCampaign may block re-subscription depending on your unsubscribe settings |
| Empty email from smart tag | Sync skipped silently |
| API URL or key invalid | Sync skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "Invalid API URL" error on save

- Confirm the URL follows the exact format: `https://YOURACCOUNTNAME.api-us1.com`
- Do not include `/admin/api.php` or any path — only the base account URL.
- Check that your server can reach the ActiveCampaign API (some hosting environments block outbound requests).

### "Invalid API Key" error on save

- Copy the API key directly from ActiveCampaign → **Settings → Developer** with no spaces.
- The API key is tied to your user account. If you recently regenerated it, update it in Contactum as well.

### List dropdown is empty after clicking refresh

- Confirm the global connection shows the green valid badge before refreshing — Contactum uses the saved credentials to fetch lists.
- Confirm you have at least one list in ActiveCampaign → **Lists**.

### Contacts are not appearing in ActiveCampaign

1. Confirm the form has ActiveCampaign toggled **on** and was saved after enabling.
2. Confirm a **List** is selected in the Configure dialog — the integration silently skips if no list ID is set.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email address on submission.
4. Submit a test entry and check the email value in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for PHP errors during submission.

### Contact is created but not subscribed to the list

- The list subscription uses `p[{list_id}] = list_id`. Verify the correct list is selected in the form's Configure dialog — the list ID must match an existing ActiveCampaign list.
- If the contact previously unsubscribed from the list, ActiveCampaign's unsubscribe rules may prevent re-subscription. Check the contact record in ActiveCampaign → **Contacts** to see their subscription status for that list.

---

## 8. Notes

- **API version:** Contactum uses ActiveCampaign's **legacy v1 API** (`/admin/api.php?api_action=…`), available on all plans including Lite. The newer v3 REST API is not used.
- **API URL format:** The URL is account-specific and always follows the pattern `https://ACCOUNTNAME.api-us1.com`. EU-hosted accounts may use a different subdomain — copy the exact URL from your Developer settings.
- **Credentials storage:** Both `apiKey` and `apiUrl` are stored in `wp_options` under the key `activecampaign`. Restrict database access and keep WordPress authentication keys strong.
- **`overwrite` flag:** When editing an existing contact, Contactum sends `overwrite=0`, which means existing field values in ActiveCampaign are **not** overwritten — only empty fields are filled in. This prevents submitted form data from accidentally clearing data already in ActiveCampaign.
