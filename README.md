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

$dotenv = Dotenv\Dotenv::create(__DIR__);
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

$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```

### Allowing non-TLS message delivery

If you want to send non-PHI mail that does not need to be HIPAA-compliant, you can allow the message delivery to take place even if a TLS connection is unavailable.

This means the message will not be converted into a secure portal message when a non-TLS connection is encountered. To allow a non-TLS message delivery, call the `setAllowNonTLS(true)` method on the message object.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
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

$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```

### Forcing Secure Notifications
Paubox Secure Notifications allow an extra layer of security, especially when coupled with an organization's requirement for message recipients to use 2-factor authentication to read messages (this setting is available to org administrators in the Paubox Admin Panel).

Instead of receiving an email with the message contents, the recipient will receive a notification email that they have a new message in Paubox.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
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

$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```


### Adding Attachments and Additional Headers


```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
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

$sendMessageResponse = $paubox->sendMessage($message);
print_r($sendMessageResponse);
```


### Checking Email Dispositions

The SOURCE_TRACKING_ID of a message is returned in the response of the sendMessage method. To check the status for any email, use its source tracking id and call the getEmailDisposition method of Paubox:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$paubox = new Paubox\Paubox();

$resp = $paubox->getEmailDisposition('SOURCE_TRACKING_ID');
print_r($resp);
```

<a name="#paubox-forms"></a>
## Paubox Forms

`PauboxForms` provides access to the [Paubox Forms API](https://docs.paubox.com/forms/get-form). No API credentials are required — these endpoints are unauthenticated.

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
