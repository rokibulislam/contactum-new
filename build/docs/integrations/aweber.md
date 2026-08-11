# AWeber Integration

Automatically add form submitters as subscribers to an AWeber list when they submit a Contactum form. AWeber uses **OAuth 2.0** — you do not paste an API key directly. Instead, you generate an authorization code inside AWeber and paste that into Contactum, which exchanges it for a long-lived access token automatically.

---

## Requirements

- **Contactum Pro**
- An active **AWeber account**
- An AWeber **authorization code** (generated during the OAuth flow below)

---

## How AWeber Authentication Works

Unlike integrations that use a static API key, AWeber issues short-lived **access tokens** via OAuth 2.0. The connection flow is:

1. You visit AWeber's authorization page and grant Contactum permission.
2. AWeber shows you a one-time **authorization code**.
3. You paste that code into Contactum and click **Save Settings**.
4. Contactum exchanges the code for an **access token** and a **refresh token**, then stores them.
5. From that point on, Contactum uses the access token automatically. When it expires, the refresh token is used silently to obtain a new one — you do not need to reconnect.

---

## 1. Generate an AWeber Authorization Code

1. Log in to your AWeber account at [app.aweber.com](https://app.aweber.com).
2. Go to **Account Settings → Integrations** (or navigate directly to the AWeber authorization URL for Contactum).
3. AWeber will ask you to grant the following permissions to Contactum:
   - `account.read` — read your account details
   - `list.read` — read your subscriber lists
   - `subscriber.read` / `subscriber.write` — read and add subscribers
   - `email.read` / `email.write` — access email campaign data
4. Click **Allow Access**.
5. AWeber displays a one-time **authorization code**. Copy it immediately — it expires quickly and can only be used once.

---

## 2. Connect AWeber in Contactum

1. Go to **Contactum → Settings → Integrations → AWeber**.
2. Paste the authorization code into the **AWeber Access Token** field.
3. Click **Save Settings**.

Contactum sends the code to AWeber's OAuth token endpoint (`POST /oauth2/token` with `grant_type=authorization_code`). On success:

- AWeber returns an `access_token`, a `refresh_token`, and an `expires_in` value.
- Contactum stores all three in `wp_options` and marks the connection as verified.
- A **"Your settings has been updated!"** message appears with the status badge turning green.

If the code has expired or was already used, AWeber returns an error. Generate a new authorization code from Step 1 and try again.

> The authorization code is a one-time credential — once exchanged, the original code is invalid. Contactum stores the resulting tokens, not the code itself.

**To disconnect**, click **Disconnect AWeber**. This clears the stored access token, refresh token, and authorization code, and stops all AWeber subscriptions until you reconnect.

---

## 3. Token Expiry and Automatic Refresh

AWeber access tokens expire after a set period. Contactum handles this automatically:

- Before every subscription attempt, the integration checks whether `expires_at` has passed.
- If the token is expired, Contactum sends the stored `refresh_token` to AWeber (`grant_type=refresh_token`) to obtain a fresh access token.
- The new token, refresh token, and expiry time are saved to `wp_options` immediately.
- If the refresh fails (e.g. the refresh token was revoked), the subscription is skipped silently and you must reconnect manually by repeating Steps 1 and 2.

---

## 4. Enable AWeber on a Specific Form

The global OAuth connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **AWeber** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 5. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List ID** | Yes | The ID of the AWeber subscriber list to add the contact to |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |
| **Double Opt-In** | No | When enabled, AWeber sends a confirmation email before activating the subscription |

### Finding Your AWeber List ID

1. Log in to AWeber and go to **Lists**.
2. Click the list you want to use.
3. The list ID is the number shown in the browser URL after `/lists/` — for example, in `https://app.aweber.com/lists/1234567/...`, the list ID is `1234567`.

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

### Name Handling

AWeber accepts a single combined name field. Contactum automatically joins the resolved First Name and Last Name values with a space:

```
name = "Jane" + " " + "Doe"  →  "Jane Doe"
```

If both First Name and Last Name resolve to empty, the email address is used as the subscriber name instead.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 6. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The AWeber integration checks that:
   - The global access token is stored and verified (`status: true`)
   - The token has not expired (or refreshes it if it has)
   - The form has AWeber enabled
   - A **List ID** and an **Email** mapping are both configured
3. The email address is resolved from the mapped smart tag and validated as a proper email format using `is_email()`. If the resolved value is not a valid email, the subscription is skipped.
4. First Name and Last Name are resolved and combined into a single `name` value.
5. Contactum fetches your AWeber account ID (cached for 24 hours) and sends a `POST` request to:

```
POST https://api.aweber.com/1.0/accounts/{account_id}/lists/{list_id}/subscribers
Authorization: Bearer {access_token}

ws.op    = create
email    = subscriber@example.com
name     = Jane Doe
```

If **Double Opt-In** is enabled, an additional field is included:

```
ad_tracking = double_optin
```

6. AWeber creates the subscriber on the specified list.

> Subscription happens after the entry is saved. An AWeber API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 7. Double Opt-In

When the **Double Opt-In** toggle is enabled on the form integration, AWeber sends a confirmation email to the subscriber before activating them. The subscriber must click the confirmation link to be added to the list.

| Mode | Behaviour |
|---|---|
| Double opt-in **off** | Subscriber is added to the list immediately as active |
| Double opt-in **on** | AWeber sends a confirmation email; subscriber is pending until they confirm |

Use double opt-in for compliance with email regulations (GDPR, CAN-SPAM) or to ensure higher list quality.

---

## 8. Subscriber Behaviour in AWeber

| Scenario | Result |
|---|---|
| New email address | Subscriber created and added to the list |
| Existing email, same list | AWeber updates the subscriber's name; does not create a duplicate |
| Existing email, previously unsubscribed | AWeber may block re-subscription depending on list settings |
| Invalid email format | Subscription skipped silently by Contactum before the API is called |
| Expired token, refresh succeeds | Token refreshed automatically; subscription proceeds normally |
| Expired token, refresh fails | Subscription skipped silently; manual reconnection required |

---

## 9. Troubleshooting

### "Invalid Credentials" on save

- Authorization codes expire within minutes and can only be used once. If you waited too long or already clicked Save once, generate a fresh authorization code from Step 1 and try again.
- Ensure you copied the entire authorization code with no extra spaces or line breaks.

### Status badge shows connected but subscribers are not appearing

1. Confirm the form has AWeber toggled **on** and was saved after enabling.
2. Confirm the **List ID** field contains the correct numeric ID from your AWeber list URL.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email address.
4. Submit a test entry and verify the email value appears in **Contactum → Entries**.
5. Check whether **Double Opt-In** is enabled — the subscriber may be pending confirmation in AWeber → **Subscribers → Pending**.

### Connection drops after some time

AWeber access tokens expire periodically. Contactum automatically refreshes them using the stored refresh token. If the connection drops unexpectedly, the refresh token itself may have been invalidated (e.g. you revoked access in AWeber). Reconnect by repeating Steps 1 and 2.

### "Could not retrieve AWeber account ID"

Contactum caches your AWeber account ID for 24 hours using a WordPress transient. If the transient was cleared (cache plugin, site migration), the next submission will re-fetch it automatically. If it fails repeatedly, check that the access token is still valid and that the `account.read` scope was granted during authorization.

### List ID is not accepting the value

The List ID must be the **numeric ID** from the AWeber list URL, not the list name. Example: if the URL is `app.aweber.com/lists/1234567/overview`, enter `1234567`.

---

## 10. Notes

- **OAuth 2.0 only:** AWeber does not support static API keys for this integration. The OAuth authorization code flow is the only supported authentication method.
- **One-time authorization code:** Each code generated in AWeber is single-use and short-lived. Do not reuse codes from previous setups.
- **Credentials storage:** `access_token`, `refresh_token`, `expires_at`, and `authorizeCode` are stored in `wp_options` under the key `aweber`. Restrict database access and keep WordPress authentication keys strong.
- **AWeber API version:** Contactum uses AWeber's REST API v1.0 (`https://api.aweber.com/1.0/`).
- **Account ID cache:** The AWeber account ID is cached in a WordPress transient (`contactum_aweber_account_id`) for 24 hours to avoid redundant API calls on every form submission.
