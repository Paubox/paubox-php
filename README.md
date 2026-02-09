<img src="https://avatars.githubusercontent.com/u/22528478?s=200&v=4" alt="Paubox" width="150px">

# Paubox PHP

This is the official **Paubox PHP** for the [Paubox Email API](https://www.paubox.com/solutions/email-api).

The Paubox Email API allows your application to send secure, HIPAA compliant email via [Paubox](https://www.paubox.com) and track deliveries and opens.

# Table of Contents
* [Installation](#installation)
* [Usage](#usage)
* [Contributing](#contributing)
* [License](#license)

## External Resources
* [Documentation](https://docs.paubox.com/)
* [Quickstart Guide](https://docs.paubox.com/paubox_email_api/docs/quickstart/)

## Installation

### Getting Paubox API Credentials
You will need to have a Paubox account. You can [sign up here](https://www.paubox.com/pricing/paubox-email-api).

Once you have an account, follow the instructions on the REST API dashboard to verify domain ownership and generate API keys. Further [quickstart instructions for this process can be found here.](https://docs.paubox.com/paubox_email_api/docs/quickstart/)

### Install Package

Using Composer:

```
composer require paubox/paubox-php
```

Or add to your `composer.json`:

```json
{
    "require": {
        "paubox/paubox-php": "^2.0"
    }
}
```

Then run:

```
composer install
```

## Usage

Please refer to the [API Endpoints](#documentation-for-api-endpoints) section below for detailed usage of each endpoint.

For full API documentation and additional examples, visit [docs.paubox.com](https://docs.paubox.com/).

---

## API Endpoints

All URIs are relative to *https://api.paubox.net/v1/YOUR_API_USERNAME*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*DynamicTemplatesApi* | [**createDynamicTemplate**](docs/Api/DynamicTemplatesApi.md#createdynamictemplate) | **POST** /dynamic_templates | Create a dynamic template
*DynamicTemplatesApi* | [**deleteDynamicTemplate**](docs/Api/DynamicTemplatesApi.md#deletedynamictemplate) | **DELETE** /dynamic_templates/{id} | Delete a dynamic template
*DynamicTemplatesApi* | [**getDynamicTemplate**](docs/Api/DynamicTemplatesApi.md#getdynamictemplate) | **GET** /dynamic_templates/{id} | Get a dynamic template
*DynamicTemplatesApi* | [**listDynamicTemplates**](docs/Api/DynamicTemplatesApi.md#listdynamictemplates) | **GET** /dynamic_templates | List all dynamic templates
*DynamicTemplatesApi* | [**sendTemplatedMessage**](docs/Api/DynamicTemplatesApi.md#sendtemplatedmessage) | **POST** /templated_messages | Send a dynamically templated message
*DynamicTemplatesApi* | [**updateDynamicTemplate**](docs/Api/DynamicTemplatesApi.md#updatedynamictemplate) | **PATCH** /dynamic_templates/{id} | Update a dynamic template
*MessagesApi* | [**getMessageReceipt**](docs/Api/MessagesApi.md#getmessagereceipt) | **GET** /message_receipt | Get email disposition
*MessagesApi* | [**sendBulkMessages**](docs/Api/MessagesApi.md#sendbulkmessages) | **POST** /bulk_messages | Send multiple email messages (batch)
*MessagesApi* | [**sendMessage**](docs/Api/MessagesApi.md#sendmessage) | **POST** /messages | Send a single email message

## Models

- [Attachment](docs/Model/Attachment.md)
- [BulkSendRequest](docs/Model/BulkSendRequest.md)
- [BulkSendRequestData](docs/Model/BulkSendRequestData.md)
- [BulkSendResponse](docs/Model/BulkSendResponse.md)
- [BulkSendResponseMessagesInner](docs/Model/BulkSendResponseMessagesInner.md)
- [ClickData](docs/Model/ClickData.md)
- [DeleteDynamicTemplate200Response](docs/Model/DeleteDynamicTemplate200Response.md)
- [DeliveryStatus](docs/Model/DeliveryStatus.md)
- [DynamicTemplateListResponse](docs/Model/DynamicTemplateListResponse.md)
- [DynamicTemplateResponse](docs/Model/DynamicTemplateResponse.md)
- [ErrorResponse](docs/Model/ErrorResponse.md)
- [ErrorResponseErrorsInner](docs/Model/ErrorResponseErrorsInner.md)
- [Message](docs/Model/Message.md)
- [MessageContent](docs/Model/MessageContent.md)
- [MessageDelivery](docs/Model/MessageDelivery.md)
- [MessageHeaders](docs/Model/MessageHeaders.md)
- [MessageReceiptErrorResponse](docs/Model/MessageReceiptErrorResponse.md)
- [MessageReceiptResponse](docs/Model/MessageReceiptResponse.md)
- [MessageReceiptResponseData](docs/Model/MessageReceiptResponseData.md)
- [MessageReceiptResponseDataMessage](docs/Model/MessageReceiptResponseDataMessage.md)
- [SingleSendRequest](docs/Model/SingleSendRequest.md)
- [SingleSendRequestData](docs/Model/SingleSendRequestData.md)
- [SingleSendResponse](docs/Model/SingleSendResponse.md)
- [TemplatedMessage](docs/Model/TemplatedMessage.md)
- [TemplatedMessageHeaders](docs/Model/TemplatedMessageHeaders.md)
- [TemplatedMessageRequest](docs/Model/TemplatedMessageRequest.md)
- [TemplatedMessageRequestData](docs/Model/TemplatedMessageRequestData.md)

Authentication schemes defined for the API:
### PauboxToken

- **Type**: API key
- **API key parameter name**: Authorization
- **Location**: HTTP header


## Tests

To run the tests, use:

```bash
composer install
vendor/bin/phpunit
```



## About this package

This PHP package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: `1.0.0`
    - Package version: `2.0.0`
    - Generator version: `7.19.0`
- Build package: `org.openapitools.codegen.languages.PhpClientCodegen`


## Contributing

The Paubox PHP is maintained by [Paubox, Inc.](https://www.paubox.com)

We want to empower our users building applications with the Paubox Email API, and so we encourage you to file bug reports/create GitHub issues and pull requests. Chances are other developers using our Email API might be having similar ideas about new features or approaches to improving the SDK, so we encourage you to upvote or comment on existing issues or pull requests!

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
Copyright &copy; 2026, Paubox, Inc.
