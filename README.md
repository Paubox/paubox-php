<img src="https://avatars.githubusercontent.com/u/22528478?s=200&v=4" alt="Paubox" width="150px">

# Paubox PHP

**NEW:** [Version 2 of the Paubox Email API SDK for PHP](https://github.com/Paubox/paubox-php/tree/sdk-generation/v2.0.0-beta) is available to beta test now. It includes code for newer features like bulk message sending, dynamic templates, and more. We will be deprecating the old in the near future.

This is the official PHP wrapper for the [Paubox Email API](https://www.paubox.com/products/paubox-email-api). 

The Paubox Email API allows your application to send secure, HIPAA compliant email via Paubox and track deliveries and opens.
The API wrapper also allows you to construct and send messages.

# Table of Contents
* [Installation](#installation)
* [Usage](#usage)
  * [Sending messages](#sending-messages)
  * [Allowing non-TLS message delivery](#allowing-non-tls-message-delivery)
  * [Forcing Secure Notifications](#forcing-secure-notifications)
  * [Adding Attachments and Additional Headers](#adding-attachments-and-additional-headers)
  * [Checking Email Dispositions](#checking-email-dispositions)
  * [Paubox Forms](#paubox-forms)
    * [Getting a form](#getting-a-form)
    * [Submitting a form](#submitting-a-form)
    * [Submitting a form with file attachments](#submitting-a-form-with-file-attachments)
    * [Scoped API keys](#scoped-api-keys)
    * [Getting a form by ID](#getting-a-form-by-id)
    * [Listing forms](#listing-forms)
    * [Creating a form](#creating-a-form)
    * [Updating a form](#updating-a-form)
    * [Archiving and unarchiving a form](#archiving-and-unarchiving-a-form)
    * [Copying a form](#copying-a-form)
    * [Form statistics](#form-statistics)
    * [Listing submissions](#listing-submissions)
    * [Downloading submissions as CSV](#downloading-submissions-as-csv)
    * [Downloading a submission as PDF](#downloading-a-submission-as-pdf)
    * [Error handling](#error-handling)
    * [Requirements](#requirements)
* [Running the tests](#running-the-tests)
* [Contributing](#contributing)
* [License](#license)

<a name="#installation"></a>
## Installation

Using composer:

```bash
$ composer require paubox/paubox-php
```

### Getting Paubox API Credentials

You will need to have a Paubox account. You can [sign up here](https://www.paubox.com/pricing/paubox-email-api).

Once you have an account, follow the instructions on the Rest API dashboard to verify domain ownership and generate API credentials.

### Configuring API Credentials

Include your API credentials in your environment file.

```bash
$ echo "export PAUBOX_API_KEY='YOUR_API_KEY'" > .env
$ echo "export PAUBOX_API_USER='YOUR_ENDPOINT_NAME'" >> .env
$ source .env
$ echo ".env" >> .gitignore
```

<a name="#usage"></a>
## Usage

To send email, prepare a Message object and call the sendMessage method of
Paubox.

### Sending messages

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$paubox = new Paubox\Paubox();

$message = new Paubox\Mail\Message();
$content = new Paubox\Mail\Content();
$content->setPlainText("Hello World");

$header = new Paubox\Mail\Header();
$header->setSubject("Testing!");
$header->setFrom("sender@domain.com");

$recipients = array();
array_push($recipients,'recipient@example.com');

$message->setHeader($header);
$message->setContent($content);
$message->setRecipients($recipients);

$sendMessageResponse = new Paubox\Mail\SendMessageResponse();
$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```

### Allowing non-TLS message delivery

If you want to send non-PHI mail that does not need to be HIPAA-compliant, you can allow the message delivery to take place even if a TLS connection is unavailable.

This means the message will not be converted into a secure portal message when a non-TLS connection is encountered. To allow a non-TLS message delivery, call the `setAllowNonTLS(true)` method on the message object.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$paubox = new Paubox\Paubox();

$message = new Paubox\Mail\Message();
$content = new Paubox\Mail\Content();
$content->setPlainText("Hello World");

$header = new Paubox\Mail\Header();
$header->setSubject("Testing!");
$header->setFrom("sender@domain.com");

$recipients = array();
array_push($recipients,'recipient@example.com');

$message->setHeader($header);
$message->setContent($content);
$message->setRecipients($recipients);
$message->setAllowNonTLS(true);

$sendMessageResponse = new Paubox\Mail\SendMessageResponse();
$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```

### Forcing Secure Notifications
Paubox Secure Notifications allow an extra layer of security, especially when coupled with an organization's requirement for message recipients to use 2-factor authentication to read messages (this setting is available to org administrators in the Paubox Admin Panel).

Instead of receiving an email with the message contents, the recipient will receive a notification email that they have a new message in Paubox.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$paubox = new Paubox\Paubox();

$message = new Paubox\Mail\Message();
$content = new Paubox\Mail\Content();
$content->setPlainText("Hello World");

$header = new Paubox\Mail\Header();
$header->setSubject("Testing!");
$header->setFrom("sender@domain.com");

$recipients = array();
array_push($recipients,'recipient@example.com');

$message->setHeader($header);
$message->setContent($content);
$message->setRecipients($recipients);
$message->setForceSecureNotification("true");

$sendMessageResponse = new Paubox\Mail\SendMessageResponse();
$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```


### Adding Attachments and Additional Headers


```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$paubox = new Paubox\Paubox();

$message = new Paubox\Mail\Message();
$content = new Paubox\Mail\Content();
$content->setPlainText("Hello World");
$content->setHtmlText("<html><head></head><body>Hello World</body></html>");

$header = new Paubox\Mail\Header();
$header->setSubject("Testing!");
$header->setFrom("sender@domain.com");
$header->setReplyTo("reply_to@domain.com");

$firstAttachment = new Paubox\Mail\Attachment();
$firstAttachment->setFileName("hello_world.txt");
$firstAttachment->setContentType("text/plain");
$firstAttachment->setContent("SGVsbG8gV29ybGQh\n");

$secondAttachment = new Paubox\Mail\Attachment();
$secondAttachment->setFileName("hello_world2.txt");
$secondAttachment->setContentType("text/plain");
$secondAttachment->setContent("SGVsbG8gV29ybGQh\n");

$attachments = array();
array_push($attachments,$firstAttachment);
array_push($attachments,$secondAttachment);

$recipients = array();
array_push($recipients,'recipient@example.com');

$bcc = array();
array_push($bcc, 'recipient2@example.com');

$cc = array();
array_push($cc, 'recipientcc@example.com');

$message->setHeader($header);
$message->setContent($content);
$message->setAttachments($attachments);
$message->setRecipients($recipients);
$message->setBcc($bcc);

$sendMessageResponse = new Paubox\Mail\SendMessageResponse();
$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```


### Checking Email Dispositions

The SOURCE_TRACKING_ID of a message is returned in the response of the sendMessage method. To check the status for any email, use its source tracking id and call the getEmailDisposition method of Paubox:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$paubox = new Paubox\Paubox();

$resp = $paubox->getEmailDisposition('SOURCE_TRACKING_ID');
print_r($resp);
```

<a name="#paubox-forms"></a>
## Paubox Forms

`PauboxForms` provides access to the [Paubox Forms API](https://docs.paubox.com/forms/get-form). The public endpoints — getting a form definition and submitting a form response — are unauthenticated and need no credentials. The form-management endpoints (listing, creating, updating, archiving, and copying forms, plus retrieving submissions, stats, CSVs, and PDFs) require a scoped API key — see [Scoped API keys](#scoped-api-keys) below.

### Getting a form

Retrieve the full definition of a form (HTML, JSON schema, CSS, and metadata) by its UUID.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms();

$form = $forms->getForm('YOUR-FORM-UUID');
echo $form->title;       // "Patient Intake Form"
echo $form->form_html;   // "<form>...</form>"
print_r($form->form_json);
```

### Submitting a form

Build a `FormSubmission` with `form_data` matching the form's field schema and call `submitForm`. Returns `true` on success; throws an exception on failure.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms();

$submission = new Paubox\Forms\FormSubmission();
$submission->setFormData([
    'first_name' => 'Jane',
    'last_name'  => 'Smith',
    'email'      => 'jane@example.com',
]);

$result = $forms->submitForm('YOUR-FORM-UUID', $submission);
// $result === true on success
```

### Submitting a form with file attachments

Attachments must be base64-encoded. Maximum total request size is 250 MB.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms();

$attachment = new Paubox\Forms\FormAttachment();
$attachment->setName('consent.pdf');
$attachment->setContent(base64_encode(file_get_contents('/path/to/consent.pdf')));

$submission = new Paubox\Forms\FormSubmission();
$submission->setFormData(['first_name' => 'Jane', 'last_name' => 'Smith']);
$submission->setAttachments([$attachment]);

$result = $forms->submitForm('YOUR-FORM-UUID', $submission);
```

### Scoped API keys

All of the form-management methods below require a Paubox scoped API key with the `forms` scope. Generate one from your Paubox dashboard, then either pass it to the `PauboxForms` constructor or set it in the `PAUBOX_FORMS_API_KEY` environment variable. The SDK sends it as an `Authorization: Bearer` header.

**Important:** `PAUBOX_FORMS_API_KEY` is a *different* credential from the transactional Email API key (`PAUBOX_API_KEY`). Do not reuse the same value for both — the email client sends `PAUBOX_API_KEY` as `Token token=` to `api.paubox.net`, while `PauboxForms` sends `PAUBOX_FORMS_API_KEY` as `Authorization: Bearer` to `apx.paubox.com/forms`. Mixing them misdelivers a credential to the wrong endpoint. Configure both independently:

```bash
echo "export PAUBOX_API_USER='YOUR_EMAIL_LOGIN'"           >> .env
echo "export PAUBOX_API_KEY='YOUR_EMAIL_API_KEY'"          >> .env
echo "export PAUBOX_FORMS_API_KEY='YOUR_FORMS_SCOPED_KEY'" >> .env
```

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Option 1: pass the key directly
$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

// Option 2: use the PAUBOX_FORMS_API_KEY environment variable
$forms = new Paubox\PauboxForms();
```

Optionally, override the target endpoint with a constructor argument or the `PAUBOX_FORMS_BASE_URL` environment variable. Defaults to `https://apx.paubox.com/forms`:

```php
$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY', 'https://staging-forms.example.com');
```

If no key is configured, calling a management method throws a `Paubox\Forms\PauboxFormsException`. The public `getForm` and `submitForm` methods never require a key.

### Error handling

All Forms methods throw `Paubox\Forms\PauboxFormsException` on failure. The exception's message contains the operation and HTTP status only — the raw response body is not included in the message (form submission bodies can contain PHI). Read `getStatusCode()`, `getUrl()`, and `getResponseBody()` explicitly if you need the details:

```php
try {
    $forms->updateForm($formId, ['title' => $newTitle]);
} catch (Paubox\Forms\PauboxFormsException $e) {
    error_log('Forms update failed: ' . $e->getMessage());
    // Read the body explicitly — do NOT log $e->getMessage() alone if
    // the caller might echo the exception unfiltered.
    if ($e->getStatusCode() === 403) {
        // handle forbidden
    }
}
```

### Requirements

- PHP 8.1 or newer (older versions cannot install because of an unrelated advisory on the vendored HTTP client).
- The package pins its HTTP dependency (`nategood/httpful ^1.0`) and enables strict TLS certificate verification.

### Getting a form by ID

Retrieve a form through the authenticated management endpoint. Unlike the public `getForm`, this also returns inactive and archived forms.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$form = $forms->getFormById('YOUR-FORM-UUID');
echo $form->title;
echo $form->active;
```

### Listing forms

List your forms with optional filtering, searching, ordering, and pagination. Include `customer_id` (your Paubox customer ID) — the API rejects list requests that don't name the customer the key belongs to. Booleans are handled for you — pass real `true`/`false` values.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$result = $forms->listForms([
    'customer_id' => 12345,        // your Paubox customer ID
    'search'   => 'intake',        // match against form titles
    'archived' => false,
    'active'   => true,
    'order'    => 'desc',          // "asc" or "desc"
    'order_by' => 'updated_at',    // "title", "updated_at", "submission_count", or "created_at"
    'page'     => 1,               // 1-based
    'items'    => 25,              // max 100
]);

foreach ($result->results as $form) {
    echo $form->title . "\n";
}
print_r($result->page_info); // count, pages, page, items
```

### Creating a form

Build a `Forms\Form` and call `createForm`. `title`, `formJson`, and `customerId` are required; the other setters are optional. Returns the new form's UUID.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$form = new Paubox\Forms\Form();
$form->setTitle('Patient Intake Form');
$form->setFormJson(['fields' => [['name' => 'first_name', 'type' => 'text']]]);
$form->setCustomerId(12345);
$form->setDescription('Collects new patient information');
$form->setRecipient('intake@yourclinic.com');
$form->setActive(true);

$newFormId = $forms->createForm($form);
echo $newFormId; // "NEW-FORM-UUID"
```

### Updating a form

Partial update — pass an associative array with only the attributes you want to change. Allowed keys: `title`, `description`, `form_json`, `vanity_url`, `recipient`, `active`, `subscription_list_id`. Omitted keys are left unchanged.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$response = $forms->updateForm('YOUR-FORM-UUID', [
    'title'  => 'Patient Intake Form (v2)',
    'active' => false,
]);
echo $response->detail; // "Form updated successfully"
```

### Archiving and unarchiving a form

Both return `true` on success and throw an exception on failure.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$forms->archiveForm('YOUR-FORM-UUID');
$forms->unarchiveForm('YOUR-FORM-UUID');
```

### Copying a form

Duplicate an existing form under a new title. Returns the full new form object.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$newForm = $forms->copyForm('SOURCE-FORM-UUID', 'Patient Intake Form (Copy)');
echo $newForm->id;
echo $newForm->title;
```

### Form statistics

Get aggregate stats for your forms. `customer_id` is optional and defaults to the customer associated with your API key.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$stats = $forms->getFormStats();
// or: $stats = $forms->getFormStats(['customer_id' => 12345]);

echo $stats->active_form_count;
echo $stats->total_submission_count;
echo $stats->submissions_last_7_days;
```

### Listing submissions

List the submissions for a form with optional ordering and pagination.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$result = $forms->listSubmissions('YOUR-FORM-UUID', [
    'order'    => 'desc',          // "asc" or "desc"
    'order_by' => 'created_at',    // "submitter_email" or "created_at"
    'page'     => 1,
    'items'    => 25,              // max 100
]);

foreach ($result->data as $submission) {
    print_r($submission);
}
echo $result->total;
```

### Downloading submissions as CSV

`getSubmissionsCsv` returns every submission for a form as a CSV string; `getSubmissionCsv` returns a single submission. Save the result to a file or process it directly.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

// All submissions for a form
$csv = $forms->getSubmissionsCsv('YOUR-FORM-UUID');
file_put_contents('submissions.csv', $csv);

// A single submission
$csv = $forms->getSubmissionCsv('YOUR-FORM-UUID', 'SUBMISSION-ID');
file_put_contents('submission.csv', $csv);
```

### Downloading a submission as PDF

Returns the PDF as a binary string — write it straight to a file.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$forms = new Paubox\PauboxForms('YOUR-SCOPED-API-KEY');

$pdf = $forms->getSubmissionPdf('YOUR-FORM-UUID', 'SUBMISSION-ID');
file_put_contents('submission.pdf', $pdf);
```

<a name="#running-the-tests"></a>
## Running the tests

The default test suite is offline and read-only. Requires no credentials, no fixtures, no network:

```bash
$ vendor/bin/phpunit src/tests/PauboxFormsTest.php
```

Tests that hit the network or write to a real customer account are separated into groups and excluded by default:

- **`network`** — read-only tests that resolve a real API endpoint (e.g. the public `getForm` 404 path).
- **`mutating`** — creates, copies, archives, updates, or submits against a real customer. Restores state where possible (`try/finally`), but should never run against a production customer without explicit intent.

To run them:

```bash
$ export PAUBOX_FORMS_API_KEY='your-scoped-forms-key'
$ export PAUBOX_FORMS_BASE_URL='https://your-staging-endpoint'   # optional
$ export QA_TEST_FORM_UUID='...'         # a form on the target customer
$ export QA_TEST_SUBMISSION_UUID='...'   # a submission on that form
$ export QA_TEST_CUSTOMER_ID='...'       # numeric customer id owning them
$ vendor/bin/phpunit --group mutating src/tests/PauboxFormsTest.php
```

> :warning: The mutating group writes to whatever host `PAUBOX_FORMS_BASE_URL` points at (defaults to production). Every test attempts to restore state in a `finally` block, but a crash between an archive and a restore leaves the form archived — verify the test customer is a QA account before running.

<a name="#contributing"></a>
## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/paubox/paubox-php.


<a name="#license"></a>
## License

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

## Copyright
Copyright &copy; 2021, Paubox, Inc.
## 💬 Community & support

Questions, ideas, or want to share what you built? Join the **[Paubox Community](https://github.com/Paubox/community/discussions)** — the single home for discussions across every Paubox SDK and API.

🔐 Found a security issue? Email **devops@paubox.com** — please don't post it publicly.
