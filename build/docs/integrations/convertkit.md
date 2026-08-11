# ConvertKit Integration

Automatically subscribe form submitters to a ConvertKit form (mailing list) when they submit a Contactum form.

> **Note:** ConvertKit rebranded to **Kit** in late 2023. The product may appear as "Kit" in the ConvertKit dashboard, but the API is still the ConvertKit API v3 and the credentials are found in the same place.

---

## Requirements

- **Contactum Pro**
- An active **ConvertKit (Kit) account**
- A ConvertKit **API Key** and **API Secret**

---

## 1. Get Your ConvertKit API Key and API Secret

ConvertKit requires two credentials: a public **API Key** and a private **API Secret**. Both are found in the same location.

1. Log in to your ConvertKit (Kit) account at [app.convertkit.com](https://app.convertkit.com).
2. Click your account name or avatar in the top-right corner and select **Settings**.
3. Go to the **Advanced** section (or **Developer** section depending on your account view).
4. Under **API**, you will find:
   - **API Key** — your public key used for read operations and subscribing contacts
   - **API Secret** — your private key required for account-level operations
5. Copy both values.

> Keep your API Secret private — it grants full read/write access to your ConvertKit account. The API Key alone allows subscribing contacts to forms.

---

## 2. Connect ConvertKit in Contactum

1. Go to **Contactum → Settings → Integrations → ConvertKit**.
2. Enter your **API Key** in the **ConvertKit API Key** field.
3. Enter your **API Secret** in the **ConvertKit API Secret** field.
4. Click **Save Settings**.

Contactum validates the credentials by calling `GET /forms` on the ConvertKit v3 API using the provided API Key. If the request returns your list of forms, the key is valid. A **"Your ConvertKit api key has been verified and successfully set"** message appears and the status badge turns green.

If the API Key is empty or invalid, validation fails and the integration is not activated.

**To disconnect**, click **Disconnect ConvertKit**. This clears both the API Key and API Secret and disables all ConvertKit subscriptions until you reconnect.

---

## 3. Enable ConvertKit on a Specific Form

The global connection does not subscribe anyone on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **ConvertKit** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **List** | Yes | The ConvertKit form to subscribe the contact to. Click the refresh (↻) icon to load all forms from your account |
| **Email** | Yes | Map to the form field that collects the subscriber's email address |
| **First Name** | No | Map to a text or name field for the subscriber's first name |
| **Last Name** | No | Map to a text or name field for the subscriber's last name |

> **ConvertKit "Forms" vs "Lists":** In ConvertKit, subscribers are added to **Forms** (not lists in the traditional sense). The dropdown labelled **List** in Contactum shows your ConvertKit forms. Select the form whose subscriber list you want to grow.

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
2. The ConvertKit integration checks that:
   - The global API Key and API Secret are saved and verified (`status: true`)
   - The form has ConvertKit enabled
   - A **List** (ConvertKit form ID) and an **Email** mapping are both configured
3. Smart tags in the Email, First Name, and Last Name fields are resolved against the submitted entry data.
4. If the resolved email address is empty, the subscription is silently skipped.
5. A `POST` request is sent to the ConvertKit API:

```
POST https://api.convertkit.com/v3/forms/{form_id}/subscribe?api_key={api_key}
Content-Type: application/json

{
  "email": "subscriber@example.com",
  "first_name": "Jane",
  "last_name": "Doe",
  "api_key": "{api_key}"
}
```

6. ConvertKit creates the subscriber and adds them to the specified form's subscriber list.
7. A successful response returns a `subscription` object containing the subscriber details.

> Subscription happens after the entry is saved. A ConvertKit API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Subscriber Behaviour in ConvertKit

| Scenario | Result |
|---|---|
| New email address | Subscriber created and added to the form |
| Existing email, same form | Subscriber's first/last name updated; subscription unchanged |
| Existing email, different form | Subscriber added to the new form; existing subscriptions unchanged |
| Existing email, unsubscribed | ConvertKit may block re-subscription depending on unsubscribe type |
| Empty email from smart tag | Subscription skipped silently |
| Invalid or empty API Key | Subscription skipped silently; form still processes normally |

---

## 7. Troubleshooting

### API Key is not validating

- Confirm you copied the full API Key from **Settings → Advanced → API** with no extra spaces.
- The API Key is different from the API Secret — make sure each value is entered in its correct field.
- Confirm your ConvertKit account has at least one form. The auth test calls `GET /forms`, so an account with no forms returns an empty array but should still validate successfully.

### List dropdown is empty after clicking refresh

- Confirm the global API Key shows the green valid badge before refreshing — Contactum uses the saved credentials to fetch forms.
- Log in to ConvertKit and go to **Grow → Landing Pages & Forms** to confirm at least one form exists. If no forms are present, create one and then click refresh.

### Subscribers are not appearing in ConvertKit

1. Confirm the form has ConvertKit toggled **on** and was saved after enabling.
2. Confirm a **List** (ConvertKit form) is selected in the Configure dialog.
3. Confirm the **Email** field is mapped and the mapped form field contains a valid email address on submission.
4. Submit a test entry and verify the email value appears in **Contactum → Entries**.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### "Your ConvertKit API Key is not valid" error on save

- The API Key entered is incorrect. Copy it again directly from ConvertKit → **Settings → Advanced**.
- Make sure you are entering the **API Key** (shorter, used for public operations), not the **API Secret** (longer, used for account-level operations) in the wrong field.

### Subscriber is added but name is missing

- Confirm the First Name and Last Name smart tags are mapped in the Configure dialog.
- Check that the mapped form fields contain values — submit a test entry and verify the field values appear in **Contactum → Entries**.

---

## 8. Notes

- **ConvertKit terminology:** ConvertKit calls its subscriber lists **Forms**. In Contactum the field is labelled **List**, but the values shown are your ConvertKit forms. Selecting a form means new subscribers are added to that form's subscriber list.
- **API version:** Contactum uses ConvertKit API v3 (`https://api.convertkit.com/v3/`).
- **Auth method:** The API Key is passed as the `api_key` query parameter on every request. The API Secret is stored alongside the API Key but the subscribe endpoint uses only the API Key.
- **No double opt-in toggle:** Contactum does not expose a double opt-in setting for ConvertKit. Whether ConvertKit sends a confirmation email depends on the **incentive email** setting configured on the form inside ConvertKit itself.
- **Credentials storage:** `apiKey` and `apiSecret` are stored in `wp_options` under the key `convertkit`. Restrict database access and keep WordPress authentication keys strong.
