<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxReceiving;
use Paubox\Receiving\PauboxReceivingException;
use Paubox\Service\ApiHelper;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxReceiving.php";
require_once dirname(__DIR__) . "/receiving/PauboxReceivingException.php";

class PauboxReceivingTest extends TestCase
{
    const EMAIL_ID = '0192f0c4-0000-7000-8000-000000000001';
    const ATTACHMENT_ID = '0192f0c4-0000-7000-8000-0000000000a1';
    const MISSING_ID = '00000000-0000-0000-0000-000000000000';

    private $receiving;

    protected function setUp(): void
    {
        $this->receiving = new PauboxReceiving(getenv('PAUBOX_API_KEY') ?: null);
    }

    protected function tearDown(): void
    {
        $this->receiving = null;
        parent::tearDown();
    }

    private function skipIfNoApiKey()
    {
        if (!getenv('PAUBOX_API_KEY')) {
            $this->markTestSkipped('PAUBOX_API_KEY is not set.');
        }
    }

    public function missingApiKeyDataProvider()
    {
        return [
            'listReceivingDomains'  => ['listReceivingDomains', []],
            'createReceivingDomain' => ['createReceivingDomain', []],
            'getReceivingDomain'    => ['getReceivingDomain', [1]],
            'deleteReceivingDomain' => ['deleteReceivingDomain', [1]],
            'listMailboxes'         => ['listMailboxes', [1]],
            'createMailbox'         => ['createMailbox', [1, 'user', 'pass']],
            'getMailbox'            => ['getMailbox', [1, 1]],
            'deleteMailbox'         => ['deleteMailbox', [1, 1]],
            'listReceivedEmails'    => ['listReceivedEmails', []],
            'getReceivedEmail'      => ['getReceivedEmail', [self::EMAIL_ID]],
            'downloadAttachment'    => ['downloadAttachment', [self::EMAIL_ID, self::ATTACHMENT_ID]],
        ];
    }

    /**
     * @dataProvider missingApiKeyDataProvider
     */
    public function testMethod_ThrowsWithoutApiKey($method, $args)
    {
        $originalKey = getenv('PAUBOX_API_KEY');
        putenv('PAUBOX_API_KEY');

        try {
            $keylessClient = new PauboxReceiving();
            $caught = false;
            try {
                call_user_func_array([$keylessClient, $method], $args);
            } catch (PauboxReceivingException $e) {
                $caught = true;
                $this->assertStringContainsString("API key", $e->getMessage());
                $this->assertStringContainsString("PAUBOX_API_KEY", $e->getMessage());
            }
            $this->assertTrue($caught, "Expected PauboxReceivingException when calling $method() without an API key.");
        } finally {
            if ($originalKey !== false) {
                putenv('PAUBOX_API_KEY=' . $originalKey);
            }
        }
    }

