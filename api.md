# Paubox PHP API Reference

## Email API (`Paubox\Paubox`)

Base URL: `https://api.paubox.net/v1/{PAUBOX_API_USER}/`  
Authentication: `Authorization: Token token={PAUBOX_API_KEY}`

---

### `sendMessage(Message $message): object`

Sends a HIPAA-compliant email.

**Parameters**

| Class | Method | Type | Required | Description |
|---|---|---|---|---|
| `Message` | `setRecipients($array)` | string[] | Yes | To addresses |
| `Message` | `setCc($array)` | string[] | No | CC addresses |
| `Message` | `setBcc($array)` | string[] | No | BCC addresses |
| `Message` | `setHeader(Header $h)` | `Header` | Yes | Subject, From, Reply-To |
| `Message` | `setContent(Content $c)` | `Content` | Yes | Plain/HTML body |
| `Message` | `setAttachments($array)` | `Attachment[]` | No | File attachments |
| `Message` | `setAllowNonTLS($bool)` | bool | No | Allow non-TLS delivery (default false) |
| `Message` | `setForceSecureNotification($str)` | `"true"\|"false"` | No | Force portal notification |
| `Header` | `setSubject($str)` | string | Yes | Email subject |
| `Header` | `setFrom($str)` | string | Yes | Sender address |
| `Header` | `setReplyTo($str)` | string | No | Reply-To address |
| `Content` | `setPlainText($str)` | string | No | Plain text body |
| `Content` | `setHtmlText($str)` | string | No | HTML body (base64-encoded automatically) |
| `Attachment` | `setFileName($str)` | string | Yes | File name |
| `Attachment` | `setContentType($str)` | string | Yes | MIME type |
| `Attachment` | `setContent($str)` | string | Yes | Base64-encoded file content |

**Returns** `stdClass` with:
```
sourceTrackingId  string   Use this to check delivery status
data              object
errors            array
```

**Throws** `\Exception` if `Header` or `Content` is null, or if the API returns an unparseable response.

---

### `getEmailDisposition(string $sourceTrackingId): object`

Checks the delivery and open status of a sent message.

**Returns** `stdClass` with:
```
sourceTrackingId  string
data
  message
    id                  string
    message_deliveries  array
      recipient         string
      status
        deliveryStatus  string
        deliveryTime    string
        openedStatus    string
        openedTime      string
errors  array
```

**Throws** `\Exception` if the API returns an unparseable response.

---

## Forms API (`Paubox\PauboxForms`)

Base URL: `https://next.paubox.com`  
Authentication: None

---

### `getForm(string $formId): stdClass`

Retrieves the full definition of a form by UUID.

**Endpoint:** `GET /public/form_data/{form_id}`

**Parameters**

| Name | Type | Description |
|---|---|---|
| `$formId` | string (UUID) | UUID of the form to retrieve |

**Returns** `stdClass` with:
```
id                          string (UUID)
title                       string
description                 string|null
form_html                   string|null   Rendered HTML of the form
form_json                   object|null   Field schema
form_css                    string|null   Scoped CSS
vanity_url                  string|null
version                     int
active                      bool
customer_id                 int
signable                    bool
signature_confirmation_label string|null
submission_count            int
type                        string|null
deleted                     bool
archived                    bool
created_at                  string (ISO 8601)
updated_at                  string (ISO 8601)
```

**Throws** `\Exception` if the form is not found or the response is invalid.

---

### `submitForm(string $formId, FormSubmission $submission): true`

Submits a respondent's answers for a form.

**Endpoint:** `POST /api/forms/{form_id}/submissions`

Maximum request size: **250 MB** (to support file attachments).

**Parameters**

| Class | Method | Type | Required | Description |
|---|---|---|---|---|
| `FormSubmission` | `setFormData($array)` | array | Yes | Key-value pairs matching the form's `form_json` schema |
| `FormSubmission` | `setAttachments($array)` | `FormAttachment[]` | No | File attachments |
| `FormAttachment` | `setName($str)` | string | Yes | Filename |
| `FormAttachment` | `setContent($str)` | string | Yes | Base64-encoded file content |

**Returns** `true` on success (HTTP 201).

**Throws** `\Exception` on failure:
- HTTP 400 — `form_data` field is missing
- HTTP 404 — form not found
