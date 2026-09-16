<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxReceiving;
use Paubox\Receiving\PauboxReceivingException;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxReceiving.php";
require_once dirname(__DIR__) . "/receiving/PauboxReceivingException.php";

class PauboxReceivingTest extends TestCase
{
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
            'getReceivedEmail'      => ['getReceivedEmail', [1]],
            'downloadAttachment'    => ['downloadAttachment', [1, 1]],
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
        $this->receiving->downloadAttachment($badId, 1);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDownloadAttachment_RejectsInvalidBlobId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->downloadAttachment(1, $badId);
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
        $this->receiving->getReceivedEmail(999999999);
    }

    /**
     * @group network
     */
    public function testDownloadAttachment_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->receiving->downloadAttachment(999999999, 999999999);
    }
}
?>
