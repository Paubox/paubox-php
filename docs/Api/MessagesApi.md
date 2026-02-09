# Paubox\MessagesApi

Send individual or bulk transactional email

All URIs are relative to https://api.paubox.net/v1/YOUR_API_USERNAME, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**getMessageReceipt()**](MessagesApi.md#getMessageReceipt) | **GET** /message_receipt | Get email disposition |
| [**sendBulkMessages()**](MessagesApi.md#sendBulkMessages) | **POST** /bulk_messages | Send multiple email messages (batch) |
| [**sendMessage()**](MessagesApi.md#sendMessage) | **POST** /messages | Send a single email message |


## `getMessageReceipt()`

```php
getMessageReceipt($source_tracking_id): \Paubox\Model\MessageReceiptResponse
```

Get email disposition

Retrieve delivery status, open tracking, and click tracking information for a sent message

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\MessagesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$source_tracking_id = 6e1cf9a4-7bde-4834-8200-ed424b50c8a7; // string | The tracking ID returned when the message was sent

try {
    $result = $apiInstance->getMessageReceipt($source_tracking_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MessagesApi->getMessageReceipt: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **source_tracking_id** | **string**| The tracking ID returned when the message was sent | |

### Return type

[**\Paubox\Model\MessageReceiptResponse**](../Model/MessageReceiptResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendBulkMessages()`

```php
sendBulkMessages($bulk_send_request): \Paubox\Model\BulkSendResponse
```

Send multiple email messages (batch)

Sends multiple messages in one request. Paubox recommends batches of 50 or fewer. Source tracking IDs are returned in the same order as the messages array.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\MessagesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$bulk_send_request = {"data":{"messages":[{"recipients":["recipient@host.com","Recipient Name <recipient@host.com>"],"bcc":["recipient3@host.com","Recipient Name <recipient4@host.com>"],"headers":{"subject":"sample email","from":"sender@authorized_domain.com","reply-to":"Sender Name <sender@authorized_domain.com>","x-custom-header":"sample custom header"},"content":{"text/plain":"Hello World!","text/html":"<html><body><h1>Hello World!</h1></body></html>"},"attachments":[{"fileName":"hello_world.txt","contentType":"text/plain","content":"SGVsbG8gV29ybGQh"}],"allowNonTLS":false,"forceSecureNotification":false},{"recipients":["recipient2@host.com","Recipient Name <recipient2@host.com>"],"headers":{"subject":"sample email","from":"sender@authorized_domain.com","reply-to":"Sender Name <sender@authorized_domain.com>"},"content":{"text/plain":"Hello World!","text/html":"<html><body><h1>Hello World!</h1></body></html>"},"attachments":[{"fileName":"hello_world.txt","contentType":"text/plain","content":"SGVsbG8gV29ybGQh"}]}]}}; // \Paubox\Model\BulkSendRequest

try {
    $result = $apiInstance->sendBulkMessages($bulk_send_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MessagesApi->sendBulkMessages: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **bulk_send_request** | [**\Paubox\Model\BulkSendRequest**](../Model/BulkSendRequest.md)|  | |

### Return type

[**\Paubox\Model\BulkSendResponse**](../Model/BulkSendResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendMessage()`

```php
sendMessage($single_send_request): \Paubox\Model\SingleSendResponse
```

Send a single email message

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\MessagesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$single_send_request = {"data":{"message":{"recipients":["recipient@host.com"],"headers":{"subject":"Hello from the Paubox Email API","from":"sender@verifieddomain.com"},"content":{"text/plain":"Hello world","text/html":"<html><body><h1>Hello world</h1></body></html>"}}}}; // \Paubox\Model\SingleSendRequest

try {
    $result = $apiInstance->sendMessage($single_send_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MessagesApi->sendMessage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **single_send_request** | [**\Paubox\Model\SingleSendRequest**](../Model/SingleSendRequest.md)|  | |

### Return type

[**\Paubox\Model\SingleSendResponse**](../Model/SingleSendResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
