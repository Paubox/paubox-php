<?php

namespace Paubox;

class Paubox
{
    private function getURL($uri)
    {
        $base_url = "https://api.paubox.com/v1/email/";
        $base_url .= $uri;
        return $base_url;
    }

    private function getAuthentication()
    {
        $token = "Bearer ";
        $token .= \getenv('PAUBOX_API_KEY');
        return $token;
    }
    
    // Returns ForceSecureNotification valid value.
    private function returnForceSecureNotificationValue($forceSecureNotification)
    {
        $forceSecureNotificationValue = null;
        if ($forceSecureNotification == null || $forceSecureNotification == "") {
            return null;
        } else {
            $forceSecureNotificationValue = strtolower(trim($forceSecureNotification));
            if ($forceSecureNotificationValue == "true") {
                return true;
            } else if ($forceSecureNotificationValue == "false") {
                return false;
            } else {
                return null;
            }
        }
    }
    
    private function buildMessageData(Mail\Message $message)
    {
        $header = $message->getHeader();
        $content = $message->getContent();

        if ($header == null)
            throw new \Exception("Message Header cannot be null.");
        if ($content == null)
            throw new \Exception("Message Content cannot be null.");

        $jsonAttachmentsArray = array();
        foreach ($message->getAttachments() as $attachment) {
            $jsonAttachment = array(
                'fileName' => $attachment->getFileName(),
                'contentType' => $attachment->getContentType(),
                'content' => $attachment->getContent()
            );
            array_push($jsonAttachmentsArray, $jsonAttachment);
        }

        $encodedHtmlText = null;
        $htmlText = $content->getHtmlText();
        if (isset($htmlText)) {
            $encodedHtmlText = base64_encode($htmlText);
        }

        $messageData = array(
            'recipients' => $message->getRecipients(),
            'cc' => $message->getCc(),
            'bcc' => $message->getBcc(),
            'headers' => array(
                'subject' => $header->getSubject(),
                'from' => $header->getFrom(),
                'reply-to' => $header->getReplyTo()
            ),
            'allowNonTLS' => $message->isAllowNonTLS(),
            'content' => array(
                'text/plain' => $content->getPlainText(),
                'text/html' => $encodedHtmlText
            ),
            'attachments' => $jsonAttachmentsArray
        );

        $forceSecureNotificationValue = Paubox::returnForceSecureNotificationValue($message->getForceSecureNotification());
        if (isset($forceSecureNotificationValue)) {
            $messageData['forceSecureNotification'] = $forceSecureNotificationValue;
        }

        return $messageData;
    }

    public function sendMessage(Mail\Message $message)
    {
        try {
            $messageData = $this->buildMessageData($message);

            $jsonRequestData = array(
                'data' => array(
                    'message' => $messageData
                )
            );

            $api = new Service\ApiHelper();
            $resp = $api->callToAPIByPost(Paubox::getURL("messages"), Paubox::getAuthentication(), $jsonRequestData);
            $sendMessageResponse = new Mail\SendMessageResponse();
            $sendMessageResponse = json_decode($resp);
            if (is_null($sendMessageResponse) && is_null($sendMessageResponse->data) && is_null($sendMessageResponse->sourceTrackingId) && is_null($sendMessageResponse->errors))
            {
                throw new \Exception($resp);
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $sendMessageResponse;
    }

    public function scheduleMessage(Mail\Message $message, $scheduledAt)
    {
        try {
            $messageData = $this->buildMessageData($message);

            $jsonRequestData = array(
                'data' => array(
                    'message' => $messageData,
                    'scheduled_at' => $scheduledAt
                )
            );

            $api = new Service\ApiHelper();
            $resp = $api->callToAPIByPost(Paubox::getURL('schedule'), Paubox::getAuthentication(), $jsonRequestData);
            return json_decode($resp);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getScheduledMessage($sourceTrackingId)
    {
        $api = new Service\ApiHelper();
        $uri = "schedule/" . $sourceTrackingId;
        $resp = $api->callToAPIByGet(Paubox::getURL($uri), Paubox::getAuthentication());
        return json_decode($resp);
    }

    public function rescheduleMessage($sourceTrackingId, $scheduledAt)
    {
        $api = new Service\ApiHelper();
        $uri = "schedule/" . $sourceTrackingId;
        $requestBody = array('scheduled_at' => $scheduledAt);
        $resp = $api->callToAPIByPatch(Paubox::getURL($uri), Paubox::getAuthentication(), $requestBody);
        return json_decode($resp);
    }

    public function cancelScheduledMessage($sourceTrackingId)
    {
        $api = new Service\ApiHelper();
        $uri = "schedule/" . $sourceTrackingId . "/cancel";
        $resp = $api->callToAPIByPost(Paubox::getURL($uri), Paubox::getAuthentication(), array());
        return json_decode($resp);
    }

    function getEmailDisposition($sourceTrackingId)
    {
        $api = new Service\ApiHelper();
        $uri = "message_receipt?sourceTrackingId=";
        $uri .= $sourceTrackingId;
        $resp = $api->callToAPIByGet(Paubox::getURL($uri), Paubox::getAuthentication());
        $emailDisposition = new Mail\GetEmailDispositionResponse();
        $emailDisposition = json_decode($resp);
        if (is_null($emailDisposition) && is_null($emailDisposition->data) && is_null($emailDisposition->sourceTrackingId) && is_null($emailDisposition->errors)) {
            throw new \Exception();
        }
        return $emailDisposition;
    }
}
?>