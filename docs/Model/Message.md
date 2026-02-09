# # Message

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**recipients** | **string[]** |  |
**bcc** | **string[]** |  | [optional]
**cc** | **string[]** |  | [optional]
**headers** | [**\Paubox\Model\MessageHeaders**](MessageHeaders.md) |  |
**allow_non_tls** | **bool** | Allow delivery over non-TLS rather than converting to a Secure Portal message. Not HIPAA-compliant if the message contains PHI. | [optional] [default to false]
**force_secure_notification** | **bool** | Force delivery as a Paubox Secure Message; recipient gets a pickup notification with a link. | [optional] [default to false]
**content** | [**\Paubox\Model\MessageContent**](MessageContent.md) |  |
**attachments** | [**\Paubox\Model\Attachment[]**](Attachment.md) |  | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
