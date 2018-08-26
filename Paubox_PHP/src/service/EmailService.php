<?php
namespace service;

use data\Message;
use data\GetEmailDispositionResponse;
use data\SendMessageResponse;

include_once 'service/ApiHelper.php';
include_once 'data/GetEmailDispositionResponse.php';
include_once 'data/SendMessageResponse.php';

class EmailService
{
    private function getURL($uri){
        $baseUrl="https://api.paubox.net/v1/";
        $baseUrl .= \Constants::$API_USER;
        $baseUrl .="/";
        $baseUrl .= $uri;
        return $baseUrl;
    }
    
    private function getAuthentication(){
        $token="Token token=";
        $token .= \Constants::$API_KEY;
        
        return $token;
    }
    
    public function SendMessage(Message $message)
    {
        try
        {
            $header = $message->getHeader();
            $content = $message->getContent();
            $attachment = $message->getAttachments();
            
            if($header==null)
                throw new \Exception("Message Header cannot be null.");
                
                if($content==null)
                    throw new \Exception("Message Content cannot be null.");
                    
                    $jsonAttachmentsArray = array();
                    
                    foreach ($message->getAttachments() as $attachment)
                    {
                        $jsonattachment= array(
                            'fileName' => $attachment->getFileName(),
                            'contentType' => $attachment->getContentType(),
                            'content' => $attachment->getContent()
                        );
                        array_push($jsonAttachmentsArray, $jsonattachment);
                    }
                    
                    
                    $jsonRequestData = array (
                        'data' => array (
                            'message' => array (
                                'recipients' => $message->getRecipients(),
                                'bcc' => $message->getBcc(),
                                'headers' => array (
                                    'subject' => $header->getSubject(),
                                    'from' => $header->getFrom(),
                                    'reply-to' => $header->getReplyTo()
                                ),
                                'allowNonTLS' => $message->isAllowNonTLS(),
                                'content' => array (
                                    'text/plain' => $content->getPlainText(),
                                    'text/html' =>  $content->getHtmlText(),
                                ),
                                'attachments' => $jsonAttachmentsArray
                            )
                        )
                    );
                    $uri = "messages";
                    
                    $api = new \ApiHelper();
                    $resp = $api->callToAPIByPost(EmailService::getURL($uri), EmailService::getAuthentication(), $jsonRequestData);
                    
                    $sendMessageResponse = new SendMessageResponse();
                    $sendMessageResponse = json_decode($resp);
                    
                    if(is_null($sendMessageResponse) && is_null($sendMessageResponse->data) &&
                        is_null($sendMessageResponse->sourceTrackingId) &&
                        is_null($sendMessageResponse->errors))
                    {
                        throw new \Exception($resp);
                    }
        }
        catch (\Exception $e) {
            throw $e;
        }
        return $sendMessageResponse;
        
    }      

    function getEmailDisposition($sourceTrackingId)
    {
        $api = new \ApiHelper();
        $uri = "message_receipt?sourceTrackingId=";
        $uri .= $sourceTrackingId;
        $resp = $api->callToAPIByGet(EmailService::getURL($uri), EmailService::getAuthentication());
        $emailDisposition = new GetEmailDispositionResponse();
        $emailDisposition = json_decode($resp);
        if(is_null($emailDisposition) && is_null($emailDisposition->data) &&
            is_null($emailDisposition->sourceTrackingId) && is_null($emailDisposition->errors)) {
                throw new \Exception();
            }
            
            if (! is_null($emailDisposition) &&  isset($emailDisposition->data) && ! is_null($emailDisposition->data->message)) {
                $messageDeliveries = array($emailDisposition->data->message->message_deliveries);
                
                if (! is_null($messageDeliveries) && ! empty($messageDeliveries)) {
                    
                    for ($i = 0; $i < sizeof($messageDeliveries); $i ++) {
                        
                        $obj=$messageDeliveries[$i];
                        
                        foreach ($obj as $value) {
                            $value->status->openStatus = "unopened";
                        }
                    }
                }
            }
            return $emailDisposition;
    }
}


?>