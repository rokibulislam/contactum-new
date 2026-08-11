# Amazon S3 Integration

Store form file and image uploads directly on Amazon S3 instead of, or alongside, your WordPress server.

---

## Requirements

- **Contactum Pro** (the S3 integration is a pro feature)
- An active **AWS account**
- An S3 bucket in the region closest to your users
- An IAM user with write-only access scoped to that bucket

---

## 1. Create an IAM User in AWS

The plugin requires only two S3 actions. Use the minimum policy below — do **not** grant full S3 or admin access.

**Step-by-step**

1. Sign in to the [AWS Console](https://console.aws.amazon.com/iam/) and open **IAM → Users → Create user**.
2. Choose **Attach policies directly**, then click **Create inline policy**.
3. Switch to the **JSON** editor and paste:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:PutObject",
        "s3:DeleteObject"
      ],
      "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/*"
    }
  ]
}
```

Replace `YOUR-BUCKET-NAME` with your actual bucket name.

4. Name the policy (e.g. `ContactumS3Upload`) and save.
5. Open the user → **Security credentials** → **Create access key**.
6. Choose **Application running outside AWS**, then copy the **Access Key ID** and **Secret Access Key** — the secret is shown only once.

---

## 2. Create an S3 Bucket

1. Go to **S3 → Create bucket**.
2. Choose the same **Region** you will enter in Contactum.
3. **Block Public Access**: if you want direct file URLs (public files), uncheck **Block all public access** and acknowledge the warning. If files should be private (no direct URLs), leave it blocked.
4. Keep all other defaults and create the bucket.

---

## 3. Configure Global Settings in Contactum

Go to **Contactum → Settings → Integrations → Amazon S3**.

| Field | Description |
|---|---|
| **Access Key ID** | The IAM access key from Step 1 |
| **Secret Access Key** | The IAM secret key (stored encrypted in `wp_options`) |
| **Bucket Name** | Exact bucket name, no URL — e.g. `my-forms-bucket` |
| **Region** | Must match the bucket's region — e.g. `us-east-1` |
| **Path Prefix** | Leading folder applied to every upload — e.g. `contactum/uploads/` (include trailing slash) |
| **File Visibility (ACL)** | `Public` → standard HTTPS URL in the entry. `Private` → no direct URL; requires pre-signed access |
| **Delete local copy after upload** | Removes the file from your server after a successful S3 upload. Cannot be undone — only enable when S3 is stable |

Click **Save Settings**. The plugin immediately tests the connection by writing and deleting a small test object (`.contactum-test-{timestamp}`). A green **Connected** badge confirms success.

If the badge shows **Not connected**, click **Test Connection** after saving to see the exact AWS error message.

---

## 4. Enable S3 on a Specific Form

Global settings connect the plugin to AWS but do not activate uploads for any form. You must enable S3 per form.

1. Open the form in the **Contactum form builder**.
2. Go to **Form Settings → Integrations**.
3. Find **Amazon S3** and toggle it on. The **Configure** button will appear.
4. Click **Configure** to set form-level options:

| Option | Description |
|---|---|
| **Enable S3 for this form** | Master switch — uploads from this form go to S3 |
| **Folder Prefix** | Appended after the global path prefix. Example: `contact-form/` → final key is `contactum/uploads/contact-form/filename.pdf`. Leave blank to use the global prefix only |
| **Delete local copy after upload** | Overrides the global setting for this form only |

5. Save the form.

> **Important:** If the **Amazon S3** card shows **Not Connected**, the global credentials have not been saved or verified. Complete Step 3 first.

---

## 5. How Uploads Work

When a visitor submits a form with a file or image field:

1. WordPress saves the file as a media attachment (standard behaviour).
2. Contactum fires the `contactum_after_file_uploaded` hook with the attachment ID, local path, and form ID.
3. The S3 integration checks: global `status` is `true` → form has S3 enabled → uploads the file to S3.
4. The final S3 object key is built as:

```
{global_path_prefix}{form_folder_prefix}{original_filename}
```

Example with `path_prefix = contactum/uploads/` and `folder_prefix = enquiries/`:

```
contactum/uploads/enquiries/proposal.pdf
```

5. The S3 URL and key are stored as post meta on the attachment:
   - `_contactum_s3_url` — full HTTPS URL (public files only)
   - `_contactum_s3_key` — object key for private access or future deletion

6. If **Delete local copy** is enabled, the local file is removed from the server.

---

## 6. File Visibility

| ACL | Behaviour | When to use |
|---|---|---|
| `public-read` | File URL is directly accessible via `https://{bucket}.s3.{region}.amazonaws.com/{key}` | General contact forms, portfolio uploads, non-sensitive files |
| `private` | No direct URL. Access requires pre-signed URLs or bucket policy | Medical forms, legal documents, payment receipts, anything sensitive |

> S3 Object Ownership must be set to **ACLs enabled** on the bucket for the `x-amz-acl` header to take effect. In newer AWS accounts, ACLs are disabled by default — enable them under **Bucket → Permissions → Object Ownership → ACLs enabled**.

---

## 7. Troubleshooting

### "Connection could not be verified" after saving

- Confirm the bucket name is spelled exactly as in the AWS Console (case-sensitive).
- Confirm the region matches the bucket's region code (e.g. `eu-west-1`, not `Europe (Ireland)`).
- Confirm the IAM user has `s3:PutObject` and `s3:DeleteObject` on `arn:aws:s3:::YOUR-BUCKET/*` (note the `/*`).
- Check that **Block Public Access** settings on the bucket do not block the ACL header if using `public-read`.

### Files are not reaching S3

- The form must have S3 toggled on and the form must be saved after enabling.
- The field must be a **File Upload** or **Image** field type — S3 only fires for actual file uploads.
- Enable `WP_DEBUG` and `WP_DEBUG_LOG` — upload errors are logged with the prefix `[Contactum S3]`.

### "Access Denied" (HTTP 403)

The IAM policy `Resource` must end in `/*`, not just the bucket ARN:

```
"Resource": "arn:aws:s3:::my-bucket/*"   ✓
"Resource": "arn:aws:s3:::my-bucket"     ✗ (bucket-level, not object-level)
```

### Local file is missing but S3 shows no upload

If **Delete local copy** is enabled and an upload fails, the file may have been deleted before the error was caught. Disable **Delete local copy** until S3 is confirmed stable. Check `debug.log` for `[Contactum S3] Upload failed:` entries.

### ACL error (HTTP 400 — The bucket does not allow ACLs)

AWS disabled ACLs by default on buckets created after April 2023. Re-enable them:

1. Open the bucket in the AWS Console.
2. **Permissions → Object Ownership → Edit**.
3. Select **ACLs enabled → Object writer**.
4. Save.

---

## 8. Security Notes

- The **Secret Access Key** is stored in `wp_options` under `contactum_s3`. Restrict database access and use a strong WordPress salt.
- The secret key is **never exposed** in the browser — the settings UI replaces it with `••••••••••••••••••••` after the first save.
- Scope the IAM policy to the specific bucket. Do not use root account credentials.
- Use a dedicated IAM user for Contactum — rotate keys periodically.
- If using `private` ACL, do not share `_contactum_s3_url` values in emails or public pages — they are not pre-signed and will return 403.
