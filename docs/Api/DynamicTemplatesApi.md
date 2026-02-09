# Paubox\DynamicTemplatesApi



All URIs are relative to https://api.paubox.net/v1/YOUR_API_USERNAME, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**createDynamicTemplate()**](DynamicTemplatesApi.md#createDynamicTemplate) | **POST** /dynamic_templates | Create a dynamic template |
| [**deleteDynamicTemplate()**](DynamicTemplatesApi.md#deleteDynamicTemplate) | **DELETE** /dynamic_templates/{id} | Delete a dynamic template |
| [**getDynamicTemplate()**](DynamicTemplatesApi.md#getDynamicTemplate) | **GET** /dynamic_templates/{id} | Get a dynamic template |
| [**listDynamicTemplates()**](DynamicTemplatesApi.md#listDynamicTemplates) | **GET** /dynamic_templates | List all dynamic templates |
| [**sendTemplatedMessage()**](DynamicTemplatesApi.md#sendTemplatedMessage) | **POST** /templated_messages | Send a dynamically templated message |
| [**updateDynamicTemplate()**](DynamicTemplatesApi.md#updateDynamicTemplate) | **PATCH** /dynamic_templates/{id} | Update a dynamic template |


## `createDynamicTemplate()`

```php
createDynamicTemplate($data_name, $data_body): \Paubox\Model\DynamicTemplateResponse
```

Create a dynamic template

Upload a new Handlebars template for dynamic content generation

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\DynamicTemplatesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$data_name = 'data_name_example'; // string | Name for the template
$data_body = '/path/to/file.txt'; // \SplFileObject | Handlebars template file (.hbs)

try {
    $result = $apiInstance->createDynamicTemplate($data_name, $data_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DynamicTemplatesApi->createDynamicTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **data_name** | **string**| Name for the template | |
| **data_body** | **\SplFileObject****\SplFileObject**| Handlebars template file (.hbs) | |

### Return type

[**\Paubox\Model\DynamicTemplateResponse**](../Model/DynamicTemplateResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteDynamicTemplate()`

```php
deleteDynamicTemplate($id): \Paubox\Model\DeleteDynamicTemplate200Response
```

Delete a dynamic template

Delete a specific dynamic template by ID

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\DynamicTemplatesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | Template ID to delete

try {
    $result = $apiInstance->deleteDynamicTemplate($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DynamicTemplatesApi->deleteDynamicTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Template ID to delete | |

### Return type

[**\Paubox\Model\DeleteDynamicTemplate200Response**](../Model/DeleteDynamicTemplate200Response.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDynamicTemplate()`

```php
getDynamicTemplate($id): \Paubox\Model\DynamicTemplateResponse
```

Get a dynamic template

Retrieve a specific dynamic template by ID

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\DynamicTemplatesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | Template ID

try {
    $result = $apiInstance->getDynamicTemplate($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DynamicTemplatesApi->getDynamicTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Template ID | |

### Return type

[**\Paubox\Model\DynamicTemplateResponse**](../Model/DynamicTemplateResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listDynamicTemplates()`

```php
listDynamicTemplates(): \Paubox\Model\DynamicTemplateListResponse
```

List all dynamic templates

Retrieve all dynamic templates for your organization

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\DynamicTemplatesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->listDynamicTemplates();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DynamicTemplatesApi->listDynamicTemplates: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Paubox\Model\DynamicTemplateListResponse**](../Model/DynamicTemplateListResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendTemplatedMessage()`

```php
sendTemplatedMessage($templated_message_request): \Paubox\Model\SingleSendResponse
```

Send a dynamically templated message

Send an email using a dynamic template with variable substitution

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\DynamicTemplatesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$templated_message_request = {"data":{"template_name":"detailed_test","template_values":"{ \"name\": \"Howard\", \"conditional\":\"true\",\"items\":[\"one\",\"two\",\"three\"] }","message":{"recipients":["recipient@host.com","Recipient Name <recipient2@host.com>"],"bcc":["recipient3@host.com","Recipient Name <recipient4@host.com>"],"headers":{"subject":"sample email","from":"sender@authorized_domain.com","reply-to":"Sender Name <sender@authorized_domain.com>"},"allowNonTLS":false,"forceSecureNotification":false,"attachments":[{"fileName":"hello_world.txt","contentType":"text/plain","content":"SGVsbG8gV29ybGQ"}]}}}; // \Paubox\Model\TemplatedMessageRequest

try {
    $result = $apiInstance->sendTemplatedMessage($templated_message_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DynamicTemplatesApi->sendTemplatedMessage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **templated_message_request** | [**\Paubox\Model\TemplatedMessageRequest**](../Model/TemplatedMessageRequest.md)|  | |

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

## `updateDynamicTemplate()`

```php
updateDynamicTemplate($id, $data_name, $data_body): \Paubox\Model\DynamicTemplateResponse
```

Update a dynamic template

Update an existing Handlebars template

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: PauboxToken
$config = Paubox\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Paubox\Configuration::getDefaultConfiguration()->setApiKeyPrefix('Authorization', 'Bearer');


$apiInstance = new Paubox\Api\DynamicTemplatesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | Template ID to update
$data_name = 'data_name_example'; // string | Updated name for the template
$data_body = '/path/to/file.txt'; // \SplFileObject | Updated Handlebars template file (.hbs)

try {
    $result = $apiInstance->updateDynamicTemplate($id, $data_name, $data_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DynamicTemplatesApi->updateDynamicTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Template ID to update | |
| **data_name** | **string**| Updated name for the template | [optional] |
| **data_body** | **\SplFileObject****\SplFileObject**| Updated Handlebars template file (.hbs) | [optional] |

### Return type

[**\Paubox\Model\DynamicTemplateResponse**](../Model/DynamicTemplateResponse.md)

### Authorization

[PauboxToken](../../README.md#PauboxToken)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
