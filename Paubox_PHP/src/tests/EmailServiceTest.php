<?php

use PHPUnit\Framework\TestCase;
use data\GetEmailDispositionResponse;
use data\SendMessageResponse;
use data\Message;
use service\EmailService;
require_once '../../vendor/autoload.php';
require_once dirname(__DIR__)."/service/EmailService.php";
require_once dirname(__DIR__)."/data/SendMessageResponse.php";
//require 'CsvFileIterator.php';

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
        
        // TODO Auto-generated EmailServiceTest::setUp()
        
        $this->emailService = new EmailService();
    }
    
    /**
     * Cleans up the environment after running a test.
     */
    public function tearDown()
    {
        // TODO Auto-generated EmailServiceTest::tearDown()
        $this->emailService = null;
        
        parent::tearDown();
    }
    
    /**
     * Constructs the test case.
     */
    public function __construct() {
        parent::__construct();
        // Your construct here
    }
    
    /**
     * Tests EmailService->SendMessage()
     */
  /*   public function testSendMessage_ReturnSuccess()
    {
        $actualResponse = new SendMessageResponse();
        //$actualResponse = $this->emailService->SendMessage($testMsg);
        if (is_null($actualResponse) || is_null($actualResponse->data) || is_null($actualResponse->sourceTrackingId) )
        {
            $this->fail();
        }
        else if (!is_null($actualResponse->errors) && $actualResponse->errors.Count() > 0)
        {
            $this->pass();
        }
    } */
    
   /*  public function testSendMessage_ReturnError(Message $testMsg)
    {
        $actualResponse = new SendMessageResponse();
        //$actualResponse = $this->emailService->SendMessage($testMsg);
        
        if (!is_null($actualResponse))
        {
            if (!is_null($actualResponse->errors) && $actualResponse->errors.Count() > 0)
            {
                $this->pass();
            }
            else
            {
                $this->fail();
            }
        }
        else
        {
            $this->fail();
        }
        
    } */
    
    public function getEmailDataProvider_Error() {
        return array(
            array(null),
            array(""),
            array(" "),
            array("151515215"),
        );
    }
    
    public function getEmailDataProvider_Success() {
        return array(
            array("ce1e2143-474d-43ba-b829-17a26b8005e5"),
            array("1aed91d1-f7ce-4c3d-8df2-85ecd225a7fc"),
        );
    }
    
    /**
     * Tests EmailService->getEmailDisposition()
     */
    /**
     * @dataProvider getEmailDataProvider_Success
     */
    public function testGetEmailDisposition_ReturnSuccess()
    {
        $actualResponse = new GetEmailDispositionResponse();
        $actualResponse = $this->emailService->getEmailDisposition("97b18032-59d5-47c7-a7c6-a2ed27f0f44e");
        
        if (is_null($actualResponse) || is_null($actualResponse->data) || is_null($actualResponse->data->message) || is_null($actualResponse->data->message->id))
        {
            $this->fail();
        }
        else if (!is_null($actualResponse->errors) && $actualResponse->errors.Count() > 0)
        {
            $this->fail();
        }
        else if (!isnull($emailDisposition->data->message->message_deliveries) && $emailDisposition->data->message->message_deliveries.Count() > 0)
        {
            $this->pass();
        }
        else
        {
            $this->fail();
        }
        
    }
    
    /**
     * @dataProvider getEmailDataProvider_Error
     */
    public function testGetEmailDisposition_ReturnError()
    {
        $actualResponse = new GetEmailDispositionResponse();
        $actualResponse = $this->emailService->getEmailDisposition("97b18032-59d5-47c7-a7c6-a2ed27f0f44e_false");
        if (is_null($actualResponse) || is_null($actualResponse->errors) || $actualResponse->errors.Count() <= 0)
        {
            $this->fail();
        }
        else{
            if(is_null($actualResponse->Errors[0]->Title))
            {
                $this->fail();
            }
            else{
                $this->pass();
            }
        }
        
    }
    
    
}

