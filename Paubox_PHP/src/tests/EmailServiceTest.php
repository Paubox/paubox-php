<?php
use PHPUnit\Framework\TestCase;
use data\GetEmailDispositionResponse;
use data\SendMessageResponse;
use data\Message;
use data\Content;
use data\Header;
use data\Attachment;
use service\EmailService;
require_once '../../vendor/autoload.php';
require_once dirname(__DIR__) . "/service/EmailService.php";
require_once dirname(__DIR__) . "/data/SendMessageResponse.php";
require_once dirname(__DIR__) . "/config/ConfigurationManager.php";
require_once dirname(__DIR__) . "/data/Message.php";
require_once dirname(__DIR__) . "/data/Content.php";
require_once dirname(__DIR__) . "/data/Header.php";
require_once dirname(__DIR__) . "/data/Attachment.php";
require_once dirname(__DIR__) . "/tests/CsvFileIterator.php";

/**
 * EmailService test case.
 */
class EmailServiceTest extends TestCase
{

    private $emailService;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();
        ConfigurationManager::getProperties("E:\WORK\GIT\paubox-php\PauboxTest\src\config.ini");
        $this->emailService = new EmailService();
    }

    /**
     * Cleans up the environment after running a test.
     */
    public function tearDown()
    {
        $this->emailService = null;
        parent::tearDown();
    }

    public function sendMessageDataProvider_Success()
    {
        $listMessages = array();
        $csvObj = new CsvFileIterator('E:\WORK\GIT\paubox-php\Paubox_PHP\src\tests\SendMessage_TestData.csv');
        $csvObj->next();
        $csvObj->next(); // Skip headers from csv file
        for (; $csvObj->valid(); $csvObj->next()) {
            if ($csvObj->current()[13] != 'SUCCESS') // If Expected output is not Success , then skip the test data
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
     * @dataProvider sendMessageDataProvider_Success
     */
    public function testSendMessage_ReturnSuccess(Message $testMsg)
    {
        $actualResponse = new SendMessageResponse();
        $actualResponse = $this->emailService->SendMessage($testMsg);
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
        $csvObj = new CsvFileIterator('E:\WORK\GIT\paubox-php\Paubox_PHP\src\tests\SendMessage_TestData.csv');
        $csvObj->next();
        $csvObj->next(); // Skip headers from csv file
        for (; $csvObj->valid(); $csvObj->next()) {
            if ($csvObj->current()[13] != 'ERROR') // If Expected output is not Error , then skip the test data
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
        $actualResponse = $this->emailService->SendMessage($testMsg);

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
     * Tests EmailService->getEmailDisposition()
     *
     * @dataProvider getEmailDataProvider_Success
     */
    public function testGetEmailDisposition_ReturnSuccess($sourceTrackingId)
    {
        $actualResponse = new GetEmailDispositionResponse();
        $actualResponse = $this->emailService->getEmailDisposition($sourceTrackingId);
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
        $actualResponse = $this->emailService->getEmailDisposition($sourceTrackingId);
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
}
