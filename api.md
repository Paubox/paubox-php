# Paubox PHP API Reference

## Email API (`Paubox\Paubox`)

Base URL: `https://api.paubox.com/v1/email`  
Authentication: `Authorization: Bearer {PAUBOX_API_KEY}`

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

Base URL: `https://api.paubox.com/v1/forms`  
Authentication: None for the public endpoints documented here; form-management endpoints use `Authorization: Bearer {PAUBOX_FORMS_API_KEY}` (see README)

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

---

## Webhooks API (`Paubox\PauboxWebhooks`)

Base URL: `https://api.paubox.com/v1/webhooks`  
Authentication: `Authorization: Bearer {PAUBOX_WEBHOOKS_API_KEY}`

A webhook endpoint is a URL you own that Paubox notifies when something happens. These methods
manage those subscriptions: which URL to notify, and for which events.

Every request carries the `/endpoints` resource. The bare base is unrouted on the public gateway
by design: the rule is scoped to `/endpoints` so the producers' event-ingest route stays private.

The key is read from the constructor or the `PAUBOX_WEBHOOKS_API_KEY` environment variable, and
the base URL from `PAUBOX_WEBHOOKS_BASE_URL`. Which events a key may subscribe to follows from
its scopes; the SDK does not inspect them, so an event the key is not scoped for returns 403 and
an unrecognised one 422.

```php
$webhooks = new Paubox\PauboxWebhooks('YOUR-SCOPED-API-KEY');
```

---

### `listWebhookEndpoints(array $params = []): stdClass`

Lists the endpoints this key can act on. Endpoints carrying an event the key is not scoped for
are filtered out by the service.

**Endpoint:** `GET /endpoints`

**Parameters**

| Name | Type | Required | Description |
| --- | --- | --- | --- |
| `page` | int | No | 1-based page number |
| `items` | int | No | Page size |

Any other key is ignored rather than forwarded.

**Returns** the full document — `data` plus `page_info` — since a caller paginating needs both:

```json
{
  "data": [
    {
      "id": "2ec66c21-bf48-48eb-8d28-f80b2d6b77c7",
      "target_url": "https://example.com/paubox-webhook",
      "status": "active",
      "events": ["forms.submission.created"],
      "created_at": "2026-10-09T01:43:41.639968+00:00",
      "updated_at": "2026-10-09T01:43:41.639968+00:00"
    }
  ],
  "page_info": { "count": 1, "items": 50 }
}
```

`page_info->count` is the total number of matching endpoints, not the length of `data`.

---

### `createWebhookEndpoint(array $params): stdClass`

Subscribes a URL to one or more events.

**Endpoint:** `POST /endpoints`

**Parameters**

| Name | Type | Required | Description |
| --- | --- | --- | --- |
| `target_url` | string | Yes | An https URL resolving to a publicly routable address |
| `events` | string[] | Yes | Non-empty list of event names |

**Returns** the endpoint itself, unwrapped from its `data` envelope, carrying `signing_secret`:

```php
$endpoint = $webhooks->createWebhookEndpoint([
    'target_url' => 'https://example.com/paubox-webhook',
    'events'     => ['forms.submission.created'],
]);

$endpoint->signing_secret; // whsec_...
```

`signing_secret` is returned **only here**. It is absent from `getWebhookEndpoint` and
`listWebhookEndpoints` and cannot be read back: recovering from a lost secret means replacing
the endpoint.

Event names are not validated client-side — the catalog belongs to the service and grows without
an SDK release.

**Throws** `Paubox\Webhooks\PauboxWebhooksException`:
- HTTP 422 — missing or malformed `target_url`, an empty `events`, an unrecognised event, or a
  `target_url` that already has an endpoint
- HTTP 403 — an event this key is not scoped for

---

### `getWebhookEndpoint(string $id): stdClass`

**Endpoint:** `GET /endpoints/{id}`

**Parameters**

| Name | Type | Required | Description |
| --- | --- | --- | --- |
| `$id` | string | Yes | Endpoint UUID |

**Returns** the endpoint, unwrapped from its `data` envelope. The signing secret is not included.

**Throws** `PauboxWebhooksException` with HTTP 404 if no such endpoint exists, or it belongs to
another account. An id that is not UUID-shaped throws locally, before any request is sent.

---

### `updateWebhookEndpoint(string $id, array $params): stdClass`

A partial update: only the keys present in `$params` are sent, so changing the status leaves the
URL and events alone.

**Endpoint:** `PATCH /endpoints/{id}`

**Parameters**

| Name | Type | Required | Description |
| --- | --- | --- | --- |
| `target_url` | string | No | New delivery target |
| `status` | string | No | `active` or `disabled` |
| `events` | string[] | No | Replaces the list rather than adding to it |

At least one key is required.

```php
$endpoint = $webhooks->updateWebhookEndpoint($id, ['status' => 'disabled']);
```

`disabled` pauses deliveries without losing the subscription; `active` resumes them.

---

### `deleteWebhookEndpoint(string $id): bool`

**Endpoint:** `DELETE /endpoints/{id}`

**Returns** `true`. The service answers `204 No Content`, so there is no body to decode.

Deleting stops every event on that endpoint and discards its signing secret.

---

### Webhook errors

Every non-2xx response throws `Paubox\Webhooks\PauboxWebhooksException`. The service's own
message is promoted onto the exception message, since a bare status says nothing about which
field was wrong:

```php
use Paubox\Webhooks\PauboxWebhooksException;

try {
    $webhooks->createWebhookEndpoint($params);
} catch (PauboxWebhooksException $e) {
    $e->getStatusCode();   // 422
    $e->getMessage();      // createWebhookEndpoint failed: HTTP 422: target_url: ...
    $e->getUrl();
    $e->getResponseBody(); // raw response
}
```

A body that is not JSON leaves the message as just the status, with the raw body still on
`getResponseBody()`.
