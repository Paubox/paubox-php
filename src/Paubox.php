<?php

namespace Paubox;

use Paubox\Mail\SendMessageResponse;

class Paubox
{
    private function getURL($uri)
    {
        $base_url = "https://api.paubox.net/v1/";
        $base_url .= \getenv('PAUBOX_API_USER');
        $base_url .= "/";
        $base_url .= $uri;
        return $base_url;
    }
    
    private function getAuthentication()
    {
        $token = "Token token=";
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
    
    public function sendMessage(Mail\Message $message)
    {
        $encodedHtmlText= null;
        try {
            $header = $message->getHeader();
            $content = $message->getContent();
            $attachment = $message->getAttachments();
            
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
            
            $htmlText = $content->getHtmlText();
            if(isset($htmlText))  // if html text is not null or empty, convert it to base 64 string.
            {
                $encodedHtmlText = base64_encode($htmlText);
            }
            
            $forceSecureNotificationValue = Paubox::returnForceSecureNotificationValue($message->getForceSecureNotification());
            
            if (isset($forceSecureNotificationValue)) // if $forceSecureNotificationValue is not null or empty, pass forceSecureNotification value in request
            {
                $jsonRequestData = array(
                    'data' => array(
                        'message' => array(
                            'recipients' => $message->getRecipients(),
                            'cc' => $message->getCc(),
                            'bcc' => $message->getBcc(),
                            'headers' => array(
                                'subject' => $header->getSubject(),
                                'from' => $header->getFrom(),
                                'reply-to' => $header->getReplyTo()
                            ),
                            'allowNonTLS' => $message->isAllowNonTLS(),
                            'forceSecureNotification' => $forceSecureNotificationValue,
                            'content' => array(
                                'text/plain' => $content->getPlainText(),
                                'text/html' => $encodedHtmlText
                            ),
                            'attachments' => $jsonAttachmentsArray
                        )
                    )
                );
            }
            else
            {
                $jsonRequestData = array(
                    'data' => array(
                        'message' => array(
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
                        )
                    )
                );
            }
            
            $uri = "messages";
            
            $api = new Service\ApiHelper();
            $resp = $api->callToAPIByPost(Paubox::getURL($uri), Paubox::getAuthentication(), $jsonRequestData);
            $sendMessageResponse = new SendMessageResponse();
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