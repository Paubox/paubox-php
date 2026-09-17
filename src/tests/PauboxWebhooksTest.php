<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxWebhooks;
use Paubox\Receiving\PauboxReceivingException;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxWebhooks.php";
require_once dirname(__DIR__) . "/receiving/PauboxReceivingException.php";

class PauboxWebhooksTest extends TestCase
{
    private $webhooks;

    protected function setUp(): void
    {
        $this->webhooks = new PauboxWebhooks(getenv('PAUBOX_API_KEY') ?: null);
    }

    protected function tearDown(): void
    {
        $this->webhooks = null;
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
            'listWebhookEndpoints'  => ['listWebhookEndpoints', []],
            'createWebhookEndpoint' => ['createWebhookEndpoint', [['target_url' => 'https://example.com', 'events' => ['api_mail_log_delivered']]]],
            'getWebhookEndpoint'    => ['getWebhookEndpoint', [1]],
            'updateWebhookEndpoint' => ['updateWebhookEndpoint', [1, ['active' => false]]],
            'deleteWebhookEndpoint' => ['deleteWebhookEndpoint', [1]],
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
            $keylessClient = new PauboxWebhooks();
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
    public function testGetWebhookEndpoint_RejectsInvalidId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->getWebhookEndpoint($badId);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testUpdateWebhookEndpoint_RejectsInvalidId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->updateWebhookEndpoint($badId, ['active' => false]);
    }

    /**
     * @dataProvider invalidIdDataProvider
     */
    public function testDeleteWebhookEndpoint_RejectsInvalidId($badId)
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->deleteWebhookEndpoint($badId);
    }

    public function testCreateWebhookEndpoint_RejectsNonArray()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->createWebhookEndpoint("not-an-array");
    }

    public function testCreateWebhookEndpoint_RejectsMissingTargetUrl()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->createWebhookEndpoint(['events' => ['api_mail_log_delivered']]);
    }

    public function testCreateWebhookEndpoint_RejectsEmptyTargetUrl()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->createWebhookEndpoint(['target_url' => '', 'events' => ['api_mail_log_delivered']]);
    }

    public function testCreateWebhookEndpoint_RejectsMissingEvents()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->createWebhookEndpoint(['target_url' => 'https://example.com']);
    }

    public function testCreateWebhookEndpoint_RejectsEmptyEvents()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->createWebhookEndpoint(['target_url' => 'https://example.com', 'events' => []]);
    }

    public function testUpdateWebhookEndpoint_RejectsEmptyParams()
    {
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->updateWebhookEndpoint(1, []);
    }

    public function testDefaultBaseUrl()
    {
        $webhooks = new PauboxWebhooks('test-key');
        $ref = new ReflectionProperty(PauboxWebhooks::class, 'baseUrl');
        $this->assertSame('https://api.paubox.com/v1/email', $ref->getValue($webhooks));
    }

    public function testConstructorBaseUrlOverride()
    {
        $webhooks = new PauboxWebhooks('test-key', 'https://custom.test/v1/email');
        $ref = new ReflectionProperty(PauboxWebhooks::class, 'baseUrl');
        $this->assertSame('https://custom.test/v1/email', $ref->getValue($webhooks));
    }

    public function testTrailingSlashStripped()
    {
        $webhooks = new PauboxWebhooks('test-key', 'https://custom.test/v1/email/');
        $ref = new ReflectionProperty(PauboxWebhooks::class, 'baseUrl');
        $this->assertSame('https://custom.test/v1/email', $ref->getValue($webhooks));
    }

    public function testConstructorApiKeyOverride()
    {
        $webhooks = new PauboxWebhooks('my-test-key');
        $ref = new ReflectionProperty(PauboxWebhooks::class, 'apiKey');
        $this->assertSame('my-test-key', $ref->getValue($webhooks));
    }

    /**
     * @group network
     */
    public function testListWebhookEndpoints_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $result = $this->webhooks->listWebhookEndpoints();
        $this->assertNotNull($result);
    }

    /**
     * @group network
     */
    public function testGetWebhookEndpoint_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->getWebhookEndpoint(999999999);
    }

    /**
     * @group network
     */
    public function testDeleteWebhookEndpoint_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxReceivingException::class);
        $this->webhooks->deleteWebhookEndpoint(999999999);
    }
}
?>
