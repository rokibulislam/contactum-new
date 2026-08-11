# iContact Integration

Automatically add form submitters as contacts to an iContact list when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **iContact account**
- An iContact **App ID**, **Username**, and **API Password** (three separate credentials)

---

## Understanding iContact's Credential System

iContact uses three separate credentials for API access — not a single API key:

| Credential | What it is |
|---|---|
| **App ID** | A unique identifier for your registered API application. Created in the iContact developer portal |
| **Username** | Your iContact account email address (the same one you use to log in) |
| **API Password** | A separate password set specifically for API access — **not** your iContact login password |

All three are required. iContact sends these as custom HTTP headers on every API request.

---

## 1. Register an App and Get Your Credentials

### App ID

1. Log in to your iContact account at [app.icontact.com](https://app.icontact.com).
2. Go to **Settings → Developer API** (or navigate to **iContact Developer Portal**).
3. Register a new application by providing an app name and description.
4. After registration, your **App ID** is displayed on the application detail page. Copy it.

### Username

Your username is the **email address** you use to log in to iContact.

### API Password

The API Password is a separate credential from your iContact login password.

1. In your iContact account, go to **Settings → API Password** (or find it under your profile/account settings).
2. Set or copy your API Password. If you have not set one before, create one now.

> The API Password is not the same as your iContact login password. It is a dedicated credential for API integrations only.

---

## 2. Connect iContact in Contactum

1. Go to **Contactum → Settings → Integrations → iContact**.
2. Enter your **App ID** in the App ID field.
3. Enter your **Username** (your iContact login email) in the Username field.
4. Enter your **API Password** in the API Password field.
5. Click **Save Settings**.

Contactum validates all three credentials by:

1. Calling `GET /accounts` to retrieve your account ID and confirm the account is active.
2. Calling `GET /{accountId}/c/` to retrieve your client folder ID.

If both requests succeed, the account ID and client folder ID are saved automatically alongside your credentials — you do not need to enter them manually. A **"Your iContact credentials have been verified and saved"** message appears and the status badge turns green.

If any credential is wrong, the error message from iContact is shown.

**To disconnect**, click **Disconnect iContact**. This clears all stored credentials and stops all iContact subscriptions until you reconnect.

---

## 3. Enable iContact on a Specific Form

The global connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **iContact** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The iContact list to subscribe the contact to. Click the refresh (↻) icon to load all lists from your account |
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the contact's first name |
| **Last Name** | No | Map to a text or name field for the contact's last name |

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The iContact integration checks that:
   - The global credentials are saved and verified (`status: true`)
   - The form has iContact enabled
   - A **List** and an **Email** mapping are both configured
3. The email address is resolved from the mapped smart tag. If empty, the subscription is silently skipped.
4. First Name and Last Name are resolved from their mapped fields.
5. A contact creation request is sent to the iContact API:

```
POST https://app.icontact.com/icp/a/{accountId}/c/{clientFolderId}/contacts
Api-Version: 2.2
Api-AppId: {appId}
Api-Username: {username}
Api-Password: {apiPassword}
Content-Type: application/json

[
  {
    "email": "subscriber@example.com",
    "firstName": "Jane",
    "lastName": "Doe"
  }
]
```

6. If iContact returns a `contactId` for the newly created or matched contact, a second request subscribes the contact to the selected list:

```
POST https://app.icontact.com/icp/a/{accountId}/c/{clientFolderId}/subscriptions
Api-Version: 2.2
Api-AppId: {appId}
Api-Username: {username}
Api-Password: {apiPassword}
Content-Type: application/json

[
  {
    "contactId": 12345,
    "listId": "67890",
    "status": "normal"
  }
]
```

> Subscription happens after the entry is saved. An iContact API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Contact Behaviour in iContact

| Scenario | Result |
|---|---|
| New email address | Contact created and subscribed to the list with status `normal` |
| Existing email address | iContact returns the existing contact ID; subscription to the list is applied |
| Existing email, already subscribed to list | iContact accepts the request without creating a duplicate |
| Existing email, previously unsubscribed | iContact may reject re-subscription depending on list and account settings |
| Empty email from smart tag | Subscription skipped silently |
| Missing contactId in response | List subscription step is skipped; contact may still have been created |
| Invalid credentials | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "No iContact accounts found for these credentials" error

- Confirm the App ID, Username, and API Password are all correct. Any one of the three being wrong causes this error.
- The Username must be your iContact **login email address**, not a display name or username alias.
- The API Password is not your login password. Check it in iContact → **Settings → API Password**.

### "Your iContact account has been disabled" error

- Your iContact account has been suspended or deactivated. Log in to iContact and check your account status.

### "No client folders found for this iContact account" error

- iContact organizes contacts under a hierarchy of accounts and client folders. If no client folders exist, the integration cannot determine where to create contacts. Contact iContact support to verify your account structure.

### List dropdown is empty after clicking refresh

- Confirm the global credentials show the green valid badge before clicking refresh.
- Log in to iContact and confirm at least one subscriber list exists under your account. Go to **Contacts → Lists** to verify.

### Contacts are not appearing in iContact after form submission

1. Confirm the form has iContact toggled **on** and was saved after enabling.
2. Confirm a **List** is selected and the **Email** field is mapped in the Configure dialog.
3. Submit a test entry and verify the email value appears in **Contactum → Entries**.
4. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API exceptions.
5. In iContact, go to **Contacts** and search for the test email address directly.

### Contact appears in iContact but is not subscribed to the list

- The contact creation step succeeded but the list subscription step failed. This can happen if the contact was created without a `contactId` being returned.
- Confirm the selected list ID still exists in iContact — if the list was deleted and recreated, its ID changes. Open the Configure dialog, click refresh, and reselect the list.

---

## 8. Notes

- **API version:** Contactum uses iContact API v2.2. The version is sent as the `Api-Version: 2.2` request header on every call.
- **Auth method:** Three custom HTTP headers — `Api-AppId`, `Api-Username`, and `Api-Password`. iContact does not use Bearer tokens or Basic Auth.
- **Auto-resolved IDs:** During the save process, Contactum automatically fetches and stores your iContact `accountId` and `clientFolderId`. These are used to construct all API endpoint URLs (`/a/{accountId}/c/{clientFolderId}/...`) and are not visible in the settings form.
- **Two-step subscription:** iContact requires creating the contact first (to get a `contactId`), then subscribing that contact to a list as a separate API call. If the first call fails, the second is not attempted.
- **Contact field names:** iContact uses `firstName` and `lastName` (camelCase). These are sent exactly as shown in the API payload.
- **Subscription status:** All new list subscriptions use `"status": "normal"`, which is iContact's active/subscribed state.
- **Credentials storage:** `appId`, `username`, `apiPassword`, `accountId`, and `clientFolderId` are stored in `wp_options` under the key `contactum_icontact`. Restrict database access and keep WordPress authentication keys strong.
