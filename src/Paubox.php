<?php

use Paubox\Mail\Message;
use Paubox\Mail\GetEmailDispositionResponse;
use Paubox\Mail\SendMessageResponse;
include_once __DIR__ . '/service/ApiHelper.php';
include_once __DIR__ . '/mail/GetEmailDispositionResponse.php';
include_once __DIR__ . '/mail/SendMessageResponse.php';

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

    public function sendMessage(Message $message)
    {
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

            $jsonRequestData = array(
                'data' => array(
                    'message' => array(
                        'recipients' => $message->getRecipients(),
                        'bcc' => $message->getBcc(),
                        'headers' => array(
                            'subject' => $header->getSubject(),
                            'from' => $header->getFrom(),
                            'reply-to' => $header->getReplyTo()
                        ),
                        'allowNonTLS' => $message->isAllowNonTLS(),
                        'content' => array(
                            'text/plain' => $content->getPlainText(),
                            'text/html' => $content->getHtmlText()
                        ),
                        'attachments' => $jsonAttachmentsArray
                    )
                )
            );
            $uri = "messages";

            $api = new \ApiHelper();
            $resp = $api->callToAPIByPost(Paubox::getURL($uri), Paubox::getAuthentication(), $jsonRequestData);

            $sendMessageResponse = new SendMessageResponse();
            $sendMessageResponse = json_decode($resp);

            if (is_null($sendMessageResponse) && is_null($sendMessageResponse->data) && is_null($sendMessageResponse->sourceTrackingId) && is_null($sendMessageResponse->errors)) {
                throw new \Exception($resp);
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $sendMessageResponse;
    }

    function getEmailDisposition($sourceTrackingId)
    {
        $api = new \ApiHelper();
        $uri = "message_receipt?sourceTrackingId=";
        $uri .= $sourceTrackingId;
        $resp = $api->callToAPIByGet(Paubox::getURL($uri), Paubox::getAuthentication());
        $emailDisposition = new GetEmailDispositionResponse();
        $emailDisposition = json_decode($resp);
        if (is_null($emailDisposition) && is_null($emailDisposition->data) && is_null($emailDisposition->sourceTrackingId) && is_null($emailDisposition->errors)) {
            throw new \Exception();
        }

        return $emailDisposition;
    }
}

?>