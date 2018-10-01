# Paubox PHP

This is the official PHP wrapper for the [Paubox Transactional Email API](https://www.paubox.com/solutions/email-api). It is currently in alpha development.

The Paubox Transactional Email API allows your application to send secure, HIPAA-compliant email via Paubox and track deliveries and opens.
The API wrapper allows you to construct and send messages.

# Table of Contents
* [Installation](#installation)
*  [Usage](#usage)
*  [Contributing](#contributing)
*  [License](#license)

<a name="#installation"></a>
## Installation

Using composer:

Add Paubox to your composer.json file.

```json
{
  "require": {
    "paubox/paubox-php": "~1"
  }
}
```

### Getting Paubox API Credentials

You will need to have a Paubox account. You can [sign up here](https://www.paubox.com/join/see-pricing?unit=messages).

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

### Adding Attachments and Additional Headers


```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

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

$paubox = new Paubox\Paubox();

$resp = $paubox->getEmailDisposition('SOURCE_TRACKING_ID');
print_r($resp);
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
Copyright &copy; 2018, Paubox Inc.
