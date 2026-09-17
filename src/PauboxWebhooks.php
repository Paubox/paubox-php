<?php
namespace Paubox;

use Paubox\Receiving\PauboxReceivingException;

class PauboxWebhooks
{
    const DEFAULT_BASE_URL = "https://api.paubox.com/v1/email";

    private $baseUrl;
    private $apiKey;

    public function __construct($apiKey = null, $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: (getenv('PAUBOX_API_KEY') ?: null);
        $resolvedBaseUrl = $baseUrl ?: self::DEFAULT_BASE_URL;
        $this->baseUrl = rtrim($resolvedBaseUrl, '/');
    }

    private function getAuthHeader()
    {
        if (empty($this->apiKey)) {
            throw new PauboxReceivingException(
                "An API key is required. Pass it to the PauboxWebhooks constructor "
                . "or set the PAUBOX_API_KEY environment variable."
            );
        }
        return "Token token=" . $this->apiKey;
    }

    private function assertId($value, $paramName)
    {
        if (is_int($value) && $value > 0) {
            return;
        }
        if (is_string($value) && $value !== '') {
            return;
        }
        throw new PauboxReceivingException(
            "$paramName must be a positive integer or non-empty string."
        );
    }

    private function throwHttpError($operation, $url, $response)
    {
        $status = isset($response->code) ? $response->code : null;
        $body = isset($response->raw_body) ? $response->raw_body : null;
        throw new PauboxReceivingException(
            "$operation failed: HTTP " . ($status === null ? 'unknown' : $status),
            $status,
            $url,
            $body
        );
    }

    public function listWebhookEndpoints()
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/webhook_endpoints";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('listWebhookEndpoints', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function createWebhookEndpoint($params)
    {
        if (!is_array($params)) {
            throw new PauboxReceivingException("params must be an array.");
        }
        if (!isset($params['target_url']) || !is_string($params['target_url']) || $params['target_url'] === '') {
            throw new PauboxReceivingException("target_url is required and must be a non-empty string.");
        }
        if (!isset($params['events']) || !is_array($params['events']) || empty($params['events'])) {
            throw new PauboxReceivingException("events is required and must be a non-empty array.");
        }

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/webhook_endpoints";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $params);
        if ($response->code !== 200 && $response->code !== 201) {
            $this->throwHttpError('createWebhookEndpoint', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function getWebhookEndpoint($id)
    {
        $this->assertId($id, 'id');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/webhook_endpoints/" . rawurlencode($id);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('getWebhookEndpoint', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function updateWebhookEndpoint($id, $params)
    {
        $this->assertId($id, 'id');
        if (!is_array($params) || empty($params)) {
            throw new PauboxReceivingException("params must be a non-empty array.");
        }

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/webhook_endpoints/" . rawurlencode($id);
        $response = $api->callToAPIByPatchWithResponse($url, $this->getAuthHeader(), $params);
        if ($response->code !== 200) {
            $this->throwHttpError('updateWebhookEndpoint', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function deleteWebhookEndpoint($id)
    {
        $this->assertId($id, 'id');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/webhook_endpoints/" . rawurlencode($id);
        $response = $api->callToAPIByDeleteWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200 && $response->code !== 204) {
            $this->throwHttpError('deleteWebhookEndpoint', $url, $response);
        }
        return json_decode($response->raw_body);
    }
}
?>
