<?php
namespace Paubox;

use Paubox\Webhooks\PauboxWebhooksException;

/**
 * Manages webhook subscriptions: which URL Paubox notifies, and for which
 * events.
 *
 * Authenticates with a scoped API key sent as `Authorization: Bearer <key>`.
 * Which events a key may subscribe to follows from its scopes; the SDK does
 * not inspect them, so an event the key is not scoped for comes back 403 and
 * an unrecognised one 422.
 */
class PauboxWebhooks
{
    /**
     * The public gateway exposes only /v1/webhooks/endpoints and rewrites it
     * onto the service's own /v1/endpoints, keeping the producers' event
     * ingest route off the public host. The bare base below is never
     * requested on its own.
     */
    const DEFAULT_BASE_URL = "https://api.paubox.com/v1/webhooks";
    const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private $baseUrl;
    private $apiKey;

    public function __construct($apiKey = null, $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: (getenv('PAUBOX_WEBHOOKS_API_KEY') ?: null);
        $resolvedBaseUrl = $baseUrl
            ?: (getenv('PAUBOX_WEBHOOKS_BASE_URL') ?: self::DEFAULT_BASE_URL);
        $this->baseUrl = rtrim($resolvedBaseUrl, '/');
    }

    /**
     * Lists the endpoints this key can act on. Endpoints carrying an event the
     * key is not scoped for are filtered out by the service.
     *
     * Returns the envelope rather than just the rows: `page_info->count` is
     * the total matching, not the length of the page, and a caller paginating
     * needs it.
     *
     * @param array $params optional 'page' and 'items'
     */
    public function listWebhookEndpoints($params = [])
    {
        $url = $this->baseUrl . "/endpoints" . $this->buildQuery($params);
        $response = $this->apiHelper()->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('listWebhookEndpoints', $url, $response);
        }
        return $this->decode('listWebhookEndpoints', $url, $response);
    }

    /**
     * Subscribes a URL to one or more events.
     *
     * The returned object carries `signing_secret`, which the service
     * discloses only here — not on get, not on list, and there is no way to
     * read it back. Store it when you receive it; recovering from a lost
     * secret means replacing the endpoint.
     *
     * @param array $params 'target_url' (string) and 'events' (array)
     */
    public function createWebhookEndpoint($params)
    {
        if (!is_array($params)) {
            throw new PauboxWebhooksException("params must be an array.");
        }
        if (!isset($params['target_url']) || !is_string($params['target_url']) || $params['target_url'] === '') {
            throw new PauboxWebhooksException("target_url is required and must be a non-empty string.");
        }
        if (!isset($params['events']) || !is_array($params['events']) || empty($params['events'])) {
            throw new PauboxWebhooksException("events is required and must be a non-empty array.");
        }

        $url = $this->baseUrl . "/endpoints";
        $response = $this->apiHelper()->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $params);
        if ($response->code !== 201) {
            $this->throwHttpError('createWebhookEndpoint', $url, $response);
        }
        return $this->unwrap('createWebhookEndpoint', $url, $response);
    }

    /**
     * Retrieves a single endpoint by its UUID. The response does not include
     * the signing secret.
     */
    public function getWebhookEndpoint($id)
    {
        $this->assertUuid($id, 'id');
        $url = $this->baseUrl . "/endpoints/" . $id;
        $response = $this->apiHelper()->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('getWebhookEndpoint', $url, $response);
        }
        return $this->unwrap('getWebhookEndpoint', $url, $response);
    }

    /**
     * Updates an endpoint. Only the keys present in $params are sent, so an
     * update that moves 'target_url' leaves 'events' and 'status' alone.
     * Supplying 'events' replaces the list rather than adding to it.
     */
    public function updateWebhookEndpoint($id, $params)
    {
        $this->assertUuid($id, 'id');
        if (!is_array($params) || empty($params)) {
            throw new PauboxWebhooksException("params must be a non-empty array.");
        }

        $url = $this->baseUrl . "/endpoints/" . $id;
        $response = $this->apiHelper()->callToAPIByPatchWithResponse($url, $this->getAuthHeader(), $params);
        if ($response->code !== 200) {
            $this->throwHttpError('updateWebhookEndpoint', $url, $response);
        }
        return $this->unwrap('updateWebhookEndpoint', $url, $response);
    }

    /**
     * Deletes an endpoint, stopping every event on it and discarding its
     * signing secret. The service answers 204 with no body, so there is
     * nothing to return.
     */
    public function deleteWebhookEndpoint($id)
    {
        $this->assertUuid($id, 'id');
        $url = $this->baseUrl . "/endpoints/" . $id;
        $response = $this->apiHelper()->callToAPIByDeleteWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 204) {
            $this->throwHttpError('deleteWebhookEndpoint', $url, $response);
        }
        return true;
    }

    /**
     * Seam for tests to supply a stubbed helper; production always gets the
     * real one.
     */
    protected function apiHelper()
    {
        return new Service\ApiHelper();
    }

    private function getAuthHeader()
    {
        if (empty($this->apiKey)) {
            throw new PauboxWebhooksException(
                "An API key is required. Pass it to the PauboxWebhooks constructor "
                . "or set the PAUBOX_WEBHOOKS_API_KEY environment variable."
            );
        }
        return "Bearer " . $this->apiKey;
    }

    /**
     * Rejects an id that is not a UUID before it reaches a URL. Without this a
     * value containing '..' or '/' would change which endpoint is called, and
     * the Authorization header goes on the same host, so the key would ride
     * along on the retargeted request.
     */
    private function assertUuid($value, $paramName)
    {
        if (!is_string($value) || !preg_match(self::UUID_PATTERN, $value)) {
            throw new PauboxWebhooksException("$paramName must be a UUID string.");
        }
    }

    private function buildQuery($params)
    {
        if (!is_array($params) || empty($params)) {
            return '';
        }

        $query = [];
        foreach (['page', 'items'] as $key) {
            if (isset($params[$key])) {
                $query[$key] = $params[$key];
            }
        }

        return empty($query) ? '' : '?' . http_build_query($query);
    }

    /**
     * Single resources arrive wrapped as {"data": {...}} and are unwrapped, so
     * callers get the endpoint rather than a container holding nothing else.
     */
    private function unwrap($operation, $url, $response)
    {
        $decoded = $this->decode($operation, $url, $response);
        if (!isset($decoded->data)) {
            throw new PauboxWebhooksException(
                "$operation returned a response without a data object.",
                $response->code,
                $url,
                $response->raw_body
            );
        }
        return $decoded->data;
    }

    private function decode($operation, $url, $response)
    {
        $decoded = json_decode($response->raw_body);
        if (is_null($decoded)) {
            throw new PauboxWebhooksException(
                "$operation returned a response that is not valid JSON.",
                $response->code,
                $url,
                $response->raw_body
            );
        }
        return $decoded;
    }

    /**
     * Errors arrive as {"message": "..."} and the message is promoted onto the
     * exception, since a bare status says nothing about which field was wrong.
     * The raw body stays on getResponseBody().
     */
    private function throwHttpError($operation, $url, $response)
    {
        $status = isset($response->code) ? $response->code : null;
        $body = isset($response->raw_body) ? $response->raw_body : null;

        $detail = '';
        if (!is_null($body)) {
            $decoded = json_decode($body);
            if (isset($decoded->message) && is_string($decoded->message)) {
                $detail = ': ' . $decoded->message;
            }
        }

        throw new PauboxWebhooksException(
            "$operation failed: HTTP " . ($status === null ? 'unknown' : $status) . $detail,
            $status,
            $url,
            $body
        );
    }
}
?>
