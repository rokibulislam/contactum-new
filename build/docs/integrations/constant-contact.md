# Constant Contact Integration

Automatically add form submitters as contacts to a Constant Contact mailing list when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **Constant Contact account**
- A Constant Contact **API access token**

---

## 1. Get Your Constant Contact Access Token

Constant Contact uses OAuth 2.0 under the hood, but for direct API access you work with a long-lived **access token** rather than managing the full OAuth flow yourself. Tokens are obtained through the Constant Contact developer portal.

**Steps:**

1. Go to the [Constant Contact developer portal](https://app.constantcontact.com/pages/dma/portal/) and sign in with your Constant Contact account.
2. Navigate to **My Applications** and click **New Application** (or open an existing one).
3. Fill in the application name (e.g. `Contactum`) and any required fields, then save.
4. In the application detail page, locate the **API Key** (also called Client ID) and note it.
5. Click **Generate Access Token** (or use the **Token Flow** link provided). Complete the short OAuth authorization screen.
6. Copy the resulting **Access Token**.

> Access tokens do not expire by default for Constant Contact v2 personal apps. Keep the token private — anyone with it can read and write your contacts and lists.

---

## 2. Connect Constant Contact in Contactum

1. Go to **Contactum → Settings → Integrations → Constant Contact**.
2. Paste your access token into the **Access Token** field.
3. Click **Save Settings**.

Contactum validates the token by calling `GET /account/info` on the Constant Contact v2 API. If the request succeeds, a **"Your Constant Contact access token has been verified and saved"** message appears and the status badge turns green.

If the token is invalid, the exact error message from Constant Contact is shown — check that you copied the full token with no leading or trailing spaces.

**To disconnect**, click **Disconnect Constant Contact**. This clears the stored token and disables all Constant Contact subscriptions across every form until you reconnect.

---

## 3. Enable Constant Contact on a Specific Form

The global connection does not subscribe anyone on its own — you must enable and map the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Constant Contact** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Mailing List** | Yes | The Constant Contact list to subscribe the contact to. Click the refresh (↻) icon to load all lists from your account |
| **Email Address** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email Address  →  {email}
First Name     →  {first_name}
Last Name      →  {last_name}
```

### Refreshing the List Dropdown

If you created a new list in Constant Contact after connecting, click the **refresh (↻)** button next to the Mailing List dropdown. Contactum calls `GET /lists` to fetch all lists from your account.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Constant Contact integration checks that:
   - The global access token is saved and verified (`status: true`)
   - The form has Constant Contact enabled
   - A **Mailing List** and an **Email Address** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST` request is sent to the Constant Contact API:

```
POST https://api.constantcontact.com/v2/contacts?action_by=ACTION_BY_OWNER
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "email_addresses": [
    { "email_address": "subscriber@example.com" }
  ],
  "first_name": "Jane",
  "last_name": "Doe",
  "lists": [
    { "id": "list_id" }
  ]
}
```

6. Constant Contact creates the contact and adds them to the specified list.

> Subscription happens after the entry is saved. A Constant Contact API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. The `action_by` Parameter

Every contact creation request includes `?action_by=ACTION_BY_OWNER`. This tells Constant Contact to attribute the opt-in to the account owner (you), rather than the contact themselves.

**Practical effects:**

| `action_by` value | Behaviour |
|---|---|
| `ACTION_BY_OWNER` (default) | Contact is added immediately without receiving a confirmation email from Constant Contact |
| `ACTION_BY_CONTACT` | Constant Contact sends a confirmation email; contact is only activated after they click the link |

If you need Constant Contact to send its own confirmation email (double opt-in via Constant Contact), you can change this value using a WordPress filter in your theme's `functions.php`:

```php
add_filter( 'contactum/constantcontact_action_by', function() {
    return 'ACTION_BY_CONTACT';
} );
```

---

## 7. Contact Behaviour in Constant Contact

| Scenario | Result |
|---|---|
| New email address | Contact created and added to the list |
| Existing email, same list | Contact's first/last name updated; list membership unchanged |
| Existing email, different list | Contact added to the new list; existing memberships unchanged |
| Existing email, previously unsubscribed | Constant Contact may reject the request depending on unsubscribe type (global unsubscribe cannot be reversed via API) |
| Empty email from smart tag | Subscription skipped silently |
| Invalid or expired access token | Subscription skipped silently; form still processes normally |

---

## 8. Troubleshooting

### Access token is not validating

- Confirm you copied the full access token from the Constant Contact developer portal with no spaces or line breaks.
- Confirm the token was generated from an active application that has not been deleted or revoked.
- If the token was generated a long time ago and you are unsure of its validity, generate a fresh token from your application's detail page.

### List dropdown is empty after clicking refresh

- Confirm you have at least one mailing list in Constant Contact → **Contacts → Lists**.
- The global access token must be saved and show the green valid badge before lists can be fetched.

### Subscribers are not appearing in Constant Contact

1. Confirm the form has Constant Contact toggled **on** and was saved after enabling.
2. Confirm a **Mailing List** is selected in the Configure dialog.
3. Confirm the **Email Address** field is mapped and the mapped form field contains a valid email address on submission.
4. Submit a test entry and verify the email value appears in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for exceptions during submission.

### "Contact cannot be re-added" or global unsubscribe error

Constant Contact distinguishes between list-level unsubscribes and **global unsubscribes**. A contact who clicked "Unsubscribe from all" in a previous email is globally suppressed — the API cannot re-add them to any list. This is enforced by Constant Contact and cannot be overridden from Contactum. The contact must manually re-opt-in through Constant Contact's own tools.

### Using your own Constant Contact OAuth application

By default, Contactum uses a shared application key. If your site is registered with its own Constant Contact OAuth app, define the following constant in `wp-config.php`:

```php
define( 'CONTACTUM_CC_APP_KEY', 'your-app-api-key' );
```

When defined, Contactum appends `?api_key={your-app-api-key}` to every API request.

---

## 9. Notes

- **API version:** Contactum uses Constant Contact API v2 (`https://api.constantcontact.com/v2/`).
- **Auth method:** Bearer token in the `Authorization` header. No OAuth code exchange is required — you paste the access token directly.
- **`action_by` default:** All subscriptions use `ACTION_BY_OWNER` by default, which adds contacts immediately without a Constant Contact confirmation email. Use the `contactum/constantcontact_action_by` filter to switch to `ACTION_BY_CONTACT` for double opt-in.
- **Credentials storage:** `accessToken` is stored in `wp_options` under the key `constantcontact`. Restrict database access and keep WordPress authentication keys strong.
- **Error format:** Constant Contact returns errors as an array — `[{"error_key": "...", "error_message": "..."}]`. The first error message is surfaced in the Contactum admin notice when saving fails.
