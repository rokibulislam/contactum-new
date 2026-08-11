# Drip Integration

Automatically add form submitters as subscribers to your Drip account when they submit a Contactum form. Optionally apply tags to subscribers at the time of creation.

---

## Requirements

- **Contactum Pro**
- An active **Drip account**
- A Drip **API Key** and your Drip **Account ID**

---

## Understanding Drip's Account Structure

Unlike traditional email marketing tools that organize contacts into separate lists, Drip uses a single subscriber database per account. All subscribers belong to the account — you organize and segment them using **tags** and **workflows** rather than separate lists. When Contactum adds a subscriber, it adds them to your Drip account directly, and you can apply tags at the same time to segment them automatically.

---

## 1. Get Your Drip API Key and Account ID

Both credentials are found in your Drip account settings.

**API Key:**

1. Log in to your Drip account at [app.getdrip.com](https://app.getdrip.com).
2. Click your account avatar or name in the top-right corner and select **User Settings** (this is your personal profile settings, not account settings).
3. Scroll down to the **API Token** section.
4. Copy the API Token — this is your **API Key**.

**Account ID:**

1. From the main Drip dashboard, click **Settings** (gear icon) in the left sidebar.
2. Go to **General Info**.
3. Your **Account ID** is the numeric value shown on this page (e.g. `1234567`).

> Keep your API Token private — it grants full access to your Drip account. The Account ID is not sensitive on its own, but both are required together.

---

## 2. Connect Drip in Contactum

1. Go to **Contactum → Settings → Integrations → Drip**.
2. Enter your **Drip API Key** in the API Key field.
3. Enter your **Drip Account ID** in the Account ID field (numeric only).
4. Click **Save Settings**.

Contactum validates the credentials by calling `GET /accounts` on the Drip v2 API using HTTP Basic Authentication. If the response contains your account data, the credentials are valid. A **"Your Drip API key has been verified and saved"** message appears and the status badge turns green.

If validation fails, the error message from Drip is shown — confirm both the API Key and Account ID are correct.

**To disconnect**, click **Disconnect Drip**. This clears both the API Key and Account ID and stops all Drip subscriptions until you reconnect.

---

## 3. Enable Drip on a Specific Form

The global connection does not add anyone to Drip on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Drip** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Email Address** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |
| **Tags** | No | Comma-separated list of tags to apply to the subscriber (e.g. `lead, newsletter, contact-form`) |

> **No list selection:** Drip does not use traditional mailing lists. There is no list dropdown — subscribers are added directly to your Drip account and organized via tags and workflows.

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email Address  →  {email}
First Name     →  {first_name}
Last Name      →  {last_name}
```

### Tags

Enter one or more tags in the **Tags** field as a comma-separated list. Tags are applied to the subscriber in Drip at the time they are created or updated. Example:

```
lead, newsletter, website-form
```

- Tags are trimmed of whitespace before being sent.
- Empty entries (from trailing commas or double commas) are ignored.
- If the Tags field is left blank, no tags are applied.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Drip integration checks that:
   - The global API Key and Account ID are saved and verified (`status: true`)
   - The form has Drip enabled
   - An **Email Address** mapping is configured
3. The email address is resolved from the mapped smart tag. If the resolved email is empty, the subscription is silently skipped.
4. First Name, Last Name, and Tags are resolved from their mapped fields.
5. If tags are configured, the comma-separated string is split into an array, each entry trimmed.
6. A `POST` request is sent to the Drip API:

```
POST https://api.getdrip.com/v2/{account_id}/subscribers
Authorization: Basic base64(apiKey)
Content-Type: application/json

{
  "subscribers": [
    {
      "email": "subscriber@example.com",
      "first_name": "Jane",
      "last_name": "Doe",
      "tags": ["lead", "newsletter"]
    }
  ]
}
```

7. Drip creates the subscriber or updates the existing record if the email already exists.

> Subscription happens after the entry is saved. A Drip API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Subscriber Behaviour in Drip

| Scenario | Result |
|---|---|
| New email address | Subscriber created in Drip with the provided fields and tags |
| Existing email address | Subscriber record updated (first/last name); tags are added (not replaced) |
| Empty email from smart tag | Subscription skipped silently |
| Tags field empty | Subscriber created/updated with no tags applied |
| Invalid or missing API Key | Subscription skipped silently; form still processes normally |
| Missing Account ID | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "Invalid credentials" error on save

- Confirm the **API Key** was copied from your Drip **User Settings → API Token** (personal settings, not account settings).
- Confirm the **Account ID** is the numeric ID from **Settings → General Info** — do not enter the account name or URL slug.
- Ensure neither field has leading or trailing spaces.

### Status badge shows connected but subscribers are not appearing in Drip

1. Confirm the form has Drip toggled **on** and was saved after enabling.
2. Confirm the **Email Address** field is mapped in the Configure dialog.
3. Confirm the mapped form field contains a valid email on submission — check **Contactum → Entries**.
4. Check Drip → **People** and search by the test email address. Drip may place new subscribers in a pending or unconfirmed state depending on your account settings.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### Tags are not appearing on the subscriber in Drip

- Confirm the Tags field in the Configure dialog is filled in with at least one tag.
- Tags must be plain text — do not use smart tags in the Tags field, enter tag names directly (e.g. `lead, newsletter`).
- In Drip, go to **People → Tags** to verify the tag exists. Drip creates tags on first use, so they will appear after the first successful subscription.

### "Drip API error (HTTP 401)" or "Unauthorized"

- The API Key has been revoked or regenerated. Get the current token from Drip → **User Settings → API Token** and update it in Contactum.

### "Drip API error (HTTP 404)"

- The Account ID does not match any account accessible with the provided API Key. Verify the Account ID in Drip → **Settings → General Info**.

---

## 8. Notes

- **No list selection:** Drip stores all subscribers in a single account-level database. Segmentation is done through tags and workflows, not separate lists.
- **API version:** Contactum uses Drip API v2 (`https://api.getdrip.com/v2/`).
- **Auth method:** HTTP Basic Authentication — the API Key is Base64-encoded as the username (no password component). `Authorization: Basic base64(apiKey)`.
- **Upsert behaviour:** Drip's subscriber endpoint creates a new subscriber or updates an existing one by email address. Submitting the same email twice will update the name fields and add any new tags.
- **Tags are additive:** When a subscriber already exists in Drip and is submitted again, tags are added to the subscriber's existing tag set — existing tags are not removed.
- **Credentials storage:** `apiKey` and `accountId` are stored in `wp_options` under the key `drip`. Restrict database access and keep WordPress authentication keys strong.
