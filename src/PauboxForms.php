<?php
namespace Paubox;

class PauboxForms
{
    private $baseUrl = "https://apx.paubox.com/forms";
    private $apiKey;

    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey ?: getenv('PAUBOX_API_KEY');
    }

    public function getForm($formId)
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/public/form_data/" . $formId;
        $resp = $api->callToAPIByGet($url, null);
        $form = json_decode($resp);
        if (is_null($form) || !isset($form->id)) {
            throw new \Exception("Form not found or invalid response.");
        }
        return $form;
    }

    public function submitForm($formId, Forms\FormSubmission $submission)
    {
        if (is_null($submission->getFormData())) {
            throw new \Exception("FormSubmission form_data cannot be null.");
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
        $url = $this->baseUrl . "/api/forms/" . $formId . "/submissions";
        $response = $api->callToAPIByPostWithResponse($url, null, $body);

        if ($response->code !== 201) {
            throw new \Exception("Form submission failed: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return true;
    }

    private function getAuthHeader()
    {
        if (empty($this->apiKey)) {
            throw new \Exception("A scoped API key with the 'forms' scope is required for this endpoint. Pass it to the PauboxForms constructor or set the PAUBOX_API_KEY environment variable.");
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
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId;
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            throw new \Exception("Failed to get form: HTTP " . $response->code . " - " . $response->raw_body);
        }

        $decoded = json_decode($response->raw_body);
        if (is_null($decoded) || !isset($decoded->data)) {
            throw new \Exception("Form not found or invalid response.");
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
            throw new \Exception("Failed to list forms: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return json_decode($response->raw_body);
    }

    public function createForm(Forms\Form $form)
    {
        if (is_null($form->getTitle())) {
            throw new \Exception("Form title cannot be null.");
        }
        if (is_null($form->getFormJson())) {
            throw new \Exception("Form formJson cannot be null.");
        }
        if (is_null($form->getCustomerId())) {
            throw new \Exception("Form customerId cannot be null.");
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
            throw new \Exception("Failed to create form: HTTP " . $response->code . " - " . $response->raw_body);
        }

        $decoded = json_decode($response->raw_body);
        if (is_null($decoded) || !isset($decoded->id)) {
            throw new \Exception("Invalid response from create form: " . $response->raw_body);
        }
        return $decoded->id;
    }

    public function updateForm($formId, $attributes)
    {
        $allowedKeys = ['title', 'description', 'form_json', 'vanity_url', 'recipient', 'active', 'subscription_list_id'];
        $body = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $attributes)) {
                $body[$key] = $attributes[$key];
            }
        }

        if (empty($body)) {
            throw new \Exception("No updatable attributes provided. Allowed keys: " . implode(', ', $allowedKeys));
        }

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId;
        $response = $api->callToAPIByPutWithResponse($url, $this->getAuthHeader(), $body);

        if ($response->code !== 200) {
            throw new \Exception("Failed to update form: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return json_decode($response->raw_body);
    }

    public function archiveForm($formId)
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId . "/archive";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), new \stdClass());

        if ($response->code !== 200) {
            throw new \Exception("Failed to archive form: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return true;
    }

    public function unarchiveForm($formId)
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId . "/unarchive";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), new \stdClass());

        if ($response->code !== 200) {
            throw new \Exception("Failed to unarchive form: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return true;
    }

    public function copyForm($formId, $newTitle)
    {
        $body = [
            'form_id' => $formId,
            'title'   => $newTitle
        ];

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/copy";
        $response = $api->callToAPIByPostWithResponse($url, $this->getAuthHeader(), $body);

        if ($response->code !== 200) {
            throw new \Exception("Failed to copy form: HTTP " . $response->code . " - " . $response->raw_body);
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
            throw new \Exception("Failed to get form stats: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return json_decode($response->raw_body);
    }

    public function listSubmissions($formId, $params = [])
    {
        $allowedKeys = ['page', 'items', 'order', 'order_by', 'submission_id'];
        $query = $this->buildQuery($params, $allowedKeys);

        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId . "/submissions";
        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            throw new \Exception("Failed to list submissions: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return json_decode($response->raw_body);
    }

    public function getSubmissionsCsv($formId)
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId . "/submissions/submission-csv";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            throw new \Exception("Failed to get submissions CSV: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return $response->raw_body;
    }

    public function getSubmissionCsv($formId, $submissionId)
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId . "/submissions/submission-csv/" . $submissionId;
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            throw new \Exception("Failed to get submission CSV: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return $response->raw_body;
    }

    public function getSubmissionPdf($formId, $submissionId)
    {
        $api = new Service\ApiHelper();
        $url = $this->baseUrl . "/api/forms/" . $formId . "/submissions/" . $submissionId . "/submission-pdf";
        $response = $api->callToAPIByGetWithResponse($url, $this->getAuthHeader());

        if ($response->code !== 200) {
            throw new \Exception("Failed to get submission PDF: HTTP " . $response->code . " - " . $response->raw_body);
        }

        return $response->raw_body;
    }
}
?>
