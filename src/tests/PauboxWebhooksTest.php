<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxWebhooks;
use Paubox\Webhooks\PauboxWebhooksException;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxWebhooks.php";
require_once dirname(__DIR__) . "/webhooks/PauboxWebhooksException.php";

/**
 * Records the call it was given and replays a canned response, so the request
 * the client builds and its handling of the reply are both checked without a
 * network.
 */
class RecordingApiHelper
{
    public $calls = [];
    private $responses;

    public function __construct($responses)
    {
        $this->responses = is_array($responses) ? $responses : [$responses];
    }

    public function callToAPIByGetWithResponse($uri, $auth)
    {
        return $this->record('GET', $uri, $auth, null);
    }

    public function callToAPIByPostWithResponse($uri, $auth, $body)
    {
        return $this->record('POST', $uri, $auth, $body);
    }

    public function callToAPIByPatchWithResponse($uri, $auth, $body)
    {
        return $this->record('PATCH', $uri, $auth, $body);
    }

    public function callToAPIByDeleteWithResponse($uri, $auth)
    {
        return $this->record('DELETE', $uri, $auth, null);
    }

    private function record($verb, $uri, $auth, $body)
    {
        $this->calls[] = ['verb' => $verb, 'uri' => $uri, 'auth' => $auth, 'body' => $body];
        $next = array_shift($this->responses);
        return (object) ['code' => $next[0], 'raw_body' => $next[1]];
    }
}

/** Swaps in the recording helper through the protected seam. */
class StubbedWebhooks extends PauboxWebhooks
{
    public $helper;

    protected function apiHelper()
    {
        return $this->helper;
    }
}

class PauboxWebhooksTest extends TestCase
{
    const ENDPOINT_ID = '2ec66c21-bf48-48eb-8d28-f80b2d6b77c7';
    const BASE = 'https://api.paubox.com/v1/webhooks';

    private function client($responses, $apiKey = 'test-key')
    {
        $client = new StubbedWebhooks($apiKey);
        $client->helper = new RecordingApiHelper($responses);
        return $client;
    }

    private function endpointJson($extra = '')
    {
        return '{"id":"' . self::ENDPOINT_ID . '",'
            . '"target_url":"https://hooks.example.com/paubox",'
            . '"status":"active",'
            . '"events":["forms.submission.created"],'
            . '"created_at":"2026-10-09T01:43:41.639968+00:00",'
            . '"updated_at":"2026-10-09T01:43:41.639968+00:00"' . $extra . '}';
    }

    // --- list -------------------------------------------------------------

    public function testList_ReturnsRowsAndPageInfo()
    {
        $client = $this->client([[200,
            '{"data":[' . $this->endpointJson() . '],"page_info":{"count":7,"items":1}}']]);

        $result = $client->listWebhookEndpoints();

        $this->assertCount(1, $result->data);
        $this->assertEquals(self::ENDPOINT_ID, $result->data[0]->id);
        // count is the total matching, not the length of this page.
        $this->assertEquals(7, $result->page_info->count);
    }

    public function testList_SendsBearerAuthToTheEndpointsResource()
    {
        $client = $this->client([[200, '{"data":[],"page_info":{"count":0,"items":0}}']]);

        $client->listWebhookEndpoints();

        $call = $client->helper->calls[0];
        $this->assertEquals('GET', $call['verb']);
        $this->assertEquals(self::BASE . '/endpoints', $call['uri']);
        // Bearer, not the Email API's "Token token=".
        $this->assertEquals('Bearer test-key', $call['auth']);
    }

    public function testList_SendsPaginationWhenGiven()
    {
        $client = $this->client([[200, '{"data":[],"page_info":{"count":0,"items":0}}']]);

        $client->listWebhookEndpoints(['page' => 2, 'items' => 25]);

        $this->assertEquals(
            self::BASE . '/endpoints?page=2&items=25',
            $client->helper->calls[0]['uri']
        );
    }

