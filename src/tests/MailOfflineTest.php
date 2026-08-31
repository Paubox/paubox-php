<?php
use PHPUnit\Framework\TestCase;
use Paubox\Paubox;
use Paubox\Mail\Message;
use Paubox\Mail\Header;
use Paubox\Mail\Content;
use Paubox\Mail\SendMessageResponse;
use Paubox\Mail\GetEmailDispositionResponse;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

/**
 * Offline coverage for the email side. Untagged, so phpunit.xml runs it by
 * default; nothing here opens a socket — sendMessage() validates before it
 * builds a request.
 */
class MailOfflineTest extends TestCase
{
    public function testSendMessageRejectsNullHeader(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Message Header cannot be null.');
        (new Paubox())->sendMessage(new Message());
    }

    public function testSendMessageRejectsNullContent(): void
    {
        $message = new Message();
        $message->setHeader(new Header());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Message Content cannot be null.');
        (new Paubox())->sendMessage($message);
    }

    /**
     * Both validation failures must reach the caller unchanged.
     */
    public function testValidationFailureIsNotSwallowed(): void
    {
        $message = new Message();
        $message->setContent(new Content());

        try {
            (new Paubox())->sendMessage($message);
            $this->fail('Expected sendMessage() to throw on a null header.');
        } catch (\Exception $e) {
            $this->assertSame('Message Header cannot be null.', $e->getMessage());
        }
    }

    /**
     * @dataProvider forceSecureNotificationProvider
     */
    public function testForceSecureNotificationNormalisation($input, $expected): void
    {
        $method = new \ReflectionMethod(Paubox::class, 'returnForceSecureNotificationValue');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke(new Paubox(), $input));
    }

    public function forceSecureNotificationProvider(): array
    {
        return [
            'true'            => ['true', true],
            'false'           => ['false', false],
            'mixed case'      => ['TrUe', true],
            'surrounding ws'  => ["  false \n", false],
            'null'            => [null, null],
            'empty string'    => ['', null],
            'unrecognised'    => ['yes', null],
        ];
    }

    public function testGetEmailDispositionResponseGetDataTakesNoArgument(): void
    {
        $response = new GetEmailDispositionResponse();
        $response->setData('payload');

        $this->assertSame('payload', $response->getData());
    }

    /**
     * getData() must behave the same on both response models.
     */
    public function testSendMessageResponseAccessorsRoundTrip(): void
    {
        $response = new SendMessageResponse();
        $response->setData('payload');
        $response->setSourceTrackingId('tracking-id');
        $response->setErrors(['boom']);

        $this->assertSame('payload', $response->getData());
        $this->assertSame('tracking-id', $response->getSourceTrackingId());
        $this->assertSame(['boom'], $response->getErrors());
    }
}
