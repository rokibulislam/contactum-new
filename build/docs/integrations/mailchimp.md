# Mailchimp Integration

Automatically add form submitters as subscribers to a Mailchimp audience (list) when they submit a Contactum form. Mailchimp is included in the **free Contactum plugin** — no Pro license is required.

---

## Requirements

- **Contactum** (free version — no Pro required)
- An active **Mailchimp account**
- A Mailchimp **API key**

---

## 1. Get Your Mailchimp API Key

1. Log in to your Mailchimp account at [login.mailchimp.com](https://login.mailchimp.com).
2. Click your account avatar or name in the bottom-left corner and select **Profile**.
3. Go to the **Extras** tab and select **API keys**.
4. Click **Create A Key**.
5. Give the key a label (e.g. `Contactum`) and copy the generated key.

> Mailchimp API keys follow the format `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-us1` — the part after the dash is your **datacenter** identifier (e.g. `us1`, `us6`, `eu1`). Contactum reads this automatically from the key to determine the correct API endpoint. Do not remove or alter the datacenter suffix.

> Keep the API key private — it grants full read/write access to your Mailchimp account.

---

## 2. Connect Mailchimp in Contactum

1. Go to **Contactum → Settings → Integrations → Mailchimp**.
2. Paste your API key into the **API Key** field.
3. Click **Save Settings**.

Contactum validates the key by calling `GET /lists` on the Mailchimp API v3. If the request returns your audiences, the key is valid. A **"Your mailchimp api key has been verified and successfully set"** message appears and the status badge turns green.

If the key is invalid, the error returned by Mailchimp is shown — confirm the full key including the datacenter suffix was copied correctly.

**To disconnect**, click **Disconnect Mailchimp**. This clears the stored API key and disables all Mailchimp subscriptions until you reconnect.

---

## 3. Enable Mailchimp on a Specific Form

The global connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Mailchimp** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The Mailchimp audience to subscribe the contact to. Click the refresh (↻) icon to load all audiences from your account |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |
| **Double Opt-In** | No | When enabled, Mailchimp sends a confirmation email before activating the subscription |

> **Mailchimp "Audiences" vs "Lists":** Mailchimp renamed lists to "Audiences" in 2019. The Contactum UI uses the term **List** — this refers to your Mailchimp audience.

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

### Mailchimp Merge Fields

Contactum maps form values to the following built-in Mailchimp merge fields:

| Contactum Field | Mailchimp Merge Tag |
|---|---|
| First Name | `FNAME` |
| Last Name | `LNAME` |

These are Mailchimp's default merge tags present in every audience. If you have renamed them in your Mailchimp audience settings, contact syncing will still work — Mailchimp uses the merge tag key (`FNAME`, `LNAME`), not the display label.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Mailchimp integration checks that:
   - The global API key is saved and verified (`status: true`)
   - The form has Mailchimp enabled
   - A **List** and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST` request is sent to the Mailchimp API:

```
POST https://{dc}.api.mailchimp.com/3.0/lists/{list_id}/members
Authorization: Basic {apiKey}
Content-Type: application/json

{
  "email_address": "subscriber@example.com",
  "status": "subscribed",
  "merge_fields": {
    "FNAME": "Jane",
    "LNAME": "Doe"
  }
}
```

The `{dc}` in the URL is automatically extracted from your API key (e.g. `us1`, `eu1`).

6. Mailchimp adds the subscriber to the specified audience.

> Subscription happens after the entry is saved. A Mailchimp API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Subscriber Behaviour in Mailchimp

| Scenario | Result |
|---|---|
| New email address | Subscriber created and added to the audience as `subscribed` |
| Existing email, already subscribed | Mailchimp updates the merge fields (FNAME, LNAME); subscription status unchanged |
| Existing email, previously unsubscribed | Mailchimp returns a 400 error — unsubscribed contacts cannot be re-subscribed via the API. The form still submits normally |
| Existing email, archived | Mailchimp may reject the request. Unarchive the contact in Mailchimp and resubmit |
| Empty email from smart tag | Subscription skipped silently |
| Invalid or expired API key | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "Your Mailchimp API Key is not valid" error on save

- Confirm the API key was copied from Mailchimp → **Profile → Extras → API keys** in full, including the datacenter suffix (e.g. `-us1`).
- If the key was deleted or deactivated in Mailchimp, generate a new one and update it in Contactum.

### List dropdown is empty after clicking refresh

- Confirm the global API key shows the green valid badge before clicking refresh.
- Log in to Mailchimp and confirm at least one audience exists under **Audience → All contacts**. Create an audience if none exist, then click refresh.

### Subscribers are not appearing in Mailchimp

1. Confirm the form has Mailchimp toggled **on** and was saved after enabling.
2. Confirm a **List** is selected and the **Email** field is mapped in the Configure dialog.
3. Submit a test entry and verify the email value appears in **Contactum → Entries**.
4. Check **Mailchimp → Audience → All contacts** and search for the test email address.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### "Member In Compliance State" error

The email address has been marked as non-subscribed in Mailchimp due to a hard bounce, spam complaint, or unsubscribe. Mailchimp enforces this at the account level — the API cannot add a contact in compliance state. The subscriber must opt in again through Mailchimp's own re-subscribe process.

### "fake or invalid" or "looks fake or invalid" error

Mailchimp's abuse-detection system rejected the email address. This can happen with role-based addresses (e.g. `info@`, `admin@`) or addresses that have a poor sending reputation. Use a standard personal or business email address.

### API endpoint or datacenter mismatch

Mailchimp API keys contain the datacenter suffix (e.g. `-us1`). Contactum extracts this automatically and builds the endpoint as `https://us1.api.mailchimp.com/3.0/`. If the key is entered without the suffix or with the wrong suffix, all requests will fail with a 404 or connection error.

---

## 8. Notes

- **Included in free Contactum:** The Mailchimp integration is built into the core Contactum plugin. A Pro license is not required.
- **API version:** Contactum uses Mailchimp Marketing API v3 (`https://{dc}.api.mailchimp.com/3.0/`).
- **Auth method:** `Authorization: Basic {apiKey}` — Mailchimp accepts the API key directly in the Basic auth header.
- **Datacenter routing:** The datacenter is embedded in every Mailchimp API key after the final `-`. Contactum parses it automatically and uses it to build the account-specific API endpoint.
- **Mailchimp merge field keys:** First Name maps to `FNAME` and Last Name maps to `LNAME`. These are Mailchimp's default merge tags and are present in every audience unless manually deleted.
- **Subscription status:** Subscribers are added with `status: "subscribed"`. Mailchimp adds them directly to the audience without a confirmation email from Mailchimp's side.
- **Credentials storage:** `apiKey` and `status` are stored in `wp_options` under the key `contactum_mailchimp`. Restrict database access and keep WordPress authentication keys strong.
