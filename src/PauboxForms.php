<?php
namespace Paubox;

use Paubox\Forms\PauboxFormsException;

class PauboxForms
{
    const DEFAULT_BASE_URL = "https://api.paubox.com/v1/forms";
    const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private $baseUrl;
    private $apiKey;

    public function __construct($apiKey = null, $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: (getenv('PAUBOX_FORMS_API_KEY') ?: null);
        $resolvedBaseUrl = $baseUrl
            ?: (getenv('PAUBOX_FORMS_BASE_URL') ?: self::DEFAULT_BASE_URL);
        $this->baseUrl = rtrim($resolvedBaseUrl, '/');
    }

    public function getForm($formId)
    {
        $this->assertUuid($formId, 'formId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/public/form_data/" . rawurlencode($formId);
        $resp = $api->callToAPIByGet($url, null);
        $form = json_decode($resp);
        if (is_null($form) || !isset($form->id)) {
            throw new PauboxFormsException("Form not found or invalid response.", null, $url, $resp);
        }
        return $form;
    }

    public function submitForm($formId, Forms\FormSubmission $submission)
    {
        $this->assertUuid($formId, 'formId');
        if (is_null($submission->getFormData())) {
            throw new PauboxFormsException("FormSubmission form_data cannot be null.");
        }

        $body = ['form_data' => $submission->getFormData()];

        $attachments = $submission->getAttachments();
        if (!empty($attachments)) {
            $jsonAttachments = [];
            foreach ($attachments as $att) {
                $jsonAttachments[] = [
                    'name'    => $att->getName(),
                    'content' => $att->getContent()
                ];
            }
            $body['attachments'] = $jsonAttachments;
        }

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId) . "/submissions";
        $response = $api->callToAPIByPostWithResponse($url, null, $body);

        if ($response->code !== 201) {
            $this->throwHttpError('submitForm', $url, $response);
        }

        return true;
    }

    private function assertUuid($value, $paramName)
    {
        if (!is_string($value) || !preg_match(self::UUID_PATTERN, $value)) {
            throw new PauboxFormsException(
                "$paramName must be a UUID string."
            );
        }
    }

    private function throwHttpError($operation, $url, $response)
    {
        $status = isset($response->code) ? $response->code : null;
        $body = isset($response->raw_body) ? $response->raw_body : null;
        throw new PauboxFormsException(
            "$operation failed: HTTP " . ($status === null ? 'unknown' : $status),
            $status,
            $url,
            $body
        );
    }

    private function getAuthHeader()
    {
        if (empty($this->apiKey)) {
            throw new PauboxFormsException(
                "A scoped API key with the 'forms' scope is required for this endpoint. "
                . "Pass it to the PauboxForms constructor or set the PAUBOX_FORMS_API_KEY environment variable. "
                . "Note: this is a distinct credential from the transactional Email API key (PAUBOX_API_KEY); "
                . "do not reuse the same value."
            );
        }
        return "Bearer " . $this->apiKey;
    }