    public function testList_IgnoresUnknownQueryKeys()
    {
        $client = $this->client([[200, '{"data":[],"page_info":{"count":0,"items":0}}']]);

        $client->listWebhookEndpoints(['page' => 1, 'secret' => 'nope']);

        $this->assertStringNotContainsString('secret', $client->helper->calls[0]['uri']);
    }

    // --- create -----------------------------------------------------------

    public function testCreate_ReturnsTheSigningSecret()
    {
        $client = $this->client([[201,
            '{"data":' . $this->endpointJson(',"signing_secret":"whsec_abc123"')
            . ',"message":"Store this signing_secret now"}']]);

        $endpoint = $client->createWebhookEndpoint([
            'target_url' => 'https://hooks.example.com/paubox',
            'events' => ['forms.submission.created'],
        ]);

        $this->assertEquals('whsec_abc123', $endpoint->signing_secret);
        $this->assertEquals(self::ENDPOINT_ID, $endpoint->id);
    }

    public function testCreate_PostsTargetUrlAndEvents()
    {
        $client = $this->client([[201, '{"data":' . $this->endpointJson() . '}']]);

        $client->createWebhookEndpoint([
            'target_url' => 'https://hooks.example.com/paubox',
            'events' => ['forms.submission.created'],
        ]);

        $call = $client->helper->calls[0];
        $this->assertEquals('POST', $call['verb']);
        $this->assertEquals(self::BASE . '/endpoints', $call['uri']);
        $this->assertEquals('https://hooks.example.com/paubox', $call['body']['target_url']);
        $this->assertEquals(['forms.submission.created'], $call['body']['events']);
    }

    /**
     * The catalog belongs to the service and grows without an SDK release, so
     * an unrecognised name must reach the server rather than be rejected here.
     */
    public function testCreate_DoesNotValidateEventNames()
    {
        $client = $this->client([[201, '{"data":' . $this->endpointJson() . '}']]);

        $client->createWebhookEndpoint([
            'target_url' => 'https://e.test/h',
            'events' => ['some.future.event'],
        ]);

        $this->assertEquals(['some.future.event'], $client->helper->calls[0]['body']['events']);
    }

    public function invalidCreateParamsProvider()
    {
        return [
            'not an array'    => ['nope'],
            'no target_url'   => [['events' => ['forms.submission.created']]],
            'empty target'    => [['target_url' => '', 'events' => ['x']]],
            'no events'       => [['target_url' => 'https://e.test/h']],
            'empty events'    => [['target_url' => 'https://e.test/h', 'events' => []]],
            'unknown key'     => [['target_url' => 'https://e.test/h', 'events' => ['x'], 'status' => 'active']],
        ];
    }

    /**
     * @dataProvider invalidCreateParamsProvider
     */
    public function testCreate_RejectsBadParamsBeforeAnyRequest($params)
    {
        $client = $this->client([]);

        $this->expectException(PauboxWebhooksException::class);
        try {
            $client->createWebhookEndpoint($params);
        } finally {
            $this->assertEmpty($client->helper->calls);
        }
    }

    // --- get --------------------------------------------------------------

    public function testGet_UnwrapsTheDataEnvelope()
    {
        $client = $this->client([[200, '{"data":' . $this->endpointJson() . '}']]);

        $endpoint = $client->getWebhookEndpoint(self::ENDPOINT_ID);

        $this->assertEquals(self::ENDPOINT_ID, $endpoint->id);
        $this->assertEquals('active', $endpoint->status);
        $this->assertEquals(self::BASE . '/endpoints/' . self::ENDPOINT_ID,
            $client->helper->calls[0]['uri']);
    }

    public function testGet_ThrowsWhenTheEnvelopeIsMissing()
    {
        $client = $this->client([[200, '{}']]);

        $this->expectException(PauboxWebhooksException::class);
        $client->getWebhookEndpoint(self::ENDPOINT_ID);
    }

