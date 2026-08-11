# SendFox Integration

Automatically add form submitters as subscribers to a SendFox list when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **SendFox account**
- A SendFox **Personal Access Token**

---

## 1. Get Your SendFox Personal Access Token

SendFox uses a **Personal Access Token** for API authentication — not an API key by that name. Both terms refer to the same credential.

1. Log in to your SendFox account at [sendfox.com](https://sendfox.com).
2. Click your account name or avatar in the top-right corner and go to **Account Settings**.
3. Select the **Integrations** tab (or navigate to **Account → Integrations**).
4. Scroll to the **Personal Access Token** section.
5. Click **Generate Token** if none exists, then copy the token.

> Keep your Personal Access Token private — it grants full read/write access to your SendFox account including contacts and lists.

---

## 2. Connect SendFox in Contactum

1. Go to **Contactum → Settings → Integrations → SendFox**.
2. Paste the Personal Access Token into the **API Key** field.
3. Click **Save Settings**.

Contactum validates the token by calling `GET /me` on the SendFox API. If the request returns your account data without an error, the token is confirmed. A **"Your SendFox API key has been verified and saved"** message appears and the status badge turns green.

If the token is invalid, the error message returned by SendFox is shown.

**To disconnect**, click **Disconnect SendFox**. This clears the stored token and stops all SendFox subscriptions until you reconnect.

---

## 3. Enable SendFox on a Specific Form

The global connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **SendFox** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The SendFox list to subscribe the contact to. Click the refresh (↻) icon to load all lists from your account |
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

### Refreshing the List Dropdown

If you created a new list in SendFox after connecting, click the **refresh (↻)** button next to the List dropdown. Contactum fetches all lists from your account, automatically paginating through multiple pages if you have a large number of lists.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The SendFox integration checks that:
   - The global Personal Access Token is saved and verified (`status: true`)
   - The form has SendFox enabled
   - A **List** and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST` request is sent to the SendFox API:

```
POST https://api.sendfox.com/contacts
Authorization: Bearer {personalAccessToken}
Content-Type: application/json

{
  "email": "subscriber@example.com",
  "first_name": "Jane",
  "last_name": "Doe",
  "lists": [42]
}
```

The `lists` field is an array containing the selected list's numeric ID.

6. SendFox creates the subscriber and adds them to the specified list.

> Subscription happens after the entry is saved. A SendFox API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Subscriber Behaviour in SendFox

| Scenario | Result |
|---|---|
| New email address | Subscriber created and added to the list |
| Existing email address | SendFox updates the subscriber's name fields and adds them to the list if not already subscribed |
| Existing email, already in list | SendFox accepts the request without creating a duplicate |
| Existing email, unsubscribed | SendFox may block re-subscription depending on account settings |
| Empty email from smart tag | Subscription skipped silently |
| Invalid or expired token | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "Your SendFox API key is not valid" error on save

- Confirm you copied the **Personal Access Token** from SendFox → **Account → Integrations → Personal Access Token** with no extra spaces.
- If the token was regenerated in SendFox, the previous token is invalid. Copy the new token and update it in Contactum.

### List dropdown is empty after clicking refresh

- Confirm the global token shows the green valid badge before clicking refresh.
- Log in to SendFox and confirm at least one list exists under **Lists**. Create a list if none exist, then click refresh.

### Subscribers are not appearing in SendFox

1. Confirm the form has SendFox toggled **on** and was saved after enabling.
2. Confirm a **List** is selected and the **Email** field is mapped in the Configure dialog.
3. Submit a test entry and verify the email value appears in **Contactum → Entries**.
4. Log in to SendFox and go to **Contacts** to search for the test email address.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### Subscriber is added but not appearing in the selected list

- Confirm the correct list is selected in the Configure dialog. If you recently deleted and recreated a list in SendFox, its numeric ID changes — click refresh and reselect the list.
- In SendFox, check **Lists** and open the specific list to see if the subscriber appears there.

---

## 8. Notes

- **API terminology:** SendFox labels this credential a **Personal Access Token** in their dashboard, but the Contactum UI field is labelled **API Key**. Both refer to the same token.
- **API base URL:** `https://api.sendfox.com/`
- **Auth method:** `Authorization: Bearer {personalAccessToken}` — standard Bearer token.
- **Paginated list fetch:** When loading your lists, Contactum automatically fetches all pages by following the `next_page_url` cursor until no further pages exist. All lists are loaded regardless of how many you have.
- **Lists field format:** The SendFox contacts endpoint accepts `lists` as an array of numeric list IDs. Contactum sends `[list_id]` — a single-element array containing the selected list's integer ID.
- **Credentials storage:** `apiKey` and `status` are stored in `wp_options` under the key `contactum_sendfox`. Restrict database access and keep WordPress authentication keys strong.
