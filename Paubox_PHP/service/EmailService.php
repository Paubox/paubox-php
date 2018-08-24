<?php
namespace service;

use Model\Message;
use data\GetEmailDispositionResponse;

include_once 'service/ApiHelper.php';
include_once 'data/GetEmailDispositionResponse.php';

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
    
    public function SendEmail(Message $message)    
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
                    
            $jsonData = array (
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
                        'attachments' => array ($message->getAttachments())
                        /* 'attachments' => array ( */
                            /* array(
                                'fileName' => $attachment->getFileName(),                            
                                'contentType' => $attachment->getContentType(),
                                "content" => $attachment->getContent()
                            ) 
                        ),    */                    
                    )
                )
            );
                    
            $api = new \ApiHelper();
            $resp = $api->callToAPIByPost("https://api.paubox.net/v1/undefeatedgames/messages", "Token token=6f7c0110a47f82e7bff933f68cc8d7ec", $jsonData);
            return $resp;
            
        } catch (\Exception $e) {
            throw $e;
        }
        
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