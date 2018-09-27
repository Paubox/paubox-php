<?php
namespace Paubox\Mail;

class SendMessageResponse
{
    private $_sourceTrackingId;
    private $_data;
    private $_errors;
    /**
     * @return mixed
     */
    public function getSourceTrackingId()
    {
        return $this->_sourceTrackingId;
    }

    /**
     * @param mixed $sourceTrackingId
     */
    public function setSourceTrackingId($sourceTrackingId)
    {
        $this->_sourceTrackingId = $sourceTrackingId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors)
    {
        $this->_errors = $errors;
    }

}

