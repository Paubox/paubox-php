# # MessageReceiptResponseDataMessage

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The message ID |
**message_deliveries** | [**\Paubox\Model\MessageDelivery[]**](MessageDelivery.md) |  |
**total_opens** | **int** | Total number of opens (single recipient only) | [optional]
**distinct_opens** | **int** | Number of distinct opens (single recipient only) | [optional]
**total_click_count** | **int** | Total number of clicks (single recipient only) | [optional]
**clicks_per_link** | [**\Paubox\Model\ClickData[]**](ClickData.md) | Click tracking data per link (single recipient only) | [optional]
**unsubscribed** | **bool** | Whether the recipient has unsubscribed (single recipient only) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
