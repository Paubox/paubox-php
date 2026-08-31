<?php
namespace Paubox\Forms;

class Form
{
    private $id;
    private $title;
    private $description;
    private $formHtml;
    private $formJson;
    private $formCss;
    private $vanityUrl;
    private $version;
    private $active;
    private $customerId;
    private $oldFormId;
    private $signable;
    private $signatureConfirmationLabel;
    private $submissionCount;
    private $type;
    private $recipient;
    private $subscriptionListId;
    private $deleted;
    private $archived;
    private $createdAt;
    private $updatedAt;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getFormHtml() { return $this->formHtml; }
    public function setFormHtml($formHtml) { $this->formHtml = $formHtml; }

    public function getFormJson() { return $this->formJson; }
    public function setFormJson($formJson) { $this->formJson = $formJson; }

    public function getFormCss() { return $this->formCss; }
    public function setFormCss($formCss) { $this->formCss = $formCss; }

    public function getVanityUrl() { return $this->vanityUrl; }
    public function setVanityUrl($vanityUrl) { $this->vanityUrl = $vanityUrl; }

    public function getVersion() { return $this->version; }
    public function setVersion($version) { $this->version = $version; }

    public function getActive() { return $this->active; }
    public function setActive($active) { $this->active = $active; }

    public function getCustomerId() { return $this->customerId; }
    public function setCustomerId($customerId) { $this->customerId = $customerId; }

    public function getOldFormId() { return $this->oldFormId; }
    public function setOldFormId($oldFormId) { $this->oldFormId = $oldFormId; }

    public function getSignable() { return $this->signable; }
    public function setSignable($signable) { $this->signable = $signable; }

    public function getSignatureConfirmationLabel() { return $this->signatureConfirmationLabel; }
    public function setSignatureConfirmationLabel($label) { $this->signatureConfirmationLabel = $label; }

    public function getSubmissionCount() { return $this->submissionCount; }
    public function setSubmissionCount($submissionCount) { $this->submissionCount = $submissionCount; }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }

    public function getRecipient() { return $this->recipient; }
    public function setRecipient($recipient) { $this->recipient = $recipient; }

    public function getSubscriptionListId() { return $this->subscriptionListId; }
    public function setSubscriptionListId($subscriptionListId) { $this->subscriptionListId = $subscriptionListId; }

    public function getDeleted() { return $this->deleted; }
    public function setDeleted($deleted) { $this->deleted = $deleted; }

    public function getArchived() { return $this->archived; }
    public function setArchived($archived) { $this->archived = $archived; }

    public function getCreatedAt() { return $this->createdAt; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }

    public function getUpdatedAt() { return $this->updatedAt; }
    public function setUpdatedAt($updatedAt) { $this->updatedAt = $updatedAt; }
}
