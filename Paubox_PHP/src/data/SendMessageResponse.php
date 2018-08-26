<?php
namespace Model;

class SendMessageResponse
{
    private $sourceTrackingId;
    private $data;
    private $errors;
    /**
     * @return mixed
     */
    public function getSourceTrackingId()
    {
        return $this->sourceTrackingId;
    }

    /**
     * @param mixed $sourceTrackingId
     */
    public function setSourceTrackingId($sourceTrackingId)
    {
        $this->sourceTrackingId = $sourceTrackingId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

}

