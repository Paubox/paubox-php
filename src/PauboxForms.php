<?php
namespace Paubox;

class PauboxForms
{
    private $baseUrl = "https://next.paubox.com";

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
}
?>
