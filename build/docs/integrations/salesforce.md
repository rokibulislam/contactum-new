# Salesforce Integration

Automatically create a **Contact** or **Lead** record in Salesforce CRM when a visitor submits a Contactum form. Salesforce uses **OAuth 2.0** — you do not paste an API key directly. Instead, you create a Connected App in Salesforce and complete a one-time authorization flow.

---

## Requirements

- **Contactum Pro**
- An active **Salesforce account** (production or sandbox)
- A Salesforce **Connected App** with OAuth enabled
- Your site must be publicly accessible (Salesforce needs to redirect back to your WordPress admin)

---

## How Salesforce Authentication Works

Salesforce uses the **OAuth 2.0 Authorization Code** flow:

1. You create a Connected App in Salesforce and note its **Consumer Key** and **Consumer Secret**.
2. You enter those credentials plus your **Salesforce Domain URL** in Contactum and click **Authenticate with Salesforce**.
3. Contactum redirects your browser to Salesforce's login and authorization screen.
4. After you approve, Salesforce redirects back to your WordPress admin with a one-time authorization code.
5. Contactum automatically exchanges that code for an **access token** and a **refresh token**, then stores both.
6. From that point on, Contactum uses the access token for all API calls. When the token expires, Contactum silently refreshes it using the stored refresh token — you do not need to re-authorize.

---

## 1. Create a Connected App in Salesforce

A Connected App is how Salesforce identifies and authorizes external applications like Contactum.

1. Log in to your Salesforce account.
2. Go to **Setup** (gear icon, top-right) → search for **App Manager** in the Quick Find box.
3. Click **New Connected App**.
4. Fill in the **Basic Information** section:
   - **Connected App Name**: `Contactum` (or any name you prefer)
   - **API Name**: auto-filled
   - **Contact Email**: your email
5. Under **API (Enable OAuth Settings)**, check **Enable OAuth Settings**.
6. In the **Callback URL** field, enter your WordPress admin redirect URL in this exact format:

   ```
   https://yoursite.com/wp-admin/?contactum_salesforce_auth=1
   ```

   Replace `yoursite.com` with your actual domain. This URL must match exactly — Salesforce will reject any redirect that does not match.

7. Under **Selected OAuth Scopes**, add:
   - **Manage user data via APIs (api)** — required to create Contacts and Leads
   - **Perform requests at any time (refresh_token, offline_access)** — required for automatic token refresh
8. Click **Save**. Salesforce may take a few minutes to apply the settings.
9. After saving, open the Connected App and click **Manage Consumer Details** (or look for **Consumer Key** and **Consumer Secret** on the app page).
10. Copy both values — you will need them in Step 2.

> **Sandbox accounts:** If you are using a Salesforce sandbox (test environment), check the **Sandbox Account** checkbox in Contactum when entering your credentials. Sandbox accounts authenticate via `https://test.salesforce.com` instead of `https://login.salesforce.com`.

---

## 2. Connect Salesforce in Contactum

1. Go to **Contactum → Settings → Integrations → Salesforce**.
2. If using a sandbox account, check **Salesforce Sandbox Account**.
3. Enter your **Salesforce Domain URL** — this is your organization's Salesforce instance URL, for example:
   ```
   https://yourcompany.my.salesforce.com
   ```
   Find it in Salesforce under **Setup → My Domain**, or copy it from your browser address bar when logged in.
4. Enter the **Consumer Key** from your Connected App.
5. Enter the **Consumer Secret** from your Connected App.
6. Click **Authenticate with Salesforce**.

Contactum saves your credentials and redirects your browser to Salesforce's OAuth authorization page. Log in to Salesforce if prompted, then click **Allow** to grant Contactum access.

Salesforce redirects back to your WordPress admin, where Contactum automatically exchanges the authorization code for an access token and refresh token. A **"Your Salesforce integration is authenticated"** message appears and the status badge turns green.

**To disconnect**, click **Disconnect Salesforce**. This clears all stored credentials and tokens and stops all Salesforce record creation until you re-authenticate.

---

## 3. Token Expiry and Automatic Refresh

Salesforce access tokens expire periodically. Contactum handles this automatically:

