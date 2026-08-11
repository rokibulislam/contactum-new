# Zoho CRM Integration

Automatically create or update a **Contact** or **Lead** record in Zoho CRM when a visitor submits a Contactum form. Zoho CRM uses **OAuth 2.0** — you do not paste an API key directly. Instead, you create a Connected App (Server-based OAuth Application) in the Zoho API Console and complete a one-time authorization flow.

---

## Requirements

- **Contactum Pro**
- An active **Zoho CRM account**
- A Zoho **Connected App** (Server-based OAuth Application) with Client ID and Client Secret
- Your site must be publicly accessible (Zoho needs to redirect back to your WordPress admin)

---

## How Zoho CRM Authentication Works

Zoho CRM uses the **OAuth 2.0 Authorization Code** flow with offline access:

1. You create a Connected App in the Zoho API Console and note its **Client ID** and **Client Secret**.
2. You select your **Data Center**, enter the credentials in Contactum, and click **Authenticate with Zoho CRM**.
3. Contactum redirects your browser to Zoho's authorization screen.
4. After you approve, Zoho redirects back to your WordPress admin with a one-time authorization code.
5. Contactum automatically exchanges the code for an **access token** and a **refresh token**, storing both along with the token expiry time.
6. Before every record creation, Contactum checks whether the access token is within 60 seconds of expiring. If so, it silently refreshes the token using the stored refresh token — you do not need to re-authorize.

---

## 1. Create a Connected App in Zoho API Console

