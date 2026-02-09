# # TemplatedMessageHeaders

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**subject** | **string** | Message subject (can include template variables) |
**from** | **string** | Must match the verified domain of your API key. |
**reply_to** | **string** | Reply-to address; must match a verified domain if different from from. | [optional]
**list_unsubscribe** | **string** | Insert a List-Unsubscribe header (mailto and/or http). See RFC guidance for syntax. | [optional]
**list_unsubscribe_post** | **string** | Used in conjunction with List-Unsubscribe header. | [optional]
**additional_properties** | **string** | Any additional custom header values. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