    private function buildQuery($params, $allowedKeys)
    {
        $query = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $params) && !is_null($params[$key])) {
                $value = $params[$key];
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $query[$key] = $value;
            }
        }
        return $query;
    }

    public function getFormById($formId)
    {
        $this->assertUuid($formId, 'formId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('getFormById', $url, $response);
        }

        $decoded = json_decode($response->raw_body);
        if (is_null($decoded) || !isset($decoded->data)) {
            throw new PauboxFormsException(
                "getFormById returned an invalid response shape.",
                $response->code,
                $url,
                $response->raw_body
            );
        }
        return $decoded->data;
    }

    public function listForms($params = [])
    {
        $allowedKeys = ['customer_id', 'form_id', 'search', 'order', 'order_by', 'archived', 'active', 'page', 'items'];
        $query = $this->buildQuery($params, $allowedKeys);

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms";
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('listForms', $url, $response);
        }

        return json_decode($response->raw_body);
    }

    public function createForm(Forms\Form $form)
    {
        if (is_null($form->getTitle())) {
            throw new PauboxFormsException("Form title cannot be null.");
        }
        if (is_null($form->getFormJson())) {
            throw new PauboxFormsException("Form formJson cannot be null.");
        }
        if (is_null($form->getCustomerId())) {
            throw new PauboxFormsException("Form customerId cannot be null.");
        }

        $body = [
            'title'       => $form->getTitle(),
            'form_json'   => $form->getFormJson(),
            'customer_id' => $form->getCustomerId()
        ];

        if (!is_null($form->getDescription())) {
            $body['description'] = $form->getDescription();
        }
        if (!is_null($form->getFormHtml())) {
            $body['form_html'] = $form->getFormHtml();
        }
        if (!is_null($form->getFormCss())) {
            $body['form_css'] = $form->getFormCss();
        }
        if (!is_null($form->getRecipient())) {
            $body['recipient'] = $form->getRecipient();
        }
        if (!is_null($form->getSignatureConfirmationLabel())) {
            $body['signature_confirmation_label'] = $form->getSignatureConfirmationLabel();
        }
        if (!is_null($form->getSubscriptionListId())) {
            $body['subscription_list_id'] = $form->getSubscriptionListId();
        }
        if (!is_null($form->getType())) {
            $body['type'] = $form->getType();
        }

        $body['signable'] = (bool)$form->getSignable();
        $body['active'] = (bool)$form->getActive();
        $body['version'] = $form->getVersion() ?: 1;
        $body['submission_count'] = $form->getSubmissionCount() ?: 0;

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $body);

        if ($response->code !== 200) {
            $this->throwHttpError('createForm', $url, $response);
        }

        $decoded = json_decode($response->raw_body);
        if (is_null($decoded) || !isset($decoded->id)) {
            throw new PauboxFormsException(
                "createForm returned an invalid response shape.",
                $response->code,
                $url,
                $response->raw_body
            );
        }
        return $decoded->id;
    }

    public function updateForm($formId, $attributes)
    {
        $this->assertUuid($formId, 'formId');
        $allowedKeys = ['title', 'description', 'form_json', 'vanity_url', 'recipient', 'active', 'subscription_list_id'];
        $booleanKeys = ['active'];
        $body = [];
        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $attributes)) {
                continue;
            }
            $value = $attributes[$key];
            if (is_null($value)) {
                continue;
            }
            if (in_array($key, $booleanKeys, true)) {
                if (is_bool($value)) {
                    $body[$key] = $value;
                } elseif (is_string($value) && strtolower($value) === 'true') {
                    $body[$key] = true;
                } elseif (is_string($value) && strtolower($value) === 'false') {
                    $body[$key] = false;
                } elseif (is_int($value)) {
                    $body[$key] = (bool)$value;
                } else {
                    throw new PauboxFormsException(
                        "updateForm attribute '$key' must be boolean-shaped (bool, 'true'/'false', or int)."
                    );
                }
                continue;
            }
            $body[$key] = $value;
        }

        if (empty($body)) {
            throw new PauboxFormsException(
                "No updatable attributes provided. Allowed non-null keys: " . implode(', ', $allowedKeys)
            );
        }

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId);
        $response = $api->callToAPIByPutWithResponse($url, $this->getAuthHeader(), $body);

        if ($response->code !== 200) {
            $this->throwHttpError('updateForm', $url, $response);
        }

        return json_decode($response->raw_body);
    }

    public function archiveForm($formId)
    {
        $this->assertUuid($formId, 'formId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId) . "/archive";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), new \stdClass());

        if ($response->code !== 200) {
            $this->throwHttpError('archiveForm', $url, $response);
        }

        return true;
    }

    public function unarchiveForm($formId)
    {
        $this->assertUuid($formId, 'formId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId) . "/unarchive";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), new \stdClass());

        if ($response->code !== 200) {
            $this->throwHttpError('unarchiveForm', $url, $response);
        }

        return true;
    }

    public function copyForm($formId, $newTitle)
    {
        $this->assertUuid($formId, 'formId');
        $body = [
            'form_id' => $formId,
            'title'   => $newTitle
        ];

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/copy";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $body);

        if ($response->code !== 200) {
            $this->throwHttpError('copyForm', $url, $response);
        }

        return json_decode($response->raw_body);
    }

    public function getFormStats($params = [])
    {
        $query = $this->buildQuery($params, ['customer_id']);

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/stats";
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('getFormStats', $url, $response);
        }

        return json_decode($response->raw_body);
    }

    public function listSubmissions($formId, $params = [])
    {
        $this->assertUuid($formId, 'formId');
        $allowedKeys = ['page', 'items', 'order', 'order_by', 'submission_id'];
        $query = $this->buildQuery($params, $allowedKeys);

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId) . "/submissions";
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('listSubmissions', $url, $response);
        }

        return json_decode($response->raw_body);
    }

    public function getSubmissionsCsv($formId)
    {
        $this->assertUuid($formId, 'formId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId) . "/submissions/submission-csv";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('getSubmissionsCsv', $url, $response);
        }

        return $response->raw_body;
    }

    public function getSubmissionCsv($formId, $submissionId)
    {
        $this->assertUuid($formId, 'formId');
        $this->assertUuid($submissionId, 'submissionId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId)
            . "/submissions/submission-csv/" . rawurlencode($submissionId);
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('getSubmissionCsv', $url, $response);
        }

        return $response->raw_body;
    }

    public function getSubmissionPdf($formId, $submissionId)
    {
        $this->assertUuid($formId, 'formId');
        $this->assertUuid($submissionId, 'submissionId');
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . rawurlencode($formId)
            . "/submissions/" . rawurlencode($submissionId) . "/submission-pdf";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            $this->throwHttpError('getSubmissionPdf', $url, $response);
        }

        return $response->raw_body;
    }
}
?>
