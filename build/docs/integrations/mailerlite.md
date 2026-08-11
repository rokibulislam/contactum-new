# MailerLite Integration

Automatically add form submitters to a MailerLite group when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **MailerLite account** (Classic or New — this integration uses the New API v3)
- A MailerLite **API token** with read/write access

---

## 1. Get Your MailerLite API Key

1. Log in to your MailerLite account.
2. Go to **Integrations → API** in the left sidebar.
3. Click **Generate new token** (or copy an existing one).
4. Give the token a name (e.g. `Contactum`) and ensure it has **Read** and **Write** permissions.
5. Copy the token — it is shown only once.

> MailerLite API tokens are long Bearer strings starting with `ey…`. Keep it private — anyone with the token can manage your subscribers.

---

## 2. Connect MailerLite in Contactum

1. Go to **Contactum → Settings → Integrations → MailerLite**.
2. Paste your API token into the **API Key** field.
3. Click **Save Settings**.

Contactum immediately calls the MailerLite API (`GET /me`) to verify the token. If valid, a **"Your MailerLite API Key is valid"** message appears and the integration status turns green.

If the key is invalid, an error message is shown — check that you copied the full token with no extra spaces.

**To disconnect**, click **Disconnect MailerLite**. This clears the stored key and disables all MailerLite subscriptions across every form until you reconnect.

---

## 3. Enable MailerLite on a Specific Form

The global connection does not subscribe anyone — you must enable and map the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **MailerLite** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Group** | Yes | The MailerLite group (audience segment) to add the subscriber to. Click the refresh icon to reload your groups from MailerLite |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |

### Using Smart Tags (Merge Tags)

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button next to the input and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

You can also type a smart tag manually if you know the field name.

### Refreshing Groups

If you created a new group in MailerLite after connecting, click the **refresh** (↻) button next to the Group dropdown. This fetches up to 100 groups live from your MailerLite account.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum processes the entry and fires the `contactum_entry_submission` action.
2. The MailerLite integration checks that:
   - The global API key is saved and verified (`status: true`)
   - The form has MailerLite enabled
   - A **Group** and **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST /subscribers` request is sent to the MailerLite API with the payload:

```json
{
  "email": "subscriber@example.com",
  "fields": {
    "name": "Jane",
    "last_name": "Doe"
  },
  "groups": [
    { "id": "group_id" }
  ]
}
```

6. The subscriber is added to the specified group. If the email already exists in MailerLite, the record is updated (name fields).

> Subscription happens after the form entry is saved. A MailerLite API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Subscriber Behaviour in MailerLite

| Scenario | Result |
|---|---|
| New email address | Subscriber created and added to the group |
| Existing email, same group | Subscriber's name fields are updated; group membership unchanged |
| Existing email, different group | Subscriber added to the new group in addition to existing groups |
| Empty email from smart tag | Subscription skipped silently |
| API key revoked or expired | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### API key is not validating

- Make sure you are using a **New MailerLite** API token (from `connect.mailerlite.com`), not a Classic MailerLite key (from `app.mailerlite.com`). The two use different APIs.
- Check that the token has not expired or been revoked in MailerLite → **Integrations → API**.
- Paste the key into a plain-text editor first to confirm there are no leading/trailing spaces before copying it into Contactum.

### Group dropdown is empty

- Click the **refresh** (↻) button — groups are loaded on demand, not on page load.
- Confirm you have at least one group created in MailerLite → **Subscribers → Groups**.
- If the API key was entered but not yet verified (amber badge), save the global settings first so the key is validated before groups are fetched.

### Subscribers are not appearing in MailerLite

1. Confirm the form has MailerLite toggled **on** and that the form was saved after enabling it.
2. Confirm a **Group** is selected in the Configure dialog — the integration silently skips if no group is set.
3. Confirm the **Email** field is mapped and the mapped form field actually collects an email address.
4. Submit a test entry and check the form entry in **Contactum → Entries** — verify the email value is present in the entry.
5. Check your server's `wp-content/debug.log` (with `WP_DEBUG_LOG` enabled) for any PHP errors during submission.

### Submitter already exists in MailerLite but is unsubscribed

MailerLite will not re-subscribe a contact that has previously unsubscribed. This is enforced by MailerLite's anti-spam policy and cannot be overridden via the API. The subscriber must re-opt-in through a MailerLite-native form or manually be moved to active status by an account admin.

---

## 8. Notes

- Only one MailerLite group can be selected per form. To add a subscriber to multiple groups from a single submission, duplicate the form's integration entry via the form settings if supported, or manage group assignment rules inside MailerLite itself.
- The integration uses MailerLite's **New API v3** endpoint (`https://connect.mailerlite.com/api`). Classic MailerLite accounts on the old API (`api.mailerlite.com`) are not supported.
- The API key is stored in `wp_options` under `contactum_mailerlite`. Restrict database access and use a strong WordPress authentication key.