    public function invalidIdDataProvider()
    {
        return [
            'zero'         => [0],
            'negative'     => [-1],
            'null'         => [null],
            'empty string' => [''],
            'bool true'    => [true],
            'bool false'   => [false],
            'float'        => [1.5],
            'array'        => [[]],
        ];
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testGetReceivingDomain_RejectsInvalidId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getReceivingDomain($badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDeleteReceivingDomain_RejectsInvalidId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->deleteReceivingDomain($badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testListMailboxes_RejectsInvalidDomainId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->listMailboxes($badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testCreateMailbox_RejectsInvalidDomainId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox($badId, 'user', 'pass');
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testGetMailbox_RejectsInvalidDomainId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getMailbox($badId, 1);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testGetMailbox_RejectsInvalidMailboxId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getMailbox(1, $badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDeleteMailbox_RejectsInvalidDomainId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->deleteMailbox($badId, 1);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDeleteMailbox_RejectsInvalidMailboxId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->deleteMailbox(1, $badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testGetReceivedEmail_RejectsInvalidId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getReceivedEmail($badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDownloadAttachment_RejectsInvalidEmailId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->downloadAttachment($badId, self::ATTACHMENT_ID);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDownloadAttachment_RejectsInvalidAttachmentId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->downloadAttachment(self::EMAIL_ID, $badId);
    }

    public function testDownloadAttachment_RejectsMissingAttachmentId()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->expectExceptionMessage('attachmentId');
        $this->receiving->downloadAttachment(self::EMAIL_ID);
    }

    public function testDownloadAttachment_RejectsBothAttachmentIdAndBlobId()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->expectExceptionMessage('not both');
        $this->receiving->downloadAttachment(
            emailId: self::EMAIL_ID,
            attachmentId: self::ATTACHMENT_ID,
            blobId: self::ATTACHMENT_ID
        );
    }

    private function fakeClient($code, $body)
    {
        $api = new FakeReceivingApiHelper((object) ['code' => $code, 'raw_body' => $body]);
        return [new FakeApiPauboxReceiving($api, 'test-key', 'https://custom.test/v1/email/'), $api];
    }

    public function attachmentArgumentStyleDataProvider()
    {
        return [
            'positional'      => [fn ($c) => $c->downloadAttachment(self::EMAIL_ID, self::ATTACHMENT_ID)],
            'named'           => [fn ($c) => $c->downloadAttachment(emailId: self::EMAIL_ID, attachmentId: self::ATTACHMENT_ID)],
            'deprecated name' => [fn ($c) => $c->downloadAttachment(emailId: self::EMAIL_ID, blobId: self::ATTACHMENT_ID)],
        ];
    }

    /**
     * @dataProvider attachmentArgumentStyleDataProvider
     */
    public function testDownloadAttachment_BuildsAttachmentUrl($call)
    {
        [$client, $api] = $this->fakeClient(200, 'bytes');
        $call($client);
        $this->assertSame([[
            'callToAPIByGetRawWithResponse',
            'https://custom.test/v1/email/receiving/' . self::EMAIL_ID . '/attachments/' . self::ATTACHMENT_ID,
            'Token token=test-key',
        ]], $api->calls);
    }

    public function testDownloadAttachment_BlobIdRaisesSilencedDeprecation()
    {
        [$client] = $this->fakeClient(200, 'bytes');
        $raised = [];
        set_error_handler(function ($errno, $errstr) use (&$raised) {
            $raised[] = [$errno, $errstr];
            return true;
        });
        try {
            $client->downloadAttachment(emailId: self::EMAIL_ID, blobId: self::ATTACHMENT_ID);
        } finally {
            restore_error_handler();
        }
        $this->assertCount(1, $raised);
        $this->assertSame(E_USER_DEPRECATED, $raised[0][0]);
        $this->assertStringContainsString('$blobId', $raised[0][1]);
    }

    public function attachmentBodyDataProvider()
    {
        return [
            'binary'       => ["%PDF-1.7\x00\xff\xfe\r\n\x1a"],
            'invalid json' => ['{"truncated": '],
            'json object'  => ['{"data": {"id": "x"}}'],
            'empty'        => [''],
        ];
    }

    /**
     * @dataProvider attachmentBodyDataProvider
     */
    public function testDownloadAttachment_ReturnsRawBytes($bytes)
    {
        [$client] = $this->fakeClient(200, $bytes);
        $this->assertSame($bytes, $client->downloadAttachment(self::EMAIL_ID, self::ATTACHMENT_ID));
    }

    public function testDownloadAttachment_ThrowsOnNotFound()
    {
        [$client] = $this->fakeClient(404, '{"error":"attachment not found"}');
        try {
            $client->downloadAttachment(self::EMAIL_ID, self::MISSING_ID);
            $this->fail('Expected PauboxReceivingException');
        } catch (PauboxReceivingException $e) {
            $this->assertSame(404, $e->getStatusCode());
            $this->assertSame(
                'https://custom.test/v1/email/receiving/' . self::EMAIL_ID . '/attachments/' . self::MISSING_ID,
                $e->getUrl()
            );
            $this->assertSame('{"error":"attachment not found"}', $e->getResponseBody());
        }
    }

    public function rawResponseContentTypeDataProvider()
    {
        return [
            'application/json' => ['application/json', '{"truncated": '],
            'application/xml'  => ['application/xml', '<unclosed'],
            'text/csv'         => ['text/csv', ''],
            'application/pdf'  => ['application/pdf', "%PDF-1.7\x00\xff"],
        ];
    }

    /**
     * @dataProvider rawResponseContentTypeDataProvider
     */
    public function testRawGetRequest_LeavesBodyUnparsed($contentType, $bytes)
    {
        $request = (new ApiHelper())->buildRawGetRequest('https://custom.test/x', 'Token token=test-key');
        $this->assertFalse($request->auto_parse);
        $this->assertSame('*/*', $request->headers['accept']);
        $this->assertSame('Token token=test-key', $request->headers['Authorization']);

        $response = new \Httpful\Response(
            $bytes,
            "HTTP/1.1 200 OK\r\nContent-Type: $contentType\r\nContent-Disposition: attachment; filename=\"a\"\r\n",
            $request
        );
        $this->assertSame(200, $response->code);
        $this->assertSame($bytes, $response->raw_body);
        $this->assertSame($bytes, $response->body);
    }

    public function testListReceivedEmails_BuildsQuery()
    {
        [$client, $api] = $this->fakeClient(200, '{"object":"list","data":[],"has_more":false}');
        $client->listReceivedEmails([
            'limit'     => 100,
            'after'     => self::EMAIL_ID,
            'before'    => null,
            'search'    => 'lab results & more',
            'sort'      => 'received_at',
            'ascending' => true,
            'domain'    => 'ignored.example.com',
        ]);
        $this->assertSame(
            'https://custom.test/v1/email/receiving?limit=100&after=' . self::EMAIL_ID
                . '&search=lab+results+%26+more&sort=received_at&ascending=true',
            $api->calls[0][1]
        );
    }

    public function testListReceivedEmails_SendsAscendingFalseAsLiteral()
    {
        [$client, $api] = $this->fakeClient(200, '{"object":"list","data":[],"has_more":false}');
        $client->listReceivedEmails(['ascending' => false]);
        $this->assertSame('https://custom.test/v1/email/receiving?ascending=false', $api->calls[0][1]);
    }

    public function testListReceivedEmails_OmitsQueryWhenNoParams()
    {
        [$client, $api] = $this->fakeClient(200, '{"object":"list","data":[],"has_more":false}');
        $client->listReceivedEmails();
        $this->assertSame('https://custom.test/v1/email/receiving', $api->calls[0][1]);
    }

    public function testListReceivedEmails_ReturnsListShape()
    {
        $body = json_encode([
            'object' => 'list',
            'data' => [[
                'email_id' => self::EMAIL_ID,
                'from' => [['name' => 'Sender', 'address' => 'sender@example.com']],
                'to' => [['name' => null, 'address' => 'inbox@example.com']],
                'subject' => 'Hello',
                'received_at' => '2026-10-01T12:00:00Z',
                'has_attachment' => true,
                'spam' => false,
                'size' => 2048,
                'domain' => 'example.com',
            ]],
            'has_more' => true,
        ]);
        [$client] = $this->fakeClient(200, $body);
        $result = $client->listReceivedEmails(['limit' => 1]);
        $this->assertSame('list', $result->object);
        $this->assertTrue($result->has_more);
        $this->assertSame(self::EMAIL_ID, $result->data[0]->email_id);
        $this->assertSame('sender@example.com', $result->data[0]->from[0]->address);
        $this->assertFalse(property_exists($result->data[0], 'blob_id'));
    }

    public function testListReceivedEmails_ThrowsOnError()
    {
        [$client] = $this->fakeClient(400, '{"error":"bad request"}');
        try {
            $client->listReceivedEmails(['limit' => 1]);
            $this->fail('Expected PauboxReceivingException');
        } catch (PauboxReceivingException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertSame('https://custom.test/v1/email/receiving?limit=1', $e->getUrl());
        }
    }

    public function testGetReceivedEmail_BuildsUrlAndReturnsDetailShape()
    {
        $body = json_encode(['data' => [
            'email_id' => self::EMAIL_ID,
            'from' => [['name' => 'Sender', 'address' => 'sender@example.com']],
            'to' => [['name' => null, 'address' => 'inbox@example.com']],
            'cc' => [],
            'subject' => 'Hello',
            'date' => 'Thu, 1 Oct 2026 12:00:00 +0000',
            'received_at' => '2026-10-01T12:00:00Z',
            'message_id' => ['<abc@example.com>'],
            'in_reply_to' => null,
            'references' => null,
            'spam' => false,
            'spam_score' => 0.1,
            'text_body' => 'hi',
            'html_body' => null,
            'attachments' => [[
                'id' => self::ATTACHMENT_ID,
                'filename' => 'report.pdf',
                'content_type' => 'application/pdf',
                'size' => 1024,
                'content_id' => null,
                'download_url' => 'https://api.paubox.com/v1/email/receiving/' . self::EMAIL_ID . '/attachments/' . self::ATTACHMENT_ID,
            ]],
            'size' => 4096,
            'authentication' => ['spf' => 'pass', 'dkim' => 'pass', 'dmarc' => 'pass'],
            'domain' => 'example.com',
            'headers' => [['name' => 'X-Test', 'value' => '1']],
        ]]);
        [$client, $api] = $this->fakeClient(200, $body);
        $result = $client->getReceivedEmail(self::EMAIL_ID);

        $this->assertSame([[
            'callToAPIByGetWithResponse',
            'https://custom.test/v1/email/receiving/' . self::EMAIL_ID,
            'Token token=test-key',
        ]], $api->calls);
        $this->assertSame(self::EMAIL_ID, $result->data->email_id);
        $attachment = $result->data->attachments[0];
        $this->assertSame(self::ATTACHMENT_ID, $attachment->id);
        $this->assertFalse(property_exists($attachment, 'blob_id'));

        [$downloadClient, $downloadApi] = $this->fakeClient(200, 'bytes');
        $downloadClient->downloadAttachment($result->data->email_id, $attachment->id);
        $this->assertSame(
            'https://custom.test/v1/email/receiving/' . self::EMAIL_ID . '/attachments/' . self::ATTACHMENT_ID,
            $downloadApi->calls[0][1]
        );
    }

    public function testGetReceivedEmail_ThrowsOnNotFound()
    {
        [$client] = $this->fakeClient(404, '{"error":"email not found"}');
        $this->expectException(PauboxReceivingException::class);
        $client->getReceivedEmail(self::MISSING_ID);
    }

    public function testCreateMailbox_RejectsEmptyName()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox(1, '', 'password');
    }

    public function testCreateMailbox_RejectsNullName()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox(1, null, 'password');
    }

    public function testCreateMailbox_RejectsNonStringName()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox(1, 123, 'password');
    }

    public function testCreateMailbox_RejectsEmptyPassword()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox(1, 'user', '');
    }

