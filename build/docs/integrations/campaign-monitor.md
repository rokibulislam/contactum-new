# Campaign Monitor Integration

Automatically add form submitters as subscribers to a Campaign Monitor list when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **Campaign Monitor account**
- A Campaign Monitor **API key**
- At least one **Client** configured in your Campaign Monitor account

---

## Understanding Campaign Monitor's Account Structure

Campaign Monitor uses a two-level hierarchy that is important to understand before connecting:

- **Account** — your top-level login. The API key belongs to this level.
- **Clients** — sub-accounts under your login. Each client has its own subscriber lists. Even if you are not an agency and only have one client, Campaign Monitor still requires you to select it.
- **Lists** — subscriber lists that belong to a client.

When connecting, you enter your API key first, then select which Client's lists Contactum should use. **Both are required** — the integration will not activate until a client is selected.

---

## 1. Get Your Campaign Monitor API Key

1. Log in to your Campaign Monitor account at [login.createsend.com](https://login.createsend.com).
2. Click your account name in the top-right corner and go to **Account Settings**.
3. Scroll down to the **API keys** section.
4. Click **Show API key** (or **Generate API key** if none exists).
5. Copy the key.

> Campaign Monitor API keys are account-level credentials. Keep the key private.

---

## 2. Connect Campaign Monitor in Contactum

Connection requires two steps: validating the API key, then selecting a client.

### Step A — Enter and verify the API key

1. Go to **Contactum → Settings → Integrations → Campaign Monitor**.
2. Enter your API key in the **Campaign Monitor API Key** field.
3. Click **Save Settings**.

Contactum validates the key by calling `GET /systemdate` on the Campaign Monitor API. If the key is valid, a **Client** dropdown appears immediately.

### Step B — Select a client

4. The **Campaign Monitor Client** dropdown is now visible. Select the client whose lists you want to use.
5. Click **Save Settings** again.

Once a client is selected and saved, the status badge turns green and the message **"Your Campaign Monitor API key has been verified and saved"** is shown.

> If you save without selecting a client, the API key is verified but the message reads **"API key verified. Please select a client to complete setup."** — the integration will not activate until you return and select a client.

**To disconnect**, click **Disconnect Campaign Monitor**. This clears the API key and client ID and stops all Campaign Monitor subscriptions until you reconnect.

---

## 3. Enable Campaign Monitor on a Specific Form

The global connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Campaign Monitor** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 (both A and B) first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The Campaign Monitor subscriber list to add the contact to. Click the refresh (↻) icon to load all lists for the selected client |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

### Name Handling

Campaign Monitor accepts a single combined **Name** field. Contactum automatically joins the First Name and Last Name values with a space:

```
"Jane" + " " + "Doe"  →  "Jane Doe"
```

If both First Name and Last Name resolve to empty, the email address is used as the subscriber name instead.

### Refreshing the List Dropdown

Lists are loaded from your selected Campaign Monitor client. If you added a new list after connecting, click the **refresh (↻)** button next to the List dropdown to reload from Campaign Monitor.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Campaign Monitor integration checks that:
   - The global API key and client are saved and verified (`status: true`)
   - The form has Campaign Monitor enabled
   - A **List** and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST` request is sent to the Campaign Monitor API:

```
POST https://api.createsend.com/api/v3.1/subscribers/{list_id}.json
Authorization: Basic base64(apiKey:x)
Content-Type: application/json

{
  "EmailAddress": "subscriber@example.com",
  "Name": "Jane Doe",
  "Resubscribe": true
}
```

6. Campaign Monitor adds the subscriber to the specified list.

> **Resubscribe is always enabled.** `"Resubscribe": true` is hardcoded in every request. If a contact previously unsubscribed from the list, they will be **resubscribed automatically** when they submit the form again. See the note in Section 7 if this behaviour is not desired.

> Subscription happens after the entry is saved. A Campaign Monitor API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Subscriber Behaviour in Campaign Monitor

| Scenario | Result |
|---|---|
| New email address | Subscriber created and added to the list |
| Existing email, same list | Subscriber's Name is updated; list membership unchanged |
| Existing email, previously unsubscribed | Subscriber is **resubscribed** to the list (Resubscribe: true) |
| Existing email, different list | Subscriber added to the new list; existing subscriptions unchanged |
| Empty email from smart tag | Subscription skipped silently |
| API key invalid or client not selected | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "API key verified. Please select a client to complete setup."

This means the API key is correct but no client has been selected. The integration is not active yet. Return to **Settings → Integrations → Campaign Monitor**, choose a client from the dropdown, and click **Save Settings** again.

### Client dropdown does not appear after saving the API key

- Confirm the API key was copied correctly with no spaces.
- Confirm your Campaign Monitor account has at least one client. In Campaign Monitor, go to **Clients** to verify.
- If the dropdown still does not appear, try clicking Save again — the client list is fetched live on save.

### List dropdown is empty after clicking refresh

- Confirm the selected client has at least one subscriber list. In Campaign Monitor go to **Lists & Subscribers** under the relevant client.
- If you only recently created a list, wait a moment and click the refresh button again.

### Subscribers are not appearing in Campaign Monitor

1. Confirm the form has Campaign Monitor toggled **on** and was saved after enabling.
2. Confirm a **List** is selected in the Configure dialog.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email address on submission.
4. Submit a test entry and verify the email value appears in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for exceptions thrown during submission.

### Previously unsubscribed contacts are being resubscribed

`Resubscribe: true` is always sent, which causes Campaign Monitor to reactivate contacts who previously unsubscribed. This is a current limitation of the integration — there is no per-form toggle to disable resubscription.

If you need to prevent resubscription of opted-out contacts, consider filtering submissions in Campaign Monitor using suppression lists or segment rules after import.

### "Unauthorized" error (HTTP 401)

- The API key may have been regenerated in Campaign Monitor. Get the new key from **Account Settings → API keys** and update it in Contactum.
- Campaign Monitor uses HTTP Basic Authentication with the format `apiKey:x` (the password field is always the literal character `x`). This is handled automatically — no changes are needed on your end.

---

## 8. Notes

- **Client selection is mandatory.** Campaign Monitor's API is structured around clients. Even with a single account, you must select a client for the integration to become active.
- **API authentication format.** Campaign Monitor uses HTTP Basic Auth where the API key is the username and `x` is the password: `Authorization: Basic base64(apiKey:x)`. This is Campaign Monitor's standard format — the password value is irrelevant.
- **API version.** Contactum uses Campaign Monitor API v3.1 (`https://api.createsend.com/api/v3.1/`). All endpoint URLs include a `.json` suffix (e.g. `subscribers/{list_id}.json`).
- **Credentials storage.** `apiKey` and `clientId` are stored in `wp_options` under the key `campaign_monitor`. Restrict database access and keep WordPress authentication keys strong.
- **Auto client fallback.** If the saved client ID is somehow cleared but the API key remains valid, Contactum will automatically use the first client returned by the API and save it. This prevents silent failures when the client ID is missing.
