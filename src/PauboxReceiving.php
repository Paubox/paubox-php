<?php
namespace Paubox;

use Paubox\Receiving\PauboxReceivingException;

class PauboxReceiving
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
                "An API key is required. Pass it to the PauboxReceiving constructor "
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

    public function listReceivingDomains()
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('listReceivingDomains', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function createReceivingDomain($slug = null)
    {
        $body = new \stdClass();
        if (!is_null($slug)) {
            $body = ['slug' => $slug];
        }
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $body);
        if ($response->code !== 200 && $response->code !== 201) {
            $this->throwHttpError('createReceivingDomain', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function getReceivingDomain($id)
    {
        $this->assertId($id, 'id');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains/" . rawurlencode($id);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('getReceivingDomain', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function deleteReceivingDomain($id)
    {
        $this->assertId($id, 'id');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains/" . rawurlencode($id);
        $response = $api->callToAPIByDeleteWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200 && $response->code !== 204) {
            $this->throwHttpError('deleteReceivingDomain', $url, $response);
        }
        return true;
    }

    public function listMailboxes($domainId)
    {
        $this->assertId($domainId, 'domainId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains/" . rawurlencode($domainId) . "/mailboxes";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('listMailboxes', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function createMailbox($domainId, $name, $password, $quotaBytes = null)
    {
        $this->assertId($domainId, 'domainId');
        if (!is_string($name) || $name === '') {
            throw new PauboxReceivingException("name is required and must be a non-empty string.");
        }
        if (!is_string($password) || $password === '') {
            throw new PauboxReceivingException("password is required and must be a non-empty string.");
        }
        $body = ['name' => $name, 'password' => $password];
        if (!is_null($quotaBytes)) {
            $body['quota_bytes'] = $quotaBytes;
        }
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains/" . rawurlencode($domainId) . "/mailboxes";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $body);
        if ($response->code !== 200 && $response->code !== 201) {
            $this->throwHttpError('createMailbox', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function getMailbox($domainId, $id)
    {
        $this->assertId($domainId, 'domainId');
        $this->assertId($id, 'id');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains/" . rawurlencode($domainId)
            . "/mailboxes/" . rawurlencode($id);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('getMailbox', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function deleteMailbox($domainId, $id)
    {
        $this->assertId($domainId, 'domainId');
        $this->assertId($id, 'id');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/domains/" . rawurlencode($domainId)
            . "/mailboxes/" . rawurlencode($id);
        $response = $api->callToAPIByDeleteWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200 && $response->code !== 204) {
            $this->throwHttpError('deleteMailbox', $url, $response);
        }
        return true;
    }

    public function listReceivedEmails($params = [])
    {
        $allowedKeys = ['limit', 'after', 'before'];
        $query = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $params) && !is_null($params[$key])) {
                $query[$key] = $params[$key];
            }
        }
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving";
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('listReceivedEmails', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function getReceivedEmail($emailId)
    {
        $this->assertId($emailId, 'emailId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/" . rawurlencode($emailId);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('getReceivedEmail', $url, $response);
        }
        return json_decode($response->raw_body);
    }

    public function downloadAttachment($emailId, $blobId)
    {
        $this->assertId($emailId, 'emailId');
        $this->assertId($blobId, 'blobId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/receiving/" . rawurlencode($emailId)
            . "/attachments/" . rawurlencode($blobId);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());
        if ($response->code !== 200) {
            $this->throwHttpError('downloadAttachment', $url, $response);
        }
        return $response->raw_body;
    }
}
?>