    public function badIdProvider()
    {
        return [
            'integer'        => [1],
            'not a uuid'     => ['abc'],
            'path traversal' => ['../endpoints'],
            'slash'          => ['2ec66c21-bf48-48eb-8d28-f80b2d6b77c7/x'],
            'empty'          => [''],
            'null'           => [null],
        ];
    }

    /**
     * A non-UUID id must be refused before a request. Without the guard a
     * value containing '..' or '/' would change which endpoint is called, and
     * the Authorization header goes on the same host, so the key would ride
     * along on the retargeted request.
     *
     * @dataProvider badIdProvider
     */
    public function testGet_RejectsBadIdsBeforeAnyRequest($id)
    {
        $client = $this->client([]);

        $this->expectException(PauboxWebhooksException::class);
        try {
            $client->getWebhookEndpoint($id);
        } finally {
            $this->assertEmpty($client->helper->calls);
        }
    }

    // --- update -----------------------------------------------------------

    public function testUpdate_SendsOnlyTheKeysGiven()
    {
        $client = $this->client([[200,
            '{"data":{"id":"' . self::ENDPOINT_ID . '","target_url":"https://hooks.example.com/paubox",'
            . '"status":"disabled","events":["forms.submission.created"],'
            . '"created_at":"2026-10-09T01:43:41.639968+00:00",'
            . '"updated_at":"2026-10-09T02:00:00.000000+00:00"}}']]);

        $endpoint = $client->updateWebhookEndpoint(self::ENDPOINT_ID, ['status' => 'disabled']);

        $this->assertEquals('disabled', $endpoint->status);

        $call = $client->helper->calls[0];
        $this->assertEquals('PATCH', $call['verb']);
        $this->assertEquals(['status' => 'disabled'], $call['body']);
    }

    public function testUpdate_RejectsEmptyParams()
    {
        $client = $this->client([]);

        $this->expectException(PauboxWebhooksException::class);
        try {
            $client->updateWebhookEndpoint(self::ENDPOINT_ID, []);
        } finally {
            $this->assertEmpty($client->helper->calls);
        }
    }

    public function testUpdate_RejectsUnknownKeysBeforeAnyRequest()
    {
        $client = $this->client([]);

        $this->expectException(PauboxWebhooksException::class);
        $this->expectExceptionMessage('Unknown params: target_uri');
        try {
            $client->updateWebhookEndpoint(self::ENDPOINT_ID, ['target_uri' => 'https://e.test/h']);
        } finally {
            $this->assertEmpty($client->helper->calls);
        }
    }

    // --- delete -----------------------------------------------------------

    /**
     * The service answers 204 with no body, so there is nothing to decode —
     * an empty body must not be read as a failure.
     */
    public function testDelete_HandlesAnEmpty204()
    {
        $client = $this->client([[204, '']]);

        $this->assertTrue($client->deleteWebhookEndpoint(self::ENDPOINT_ID));

        $call = $client->helper->calls[0];
        $this->assertEquals('DELETE', $call['verb']);
        $this->assertEquals(self::BASE . '/endpoints/' . self::ENDPOINT_ID, $call['uri']);
    }

    public function testDelete_RejectsBadIdsBeforeAnyRequest()
    {
        $client = $this->client([]);

        $this->expectException(PauboxWebhooksException::class);
        try {
            $client->deleteWebhookEndpoint('../endpoints');
        } finally {
            $this->assertEmpty($client->helper->calls);
        }
    }

    // --- errors -----------------------------------------------------------

