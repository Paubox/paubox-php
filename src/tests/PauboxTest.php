<?php

use Paubox\DynamicTemplate;
use PHPUnit\Framework\TestCase;
use Paubox\Mail\GetEmailDispositionResponse;
use Paubox\Mail\SendMessageResponse;
use Paubox\Mail\Message;
use Paubox\Mail\Content;
use Paubox\Mail\Header;
use Paubox\Mail\Attachment;

require_once dirname (dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/Paubox.php";
require_once dirname(__DIR__) . "/mail/SendMessageResponse.php";
require_once dirname(__DIR__) . "/mail/Message.php";
require_once dirname(__DIR__) . "/mail/Content.php";
require_once dirname(__DIR__) . "/mail/Header.php";
require_once dirname(__DIR__) . "/mail/Attachment.php";
require_once __DIR__ . "/CsvFileIterator.php";

/**
 * Paubox test case.
 */
class PauboxTest extends TestCase
{

    private $paubox;
    private $pauboxApi;
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void    
    {
        $this->paubox = new Paubox\Paubox();
        $this->pauboxApi = new DynamicTemplate();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->paubox = null;
        $this->pauboxApi = null;
        parent::tearDown();
    }

    public function sendMessageDataProvider_Success()
    {
        $listMessages = array();
        $csvObj = new CsvFileIterator('.\SendMessage_TestData.csv');
        $csvObj->next();
        $csvObj->next(); // Skip headers from csv file
        for (; $csvObj->valid(); $csvObj->next()) {
            if ($csvObj->current()[15] != 'SUCCESS') // If Expected output is not Success , then skip the test data
                continue;

            $currentObj = $csvObj->current();
            $message = new Message();
            $messageArray = array();

            $content = new Content();
            $header = new Header();
            $attachment = new Attachment();

            $message->setRecipients([
                $currentObj[1]
            ]);
            $message->setBcc([
                $currentObj[2]
            ]);
            $message->setCc([
                $currentObj[14]
            ]);

            $header->setSubject($currentObj[3]);
            $header->setFrom($currentObj[4]);
            $header->setReplyTo($currentObj[5]);

            if (! is_null($currentObj[6]))
                $message->setAllowNonTLS(filter_var($currentObj[6], FILTER_VALIDATE_BOOLEAN));

            if (is_null($currentObj[7]))
                $content->setPlainText(null);
            else
                $content->setPlainText($currentObj[7]);

            if (is_null($currentObj[8]))
                $content->setHtmlText(null);
            else
                $content->setHtmlText($currentObj[8]);
            
            $message->setForceSecureNotification($currentObj[13]);

            $attachments = array();
            if (filter_var($currentObj[9], FILTER_VALIDATE_INT) > 0) {
                $attachment = new Attachment();
                $attachment->setFileName($currentObj[10]);
                $attachment->setContentType($currentObj[11]);
                $attachment->setContent($currentObj[12]);

                array_push($attachments, $attachment);
            }

            $message->setHeader($header);
            $message->setContent($content);
            $message->setAttachments($attachments);

            array_push($messageArray, $message); // Convert message object to array
            array_push($listMessages, $messageArray);
        }
        return $listMessages;
    }

    /**
     * Tests PauboxTest->sendMessage()
     *
     * @dataProvider sendMessageDataProvider_Success
     */
    public function testSendMessage_ReturnSuccess(Message $testMsg)
    {
        $actualResponse = new SendMessageResponse();
        $actualResponse = $this->paubox->sendMessage($testMsg);
        if (! is_null($actualResponse)) {
            if (isset($actualResponse->data) && ! is_null($actualResponse->data) && isset($actualResponse->sourceTrackingId) && ! is_null($actualResponse->sourceTrackingId)) {
                $this->assertTrue(true);
            } else {
                $this->fail();
            }
        } else {
            $this->fail();
        }
    }

    public function sendMessageDataProvider_Error()
    {
        $listMessages = array();
        $csvObj = new CsvFileIterator('.\SendMessage_TestData.csv');
        $csvObj->next();
        $csvObj->next(); // Skip headers from csv file
        for (; $csvObj->valid(); $csvObj->next()) {
            if ($csvObj->current()[15] != 'ERROR') // If Expected output is not Error , then skip the test data
                continue;

            $currentObj = $csvObj->current();
            $message = new Message();
            $messageArray = array();

            $content = new Content();
            $header = new Header();
            $attachment = new Attachment();

            $message->setRecipients([
                $currentObj[1]
            ]);
            $message->setBcc([
                $currentObj[2]
            ]);
            $message->setCc([
                $currentObj[14]
            ]);

            $header->setSubject($currentObj[3]);
            $header->setFrom($currentObj[4]);
            $header->setReplyTo($currentObj[5]);

            if (! is_null($currentObj[6]))
                $message->setAllowNonTLS(filter_var($currentObj[6], FILTER_VALIDATE_BOOLEAN));

            if (is_null($currentObj[7]))
                $content->setPlainText(null);
            else
                $content->setPlainText($currentObj[7]);

            if (is_null($currentObj[8]))
                $content->setHtmlText(null);
            else
                $content->setHtmlText($currentObj[8]);
            
            $message->setForceSecureNotification($currentObj[13]);

            $attachments = array();
            if (filter_var($currentObj[9], FILTER_VALIDATE_INT) > 0) {
                $attachment = new Attachment();
                $attachment->setFileName($currentObj[10]);
                $attachment->setContentType($currentObj[11]);
                $attachment->setContent($currentObj[12]);

                array_push($attachments, $attachment);
            }

            $message->setHeader($header);
            $message->setContent($content);
            $message->setAttachments($attachments);

            array_push($messageArray, $message); // Convert message object to array
            array_push($listMessages, $messageArray);
        }
        return $listMessages;
    }

    /**
     * Tests EmailService->SendMessage()
     *
     * @dataProvider sendMessageDataProvider_Error
     */
    public function testSendMessage_ReturnError(Message $testMsg)
    {
        $actualResponse = new SendMessageResponse();
        $actualResponse = $this->paubox->sendMessage($testMsg);

        if (! is_null($actualResponse)) {
            if (isset($actualResponse->errors) && ! is_null($actualResponse->errors) && count($actualResponse->errors) > 0) {
                $this->assertTrue(true);
            } else {
                $this->fail();
            }
        } else {
            $this->fail();
        }
    }

    public function getEmailDataProvider_Success()
    {
        return array(
            array(
                "ce1e2143-474d-43ba-b829-17a26b8005e5"
            ),
            array(
                "1aed91d1-f7ce-4c3d-8df2-85ecd225a7fc"
            )
        );
    }

    public function getEmailDataProvider_Error()
    {
        return array(
            array(
                null
            ),
            array(
                ""
            ),
            array(
                " "
            ),
            array(
                "151515215"
            )
        );
    }

    /**
     * Tests Paubox->getEmailDisposition()
     *
     * @dataProvider getEmailDataProvider_Success
     */
    public function testGetEmailDisposition_ReturnSuccess($sourceTrackingId)
    {
        $actualResponse = new GetEmailDispositionResponse();
        $actualResponse = $this->paubox->getEmailDisposition($sourceTrackingId);
        if (is_null($actualResponse) || is_null($actualResponse->data) || is_null($actualResponse->data->message) || is_null($actualResponse->data->message->id)) {
            $this->fail();
        } else if (isset($actualResponse->errors) && ! is_null($actualResponse->errors) && count($actualResponse->errors) > 0) {
            $this->fail();
        } else if (! is_null($actualResponse->data->message->message_deliveries) && count($actualResponse->data->message->message_deliveries) > 0) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }

    /**
     *
     * @dataProvider getEmailDataProvider_Error
     */
    public function testGetEmailDisposition_ReturnError($sourceTrackingId)
    {
        $actualResponse = new GetEmailDispositionResponse();
        $actualResponse = $this->paubox->getEmailDisposition($sourceTrackingId);
        if (is_null($actualResponse) || is_null($actualResponse->errors) || count($actualResponse->errors) <= 0) {
            $this->fail();
        } else {
            if (is_null($actualResponse->errors[0]->title)) {
                $this->fail();
            } else {
                $this->assertTrue(true);
            }
        }
    }

    public function testCreateDynamicTemplate()
    {
        $templateName = 'template_name';
        $templateFilePath = 'temp.txt';

        $response = $this->pauboxApi->createDynamicTemplate($templateName, $templateFilePath);

        $this->assertNotEmpty($response);

        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
        $this->assertEquals('Template template_name created!', $responseArray['message']);

        $params = $responseArray['params'];
        $this->assertArrayHasKey('name', $params);
        $this->assertEquals($templateName, $params['name']);
    }

    public function testUpdateDynamicTemplate()
    {
        $templateName = 'template_name_New';
        $templateFilePath = 'temp.txt';

        $response = $this->pauboxApi->updateDynamicTemplate("724", $templateName, $templateFilePath);

        $this->assertNotEmpty($response);

        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
        $this->assertEquals("Template $templateName updated!", $responseArray['message']);

        $params = $responseArray['params'];
        $this->assertArrayHasKey('name', $params);
        $this->assertEquals($templateName, $params['name']);
    }

    public function testDeleteDynamicTemplate()
    {

        $response = $this->pauboxApi->deleteDynamicTemplate("743");

        $this->assertNotEmpty($response);
        $responseArray = json_decode($response, true);
        $this->assertArrayHasKey('message', $responseArray);
    }

    public function testGetAllDynamicTemplates()
    {

        $response = $this->pauboxApi->getAllDynamicTemplates();

        $responseArray = json_decode($response, true);

        $this->assertTrue(is_array($responseArray));
        $this->assertGreaterThan(0, count($responseArray));
        $this->assertArrayHasKey('id', $responseArray[0]);
        $this->assertArrayHasKey('name', $responseArray[0]);
        $this->assertArrayHasKey('api_customer_id', $responseArray[0]);
    }

    public function testGetDynamicTemplate()
    {
        $templateId = '669';
        $response = $this->pauboxApi->getDynamicTemplate($templateId);
        $responseArray = json_decode($response, true);

        $this->assertArrayHasKey('id', $responseArray);
        $this->assertArrayHasKey('name', $responseArray);
        $this->assertArrayHasKey('api_customer_id', $responseArray);
        $this->assertArrayHasKey('body', $responseArray);
        $this->assertArrayHasKey('created_at', $responseArray);
        $this->assertArrayHasKey('updated_at', $responseArray);
    }

    public function testCreateWebhookEndpoint()
    {
        $targetUrl = 'https://example.com/webhook';
        $events = ['message_received', 'message_bounced'];
        $active = true;
        $signingKey = 'signing_key';
        $apiKey = 'api_key';

        $response = $this->webhookEndpoint->createWebhookEndpoint($targetUrl, $events, $active, $signingKey, $apiKey);

        $this->assertNotEmpty($response);

        $responseArray = json_decode($response, true);
        $this->assertEquals("Webhook created!", $responseArray['message']);
        $this->assertArrayHasKey('target_url', $responseArray);
        $this->assertEquals($targetUrl, $responseArray['target_url']);
        $this->assertArrayHasKey('events', $responseArray);
        $this->assertEquals($events, $responseArray['events']);
        $this->assertArrayHasKey('active', $responseArray);
        $this->assertEquals($active, $responseArray['active']);
        $this->assertArrayHasKey('signing_key', $responseArray);
        $this->assertEquals($signingKey, $responseArray['signing_key']);
        $this->assertArrayHasKey('api_key', $responseArray);
        $this->assertEquals($apiKey, $responseArray['api_key']);


        $deleteresponse = $this->webhookEndpoint->deleteDynamicTemplate($responseArray['id']);
        $deleteresponseArray = json_decode($deleteresponse, true);
        $this->assertNotEmpty($deleteresponseArray);
        $deleteresponseArray = json_decode($deleteresponseArray, true);
        $this->assertArrayHasKey('message', $deleteresponseArray);
        $this->assertEquals("Webhook deleted!", $deleteresponseArray['message']);

    }

    public function testUpdateWebhookEndpoint()
    {
        
        $targetUrl = 'https://example.com/';
        $events = ['message_received'];
        $active = false;
        $signingKey = 'new_signing_key';
        $apiKey = 'new_api_key';

        $Createresponse = $this->webhookEndpoint->createWebhookEndpoint($targetUrl, $events, $active, $signingKey, $apiKey);
        $CreateresponseArray = json_decode($Createresponse, true); 

        $this->assertEquals("Webhook created!", $CreateresponseArray['message']);

        $endpointId = $CreateresponseArray['id'];

        $targetUrl = 'https://example.com/new-webhook';

        $response = $this->webhookEndpoint->updateWebhookEndpoint($endpointId, $targetUrl, $events, $active, $signingKey, $apiKey);

        $this->assertNotEmpty($response);

        $responseArray = json_decode($response, true);

        $this->assertEquals("Webhook updated!", $responseArray['message']);
        $this->assertArrayHasKey('target_url', $responseArray);
        $this->assertEquals($targetUrl, $responseArray['target_url']);
        $this->assertArrayHasKey('events', $responseArray);
        $this->assertEquals($events, $responseArray['events']);
        $this->assertArrayHasKey('active', $responseArray);
        $this->assertEquals($active, $responseArray['active']);
        $this->assertArrayHasKey('signing_key', $responseArray);
        $this->assertEquals($signingKey, $responseArray['signing_key']);
        $this->assertArrayHasKey('api_key', $responseArray);
        $this->assertEquals($apiKey, $responseArray['api_key']);
    
        $deleteresponse = $this->webhookEndpoint->deleteDynamicTemplate($responseArray['id']);
        $deleteresponseArray = json_decode($deleteresponse, true);
        $this->assertNotEmpty($deleteresponseArray);
        $deleteresponseArray = json_decode($deleteresponseArray, true);
        $this->assertArrayHasKey('message', $deleteresponseArray);
        $this->assertEquals("Webhook deleted!", $deleteresponseArray['message']);
    
    }
    

    public function testGetAllDynamicTemplates()
    {
        $response = $this->webhookEndpoint->getAllDynamicTemplates();

        $responseArray = json_decode($response, true);

        $this->assertTrue(is_array($responseArray));
        $this->assertGreaterThan(0, count($responseArray));
        $this->assertArrayHasKey('id', $responseArray[0]);
        $this->assertArrayHasKey('target_url', $responseArray[0]);
        $this->assertArrayHasKey('events', $responseArray[0]);
        $this->assertArrayHasKey('active', $responseArray[0]);
        $this->assertArrayHasKey('signing_key', $responseArray[0]);
        $this->assertArrayHasKey('api_key', $responseArray[0]);
    }

    public function testGetDynamicTemplate()
    {
        $endpointId = '123';

        $response = $this->webhookEndpoint->getDynamicTemplate($endpointId);
        $responseArray = json_decode($response, true);

        $this->assertArrayHasKey('id', $responseArray);
        $this->assertArrayHasKey('target_url', $responseArray);
        $this->assertArrayHasKey('events', $responseArray);
        $this->assertArrayHasKey('active', $responseArray);
        $this->assertArrayHasKey('signing_key', $responseArray);
        $this->assertArrayHasKey('api_key', $responseArray);
    }

}