1. Log in to the Zoho API Console at [api-console.zoho.com](https://api-console.zoho.com) (use the console for your data center — e.g. [api-console.zoho.eu](https://api-console.zoho.eu) for Europe).
2. Click **Add Client** and choose **Server-based Applications**.
3. Fill in the required details:
   - **Client Name**: `Contactum` (or any name you prefer)
   - **Homepage URL**: Your WordPress site URL
   - **Authorized Redirect URIs**: Enter your WordPress admin callback URL in this exact format:
     ```
     https://yoursite.com/wp-admin/?contactum_zohocrm_auth=1
     ```
     Replace `yoursite.com` with your actual domain. This URL must match exactly — Zoho will reject any redirect that does not match.
4. Click **Create**.
5. After creation, copy the **Client ID** and **Client Secret** from the application detail page.

> The Redirect URI must be registered exactly. Including or omitting a trailing slash can cause a mismatch error.

---

## 2. Connect Zoho CRM in Contactum

1. Go to **Contactum → Settings → Integrations → Zoho CRM**.
2. Select your **Data Center** from the dropdown. Choose the region where your Zoho account is hosted:

   | Option | Region |
   |---|---|
   | United States (zoho.com) | US-hosted accounts |
   | Europe (zoho.eu) | EU-hosted accounts |
   | India (zoho.in) | India-hosted accounts |
   | Australia (zoho.com.au) | Australia-hosted accounts |
   | Japan (zoho.jp) | Japan-hosted accounts |
   | China (zoho.com.cn) | China-hosted accounts |

   > If you are unsure which data center your account uses, check the URL you see when logged in to Zoho CRM. For example, `crm.zoho.eu` means your account is on the Europe data center.

3. Enter the **Client ID** from your Connected App.
4. Enter the **Client Secret** from your Connected App.
5. Click **Authenticate with Zoho CRM**.

Contactum saves your credentials and redirects your browser to Zoho's OAuth authorization page. Log in to Zoho if prompted, review the requested permissions, then click **Accept**.

Zoho redirects back to your WordPress admin, where Contactum exchanges the authorization code for access and refresh tokens. A **"Your Zoho CRM integration is authenticated"** message appears and the status badge turns green.

**OAuth Scopes requested:**
- `ZohoCRM.users.ALL` — read user account information
- `ZohoCRM.modules.ALL` — create and update records in CRM modules
- `ZohoCRM.settings.ALL` — read module and field settings

**To disconnect**, click **Disconnect Zoho CRM**. This clears all stored credentials and tokens and stops all Zoho CRM record creation until you re-authenticate.

---

## 3. Token Expiry and Automatic Refresh

Zoho CRM access tokens expire after approximately one hour. Contactum handles expiry proactively:

- Before every record creation, Contactum checks whether the stored `expire_at` timestamp is within 60 seconds of the current time.
- If the token is about to expire, Contactum immediately sends the stored refresh token to Zoho to obtain a fresh access token.
- The new token and its expiry time are saved to `wp_options` before the API call proceeds.
- If the refresh fails (e.g. the refresh token was revoked), record creation is skipped silently and you must re-authenticate by repeating Step 2.

---

## 4. Enable Zoho CRM on a Specific Form

The global OAuth connection does not create any records on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Zoho CRM** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 5. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Zoho Module** | Yes | Choose **Contact** or **Lead** — determines which Zoho CRM module the record is created in |
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the first name |
| **Last Name** | No | Map to a text or name field for the last name. Zoho CRM requires Last Name — see note below |
| **Phone** | No | Map to a phone or text field for the phone number |
| **Company** | No* | Map to a text field for the company name. Required by Zoho when **Zoho Module** is **Lead** |

> **Last Name is required by Zoho CRM.** If the Last Name field resolves to empty, Contactum uses the First Name value as the Last Name. If both are empty, the email address is used.

> **Company is required for Leads.** If Zoho Module is set to Lead and the Company field resolves to empty, Contactum sends `"Unknown"` as the Company value to satisfy Zoho's validation.

### Contact vs Lead — Which Should You Use?

| Module | Use when |
|---|---|
| **Contacts** | The person is a known customer or business contact. Contacts in Zoho CRM are typically associated with an Account |
| **Leads** | The person is a potential customer who has not yet been qualified. Leads require a Company name and can later be converted to Contacts and Accounts in Zoho |

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
2. The Zoho CRM integration checks that:
   - The global credentials and tokens are stored and verified (`status: true`)
   - The access token is refreshed if it is within 60 seconds of expiry
   - The form has Zoho CRM enabled
   - An **Email** mapping is configured
3. The email address is resolved. If empty, the sync is skipped silently.
4. All other fields are resolved. `Last_Name` falls back to `First_Name` if empty; `Company` falls back to `"Unknown"` for Leads.
5. A `POST` request is sent to the Zoho CRM API using the **upsert** endpoint:

**For a Contact:**
```
POST https://www.zohoapis.com/crm/v2/Contacts/upsert
Authorization: Zoho-oauthtoken {access_token}
Content-Type: application/json

{
  "data": [
    {
      "Email": "subscriber@example.com",
      "First_Name": "Jane",
      "Last_Name": "Doe",
      "Phone": "+1 555 000 0000"
    }
  ],
  "duplicate_check_fields": ["Email"]
}
```

**For a Lead:**
```
POST https://www.zohoapis.com/crm/v2/Leads/upsert
Authorization: Zoho-oauthtoken {access_token}
Content-Type: application/json

{
  "data": [
    {
      "Email": "subscriber@example.com",
      "First_Name": "Jane",
      "Last_Name": "Doe",
      "Phone": "+1 555 000 0000",
      "Company": "Acme Corp"
    }
  ],
  "duplicate_check_fields": ["Email"]
}
```

The `duplicate_check_fields: ["Email"]` parameter instructs Zoho CRM to **upsert** — if a record with the same email already exists in the module, it is updated rather than duplicated.

> The API URL is automatically built from your selected Data Center. For example, Europe uses `https://www.zohoapis.eu/crm/v2/`.

6. Zoho CRM creates or updates the record in the selected module.

> Record creation happens after the entry is saved. A Zoho CRM API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 7. Record Behaviour in Zoho CRM

| Scenario | Result |
|---|---|
| New email address | New Contact or Lead created in Zoho CRM |
| Existing email, same module | Existing record updated with new field values |
| Existing email, different module | A new record is created in the other module — the existing Contact is not affected when creating a Lead and vice versa |
| Empty email from smart tag | Record creation skipped silently |
| Last Name empty | First Name value is used as Last Name |
| Lead with empty Company | `"Unknown"` is sent as the Company value |
| Phone not mapped or empty | Record created without a phone field |
| Token within 60s of expiry | Token refreshed automatically before the API call |
| Refresh token revoked | Record creation skipped silently; re-authentication required |

---

## 8. Troubleshooting

### Browser is not redirecting to Zoho after clicking Authenticate

- Confirm the **Data Center** selected in Contactum matches the region of your Zoho account.
- Confirm the **Client ID** and **Client Secret** were copied correctly from the Zoho API Console.
- Confirm your WordPress site is publicly accessible — Zoho cannot redirect back to a local or private URL.

### "redirect_uri_mismatch" or "Invalid redirect_uri" error on the Zoho authorization screen

- The Redirect URI registered in your Zoho Connected App does not match the one Contactum sends.
- The correct callback URL is: `https://yoursite.com/wp-admin/?contactum_zohocrm_auth=1`
- Edit your Connected App in the Zoho API Console and update the Authorized Redirect URI to match exactly, including the `?contactum_zohocrm_auth=1` query string.

### Status badge does not turn green after authorizing on Zoho

- If the WordPress admin was not reachable during the redirect (e.g. the site was behind maintenance mode), the code exchange fails.
- Try the authentication flow again. If it still fails, enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for errors during the callback.

### Records are not appearing in Zoho CRM

1. Confirm the form has Zoho CRM toggled **on** and was saved after enabling.
2. Confirm the **Email** field is mapped in the Configure dialog.
3. Submit a test entry and verify the field values appear in **Contactum → Entries**.
4. In Zoho CRM, go to **Contacts** or **Leads** and look for the record. Use the search or filter by creation date.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors.

### "MANDATORY_NOT_FOUND" error

- The `Last_Name` field is mandatory in Zoho CRM. If neither First Name nor Last Name resolves to a non-empty value, this error can occur. Map the Last Name field or ensure the First Name field contains a value.
- For Leads, `Company` is also mandatory. Map the Company field or accept the `"Unknown"` fallback.

### Connection drops unexpectedly

- Zoho access tokens expire after approximately one hour. Contactum refreshes them automatically. If the refresh token was revoked (e.g. you deleted the Connected App or revoked access from your Zoho account settings), re-authentication is required. Repeat Step 2.
- You can revoke connected apps in Zoho under **My Profile → Connected Apps**.

### Wrong data center selected

- If you authenticate against the wrong data center, Zoho will return authentication errors or the API calls will fail with 401. Disconnect, select the correct data center, and re-authenticate.

---

## 9. Notes

- **OAuth 2.0 Authorization Code Flow:** Zoho does not support static API keys for this integration. The Connected App OAuth flow is the only supported method.
- **API version:** Contactum uses Zoho CRM REST API v2 (`/crm/v2/`).
- **Auth header:** `Authorization: Zoho-oauthtoken {access_token}` — Zoho's own header format, not standard Bearer.
- **Data center routing:** The CRM API base URL is derived from your selected data center's TLD. For example, `zoho.eu` → `https://www.zohoapis.eu/crm/v2/`. Australia and China use `zohoapis.com.au` and `zohoapis.com.cn` respectively.
- **Upsert by email:** The `duplicate_check_fields: ["Email"]` parameter in every record creation request tells Zoho to update an existing record with the same email rather than create a duplicate. This is different from Salesforce and Pipedrive, which create new records on every submission.
- **Proactive token refresh:** Contactum checks the token expiry time before every API call and refreshes it if it expires within the next 60 seconds, avoiding mid-request failures.
- **Zoho field names:** Contactum uses Zoho's standard API field names: `Email`, `First_Name`, `Last_Name`, `Phone`, `Company`. Note the underscore in `First_Name` and `Last_Name`.
- **Credentials storage:** `accountUrl`, `client_id`, `client_secret`, `access_token`, `refresh_token`, `expire_at`, and `status` are stored in `wp_options` under the key `contactum_zoho`. Restrict database access and keep WordPress authentication keys strong.