    /**
     * A bare status says nothing about which field was wrong, so the service's
     * message is promoted onto the exception message while the raw body stays
     * on getResponseBody().
     */
    public function testError_PromotesTheServiceMessage()
    {
        $client = $this->client([[422,
            '{"message":"target_url: an endpoint already exists for this URL"}']]);

        try {
            $client->createWebhookEndpoint([
                'target_url' => 'https://hooks.example.com/paubox',
                'events' => ['forms.submission.created'],
            ]);
            $this->fail('Expected PauboxWebhooksException');
        } catch (PauboxWebhooksException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('an endpoint already exists', $e->getMessage());
            $this->assertStringContainsString('target_url', $e->getResponseBody());
            $this->assertEquals(self::BASE . '/endpoints', $e->getUrl());
        }
    }

    public function testError_SurvivesANonJsonBody()
    {
        $client = $this->client([[502, '<html>bad gateway</html>']]);

        try {
            $client->listWebhookEndpoints();
            $this->fail('Expected PauboxWebhooksException');
        } catch (PauboxWebhooksException $e) {
            $this->assertEquals(502, $e->getStatusCode());
            $this->assertStringContainsString('HTTP 502', $e->getMessage());
        }
    }

    /**
     * An id that doesn't belong to this customer is a 404 rather than a 403,
     * so one account cannot probe another's for existence.
     */
    public function testError_UnknownEndpointIs404()
    {
        $client = $this->client([[404, '{"message":"webhook endpoint not found"}']]);

        try {
            $client->getWebhookEndpoint(self::ENDPOINT_ID);
            $this->fail('Expected PauboxWebhooksException');
        } catch (PauboxWebhooksException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    // --- configuration ----------------------------------------------------

    public function missingApiKeyDataProvider()
    {
        return [
            'listWebhookEndpoints'  => ['listWebhookEndpoints', []],
            'createWebhookEndpoint' => ['createWebhookEndpoint', [[
                'target_url' => 'https://e.test/h', 'events' => ['forms.submission.created']]]],
            'getWebhookEndpoint'    => ['getWebhookEndpoint', [self::ENDPOINT_ID]],
            'updateWebhookEndpoint' => ['updateWebhookEndpoint', [self::ENDPOINT_ID, ['status' => 'disabled']]],
            'deleteWebhookEndpoint' => ['deleteWebhookEndpoint', [self::ENDPOINT_ID]],
        ];
    }

    /**
     * @dataProvider missingApiKeyDataProvider
     */
    public function testMethod_ThrowsWithoutApiKey($method, $args)
    {
        $original = getenv('PAUBOX_WEBHOOKS_API_KEY');
        putenv('PAUBOX_WEBHOOKS_API_KEY');

        try {
            $client = $this->client([], null);
            $this->expectException(PauboxWebhooksException::class);
            call_user_func_array([$client, $method], $args);
        } finally {
            if ($original !== false) {
                putenv('PAUBOX_WEBHOOKS_API_KEY=' . $original);
            }
        }
    }

    public function testBaseUrl_IsOverridableAndTrimmed()
    {
        $client = new StubbedWebhooks('k', 'https://api.staging.paubox.net/v1/webhooks/');
        $client->helper = new RecordingApiHelper([[200, '{"data":[],"page_info":{"count":0,"items":0}}']]);

        $client->listWebhookEndpoints();

        $this->assertEquals(
            'https://api.staging.paubox.net/v1/webhooks/endpoints',
            $client->helper->calls[0]['uri']
        );
    }

    public function testApiKey_FallsBackToTheEnvironment()
    {
        $original = getenv('PAUBOX_WEBHOOKS_API_KEY');
        putenv('PAUBOX_WEBHOOKS_API_KEY=from-env');

        try {
            $client = new StubbedWebhooks();
            $client->helper = new RecordingApiHelper([[200, '{"data":[],"page_info":{"count":0,"items":0}}']]);

            $client->listWebhookEndpoints();

            $this->assertEquals('Bearer from-env', $client->helper->calls[0]['auth']);
        } finally {
            if ($original === false) {
                putenv('PAUBOX_WEBHOOKS_API_KEY');
            } else {
                putenv('PAUBOX_WEBHOOKS_API_KEY=' . $original);
            }
        }
    }
}