- On every record creation attempt, if Salesforce returns a 401 Unauthorized response, Contactum sends the stored refresh token to obtain a new access token.
- The new access token is saved to `wp_options` immediately.
- If the refresh also fails (e.g. the refresh token was revoked via Salesforce's Connected App policy), record creation is skipped silently and you must re-authenticate by repeating Step 2.

---

## 4. Enable Salesforce on a Specific Form

The global OAuth connection does not create any records on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Salesforce** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 5. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Object Type** | Yes | Choose **Contact** or **Lead** — determines which Salesforce object is created on submission |
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the first name |
| **Last Name** | No | Map to a text or name field for the last name. Salesforce requires a Last Name — see note below |
| **Phone** | No | Map to a phone or text field for the phone number |
| **Company** | No* | Map to a text field for the company name. Required by Salesforce when Object Type is **Lead** |

> **Last Name is required by Salesforce.** If the Last Name field resolves to empty, Contactum uses the First Name value as the Last Name. If both are empty, the email address is used.

> **Company is required for Leads.** If Object Type is set to Lead and the Company field resolves to empty, Contactum sends `"Unknown"` as the Company value to satisfy Salesforce's validation.

### Contact vs Lead — Which Should You Use?

| Object | Use when |
|---|---|
| **Contact** | You want to create a standard CRM contact record. In Salesforce, Contacts are typically associated with an Account. Creating a Contact without an Account is valid but may generate warnings in some Salesforce configurations |
| **Lead** | You want to capture potential customers before they are qualified. Leads have their own pipeline in Salesforce and require a Company name. They can later be converted to Contacts and Accounts |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
Phone      →  {phone}
Company    →  {company}
```

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 6. How Record Creation Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Salesforce integration checks that:
   - The global credentials and tokens are stored and verified (`status: true`)
   - The form has Salesforce enabled
   - An **Email** mapping is configured
3. The email address is resolved from the mapped smart tag. If empty, the sync is skipped silently.
4. All other fields are resolved. Last Name falls back to First Name if empty; Company falls back to `"Unknown"` for Leads.
5. A `POST` request is sent to the Salesforce REST API:

**For a Contact:**
```
POST https://yourcompany.my.salesforce.com/services/data/v53.0/sobjects/Contact
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "FirstName": "Jane",
  "LastName": "Doe",
  "Email": "subscriber@example.com",
  "Phone": "+1 555 000 0000"
}
```

**For a Lead:**
```
POST https://yourcompany.my.salesforce.com/services/data/v53.0/sobjects/Lead
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "FirstName": "Jane",
  "LastName": "Doe",
  "Email": "subscriber@example.com",
  "Phone": "+1 555 000 0000",
  "Company": "Acme Corp"
}
```

6. Salesforce creates the record. If the access token has expired, Contactum automatically refreshes it and retries once.

> Record creation happens after the entry is saved. A Salesforce API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 7. Record Behaviour in Salesforce

| Scenario | Result |
|---|---|
| New email address | New Contact or Lead created in Salesforce |
| Existing email address | Salesforce may create a duplicate — no deduplication check is performed |
| Empty email from smart tag | Record creation skipped silently |
| Last Name empty | First Name value is used as Last Name |
| Lead with empty Company | `"Unknown"` is used as the Company value |
| Phone not mapped or empty | Record created without a phone field |
| Token expired, refresh succeeds | Token refreshed automatically; record created normally |
| Token expired, refresh fails | Record creation skipped silently; re-authentication required |

---

## 8. Troubleshooting

### Browser is not redirecting to Salesforce after clicking Authenticate

- Confirm the **Salesforce Domain URL** is correct (e.g. `https://yourcompany.my.salesforce.com`). Do not include a trailing slash.
- Confirm the **Consumer Key** and **Consumer Secret** were copied correctly from the Connected App.
- Confirm your WordPress site is publicly accessible — Salesforce cannot redirect back to a local or private URL.

### "redirect_uri_mismatch" error on the Salesforce authorization screen

- The Callback URL registered in your Salesforce Connected App does not match the one Contactum sends.
- The correct callback URL is: `https://yoursite.com/wp-admin/?contactum_salesforce_auth=1`
- Open your Connected App in Salesforce → **App Manager**, edit it, and update the Callback URL to match exactly.

### Status badge does not turn green after authorizing on Salesforce

- The authorization code Salesforce returns is processed during the redirect back to WordPress. If the WordPress admin was not reachable during the redirect (e.g. the admin URL changed), the code exchange fails.
- Try the authentication flow again. If it still fails, enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for errors during the callback.

### Records are not appearing in Salesforce after form submission

1. Confirm the form has Salesforce toggled **on** and was saved after enabling.
2. Confirm the **Email** field is mapped in the Configure dialog.
3. Submit a test entry and verify the field values appear in **Contactum → Entries**.
4. In Salesforce, go to **Contacts** or **Leads** and check for the record. Newly created records may take a moment to appear.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### "REQUIRED_FIELD_MISSING" error for Lead

- The `Company` field is required by Salesforce for Lead records. Map the Company field in the Configure dialog to a form field, or Contactum will use `"Unknown"` as the fallback value.

### Connection drops after some time

- Salesforce access tokens expire. Contactum refreshes them automatically using the refresh token. If the refresh token was revoked (e.g. you disconnected the Connected App in Salesforce or a session policy expired it), re-authentication is required. Repeat Step 2.

### Sandbox account error

- If you are using a Salesforce sandbox, confirm the **Sandbox Account** checkbox is checked in Contactum Settings. Sandbox and production accounts use different OAuth endpoints and tokens are not interchangeable.

---

## 9. Notes

- **OAuth 2.0 Authorization Code Flow:** Salesforce does not support static API keys for this integration. The Connected App OAuth flow is the only supported method.
- **API version:** Contactum uses Salesforce REST API v53.0 (`/services/data/v53.0/sobjects/`).
- **Auth method:** `Authorization: Bearer {access_token}` for all API calls. The OAuth token exchange uses form-encoded `POST` to `login.salesforce.com` (or `test.salesforce.com` for sandboxes).
- **Automatic token refresh:** On a 401 response, Contactum automatically sends the stored refresh token to obtain a new access token and persists it. The refresh uses `grant_type=refresh_token`.
- **No deduplication:** Each form submission creates a new record. Salesforce supports duplicate management rules — configure them in Salesforce under **Setup → Duplicate Management** to prevent or flag duplicates.
- **Salesforce field names:** Contactum uses Salesforce's standard API field names: `FirstName`, `LastName`, `Email`, `Phone`, `Company`. Custom fields are not currently mappable through the standard dialog.
- **Credentials storage:** `client_id`, `client_secret`, `instance_url`, `access_token`, `refresh_token`, `is_sandbox`, and `status` are stored in `wp_options` under the key `contactum_salesforce`. Restrict database access and keep WordPress authentication keys strong.
