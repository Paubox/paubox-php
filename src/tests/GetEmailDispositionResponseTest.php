<?php

use PHPUnit\Framework\TestCase;
use Paubox\Mail\GetEmailDispositionResponse;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class GetEmailDispositionResponseTest extends TestCase
{
    public function testGetDataTakesNoArgument()
    {
        $response = new GetEmailDispositionResponse();
        $response->setData(['message' => ['id' => 'abc']]);
        $this->assertSame(['message' => ['id' => 'abc']], $response->getData());
    }

    public function testGetDataStillAcceptsTheOldArgument()
    {
        $response = new GetEmailDispositionResponse();
        $response->setData('v');
        $this->assertSame('v', $response->getData('ignored'));
    }
}
