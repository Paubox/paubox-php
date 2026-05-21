<?php
namespace Paubox\Forms;

class FormSubmission
{
    private $formData;
    private $attachments = [];

    public function getFormData() { return $this->formData; }
    public function setFormData($formData) { $this->formData = $formData; }

    public function getAttachments() { return $this->attachments; }
    public function setAttachments($attachments) { $this->attachments = $attachments; }
}
?>