    public function testCreateMailbox_RejectsNullPassword()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox(1, 'user', null);
    }

    public function testCreateMailbox_RejectsNonStringPassword()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->createMailbox(1, 'user', 123);
    }

    public function testDefaultBaseUrl()
    {
        $receiving = new PauboxReceiving('test-key');
        $ref = new ReflectionProperty(PauboxReceiving::class, 'baseUrl');
        $this->assertSame('https://api.paubox.com/v1/email', $ref->getValue($receiving));
    }

    public function testConstructorBaseUrlOverride()
    {
        $receiving = new PauboxReceiving('test-key', 'https://custom.test/v1/email');
        $ref = new ReflectionProperty(PauboxReceiving::class, 'baseUrl');
        $this->assertSame('https://custom.test/v1/email', $ref->getValue($receiving));
    }

    public function testTrailingSlashStripped()
    {
        $receiving = new PauboxReceiving('test-key', 'https://custom.test/v1/email/');
        $ref = new ReflectionProperty(PauboxReceiving::class, 'baseUrl');
        $this->assertSame('https://custom.test/v1/email', $ref->getValue($receiving));
    }

    public function testConstructorApiKeyOverride()
    {
        $receiving = new PauboxReceiving('my-test-key');
        $ref = new ReflectionProperty(PauboxReceiving::class, 'apiKey');
        $this->assertSame('my-test-key', $ref->getValue($receiving));
    }

    /**
     * @group network
     */
    public function testListReceivingDomains_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $result = $this->receiving->listReceivingDomains();
        $this->assertNotNull($result);
    }

    /**
     * @group network
     */
    public function testGetReceivingDomain_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getReceivingDomain(999999999);
    }

    /**
     * @group network
     */
    public function testListMailboxes_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->listMailboxes(999999999);
    }

    /**
     * @group network
     */
    public function testGetMailbox_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getMailbox(999999999, 999999999);
    }

    /**
     * @group network
     */
    public function testListReceivedEmails_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $result = $this->receiving->listReceivedEmails(['limit' => 1]);
        $this->assertNotNull($result);
    }

    /**
     * @group network
     */
    public function testGetReceivedEmail_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->getReceivedEmail(self::MISSING_ID);
    }

    /**
     * @group network
     */
    public function testDownloadAttachment_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->downloadAttachment(self::MISSING_ID, self::MISSING_ID);
    }
}

class FakeReceivingApiHelper extends ApiHelper
{
    public $calls = [];
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    function callToAPIByGetWithResponse($uri, $auth_header)
    {
        $this->calls[] = ['callToAPIByGetWithResponse', $uri, $auth_header];
        return $this->response;
    }

    function callToAPIByGetRawWithResponse($uri, $auth_header)
    {
        $this->calls[] = ['callToAPIByGetRawWithResponse', $uri, $auth_header];
        return $this->response;
    }
}

class FakeApiPauboxReceiving extends PauboxReceiving
{
    private $fakeApi;

    public function __construct($fakeApi, $apiKey = null, $baseUrl = null)
    {
        parent::__construct($apiKey, $baseUrl);
        $this->fakeApi = $fakeApi;
    }

    protected function apiHelper()
    {
        return $this->fakeApi;
    }
}
?>
