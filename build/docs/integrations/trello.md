# Trello Integration

Automatically create a new Trello card whenever a Contactum form is submitted. Unlike email marketing integrations, Trello is a project management tool — each form submission becomes a card in a Trello board column (list) of your choosing.

---

## Requirements

- **Contactum Pro**
- An active **Trello account**
- A Trello **API Key** and **User Token** (both from [trello.com/app-key](https://trello.com/app-key))

---

## Understanding Trello's Credential System

Trello requires two credentials used together on every API request:

| Credential | What it is |
|---|---|
| **API Key** | A key that identifies your Trello application. Shared by all users of your app |
| **User Token** | A personal token that grants the API Key permission to act on your behalf and access your boards |

Both are found on the same page and both are required. Neither expires by default unless you revoke them manually.

---

## 1. Get Your Trello API Key and User Token

1. Log in to your Trello account and go to [trello.com/app-key](https://trello.com/app-key) (or navigate to **Trello → Power-Ups → Developer API Keys**).
2. Your **API Key** is displayed at the top of the page. Copy it.
3. On the same page, click the **Token** hyperlink shown next to your API Key description.
4. Trello displays an authorization screen — click **Allow** to grant access.
5. Copy the long **Token** string that appears on the confirmation page. This is your **User Token**.

> Keep both values private. The User Token grants access to all boards, cards, and lists in your Trello account. Revoking it is done from [trello.com/app-key](https://trello.com/app-key) → **Token**.

---

## 2. Connect Trello in Contactum

1. Go to **Contactum → Settings → Integrations → Trello**.
2. Enter your **API Key** in the API Key field.
3. Enter your **User Token** in the User Token field.
4. Click **Verify Trello**.

Contactum validates both credentials by calling `GET /tokens/{token}/member` on the Trello v1 API. If a member record is returned with a valid `id`, the credentials are confirmed. A **"Your Trello credentials have been verified and saved"** message appears and the status badge turns green.

If either credential is wrong, the error from Trello is shown.

**To disconnect**, click **Disconnect Trello**. This clears both credentials and stops all Trello card creation until you reconnect.

---

## 3. Enable Trello on a Specific Form

The global connection does not create any cards on its own — you must enable the integration per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Trello** and toggle it on.

   > If the card shows **Not Connected**, complete Step 2 first.

4. Click **Configure** to open the field mapping dialog.

---

## 4. Configure Field Mapping

| Field | Required | Description |
|---|---|---|
| **Trello List** | Yes | The Trello board column to create the card in. Click the refresh (↻) icon to load all lists across all your open boards |
| **Card Title** | No | The title of the Trello card. Accepts smart tags. Defaults to `Form Submission` if left blank |
| **Card Description** | No | The body text of the Trello card. Accepts smart tags |

> **No email field.** Trello is a project management tool — there is no subscriber or contact concept. The integration creates a card; what you put in the card title and description is entirely up to you.

### Using Smart Tags

Both the Card Title and Card Description fields accept smart tags from your form. Click the **{ }** merge tag button and select the form field. The value is inserted as `{field_name}`. Example:

```
Card Title       →  New enquiry from {first_name} {last_name}
Card Description →  Email: {email}
                    Message: {message}
                    Phone: {phone}
```

You can combine multiple smart tags with static text in both fields.

### The Trello List Dropdown

When you click the refresh (↻) button, Contactum fetches all **open boards** and then fetches all **open lists** within each board. The dropdown displays every available list in the format:

```
[Board Name] List Name
```

For example: `[Website Enquiries] To Do` or `[Sales Pipeline] New Leads`.

Select the list where new cards should be created.

5. Click **Save Settings** in the dialog, then **Save** the form.

---

## 5. How Card Creation Works

When a visitor submits the form:

1. Contactum saves the form entry and fires the `contactum_entry_submission` action.
2. The Trello integration checks that:
   - The global API Key and User Token are saved and verified (`status: true`)
   - The form has Trello enabled
   - A **Trello List** is selected
3. Smart tags in the Card Title and Card Description fields are resolved against the submitted entry data.
4. If the Card Title resolves to an empty string, it is replaced with `"Form Submission"`.
5. A `POST` request is sent to the Trello API:

```
POST https://api.trello.com/1/cards?key={api_key}&token={user_token}

idList=abc123xyz
name=New+enquiry+from+Jane+Doe
desc=Email%3A+jane%40example.com%0AMessage%3A+Hello+...
```

Trello authentication credentials (`key` and `token`) are sent as URL query parameters. The card data is sent as a form-encoded request body.

6. Trello creates a new card in the selected list on the appropriate board.

> Card creation happens after the entry is saved. A Trello API failure does **not** block the form submission or show an error to the visitor — the form completes normally.

---

## 6. Card Behaviour in Trello

| Scenario | Result |
|---|---|
| Form submitted with all fields filled | New card created in the selected list with title and description |
| Card Title field left blank or resolves empty | Card created with the title `Form Submission` |
| Card Description field left blank | Card created with no description body |
| Selected list deleted or archived in Trello | Card creation fails silently; click refresh and reselect a valid list |
| Invalid API Key or User Token | Card creation skipped silently; form still processes normally |

---

## 7. Troubleshooting

### "Invalid credentials" error on save

- Confirm both the **API Key** and **User Token** were copied from [trello.com/app-key](https://trello.com/app-key) with no extra spaces.
- The User Token is the long string shown after you click **Allow** on the Trello authorization page — not the API Key itself.
- If you revoked the token in Trello, generate a new one by clicking the **Token** link again and re-authorizing.

### Trello List dropdown is empty after clicking refresh

- Confirm both credentials show the green valid badge before clicking refresh.
- Confirm you have at least one open board with at least one open list in Trello. Archived boards and closed lists are excluded.
- If all your boards or lists are archived, create or reopen at least one, then click refresh.

### Cards are not appearing in Trello

1. Confirm the form has Trello toggled **on** and was saved after enabling.
2. Confirm a **Trello List** is selected in the Configure dialog.
3. Submit a test entry and check whether the entry appears in **Contactum → Entries**.
4. In Trello, open the board and scroll through the selected list. Cards appear immediately.
5. Enable `WP_DEBUG_LOG` and check `wp-content/debug.log` for API errors during submission.

### Card appears in the wrong board or list

- If you recently moved a list to a different board in Trello, the list ID stays the same but the board context changes. Click refresh in the Configure dialog and reselect the correct list to confirm the right one is selected.

### Smart tags are not resolving in Card Title or Description

- Confirm the smart tag field name matches the actual field name in your form (e.g. `{email}` requires a field named `email`).
- Submit a test entry and verify the expected values appear in **Contactum → Entries** for those fields.

---

## 8. Notes

- **Project management, not email marketing:** Trello creates **cards** in board lists — not subscriber records. There is no email, contact, or list-membership concept in this integration.
- **API version:** Contactum uses Trello REST API v1 (`https://api.trello.com/1/`).
- **Auth method:** Query parameters — `?key={api_key}&token={user_token}` are appended to every request URL. Trello v1 does not use Authorization headers.
- **POST body encoding:** Trello v1 card creation uses a **form-encoded** (not JSON) request body.
- **Board and list scope:** The list dropdown fetches only **open** boards (`filter=open`) and **open** lists (`filter=open`). Archived boards and closed lists are not shown.
- **List label format:** Lists in the dropdown are prefixed with their board name in brackets — `[Board Name] List Name` — so you can identify which board each list belongs to when you have multiple boards.
- **Card title fallback:** If the Card Title field is empty after smart tag resolution, Contactum uses `"Form Submission"` as the default title.
- **Credentials storage:** `api_key`, `access_token`, and `status` are stored in `wp_options` under the key `contactum_trello`. Restrict database access and keep WordPress authentication keys strong.
