# GetResponse Integration

Automatically add form submitters as contacts to a GetResponse list when they submit a Contactum form.

---

## Requirements

- **Contactum Pro**
- An active **GetResponse account**
- A GetResponse **API Key**

---

## 1. Get Your GetResponse API Key

1. Log in to your GetResponse account at [app.getresponse.com](https://app.getresponse.com).
2. Click your account name in the top-right corner and go to **Profile**.
3. Select **Integrations & API** from the left sidebar (or go to **Tools → Integrations & API**).
4. Click the **API** tab.
5. Click **Generate API Key** if no key exists, or copy the existing key.

> Keep the API Key private — it grants full access to your GetResponse account including contacts and campaigns.

---

## 2. Connect GetResponse in Contactum

1. Go to **Contactum → Settings → Integrations → GetResponse**.
2. Enter your API Key in the **GetResponse API Key** field.
3. Click **Save Settings**.

Contactum validates the key by calling `GET /accounts` on the GetResponse v3 API using the `X-Auth-Token` header. If the response contains a valid account object (no `codeDescription` error), the key is confirmed. A **"Your settings have been updated!"** message appears and the status badge turns green.

If the key is invalid, the `codeDescription` from GetResponse is shown as the error message.

**To disconnect**, click **Disconnect GetResponse**. This clears the API Key and disables all GetResponse contact syncing until you reconnect.

---

## 3. Enable GetResponse on a Specific Form

The global connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **GetResponse** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The GetResponse list to add the contact to. Click the refresh (↻) icon to load all lists from your account |
| **Email** | Yes | Map to the form field that collects the contact's email address |
| **First Name** | No | Map to a text or name field for the contact's first name |
| **Last Name** | No | Map to a text or name field for the contact's last name |

> **GetResponse "Lists" are called "Campaigns" in the API.** The List dropdown in Contactum shows your GetResponse campaigns (mailing lists). This is purely a naming difference — select the list you want contacts added to.

### Using Smart Tags

Each text field accepts a smart tag from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Email      →  {email}
First Name →  {first_name}
Last Name  →  {last_name}
```

### Name Handling

GetResponse accepts a single combined **Name** field. Contactum automatically joins the resolved First Name and Last Name values with a space:

```
"Jane" + " " + "Doe"  →  "Jane Doe"
```

If both First Name and Last Name resolve to empty, the email address is used as the contact name instead.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Subscriptions Work

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The GetResponse integration checks that:
   - The global API Key is saved and verified (`status: true`)
   - The form has GetResponse enabled
   - A **List** and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. First Name and Last Name are joined into a single name. If both are empty, the email address is used as the name.
6. A `POST` request is sent to the GetResponse API:

```
POST https://api.getresponse.com/v3/contacts
X-Auth-Token: api-key {apiKey}
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "subscriber@example.com",
  "campaign": {
    "campaignId": "abc123"
  }
}
```

7. GetResponse creates the contact and adds them to the specified campaign (list).

> Subscription happens after the entry is saved. A GetResponse API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Contact Behaviour in GetResponse

| Scenario | Result |
|---|---|
| New email address | Contact created and added to the list |
| Existing email, same list | GetResponse updates the contact name |
| Existing email, different list | Contact added to the new list |
| Existing email, unsubscribed | GetResponse may block re-subscription depending on account settings |
| Empty email from smart tag | Subscription skipped silently |
| Invalid or empty API Key | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### API Key is not validating

- Copy the API Key directly from GetResponse → **Profile → Integrations & API → API tab** with no extra spaces.
- If you recently regenerated your API Key, the previous key is no longer valid. Update it in Contactum.
- Confirm your server can reach `api.getresponse.com` — some hosting environments restrict outbound connections.

### List dropdown is empty after clicking refresh

- Confirm the global API Key shows the green valid badge before clicking refresh.
- Log in to GetResponse and confirm you have at least one list under **Contacts → Lists**. Create a list if none exist, then click refresh.

### Contacts are not appearing in GetResponse

1. Confirm the form has GetResponse toggled **on** and was saved after enabling.
2. Confirm a **List** is selected in the Configure dialog.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email on submission.
4. Submit a test entry and check the email value in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for exceptions thrown during submission.

### "Error in external resources" or connection error

- This message indicates Contactum could not reach the GetResponse API. Check your server's outbound internet access and confirm `api.getresponse.com` is not blocked by a firewall.

### GetResponse Max360 or Enterprise accounts

GetResponse offers a separate enterprise-tier product (GetResponse MAX / MAX360) with a different API endpoint. The Contactum integration uses the standard `https://api.getresponse.com/v3` endpoint. If your account uses a custom enterprise API URL, the integration may not connect correctly — contact support for custom endpoint configuration.

---

## 8. Notes

- **API version:** Contactum uses GetResponse API v3 (`https://api.getresponse.com/v3`).
- **Auth method:** `X-Auth-Token: api-key {apiKey}` — GetResponse's own authentication header format, not Bearer or Basic.
- **Lists vs Campaigns:** GetResponse internally calls mailing lists "campaigns" (`campaignId`). The Contactum UI labels them "Lists" to match common email marketing terminology.
- **Single name field:** The GetResponse contacts API accepts one combined `name` field. Contactum joins First Name and Last Name before sending. There is no separate first/last name field in the API payload.
- **Credentials storage:** `apiKey` and `status` are stored in `wp_options` under the key `contactum_getresponse`. Restrict database access and keep WordPress authentication keys strong.
