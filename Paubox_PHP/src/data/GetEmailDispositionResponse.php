<?php
namespace data;

class GetEmailDispositionResponse
{
    private  $_sourceTrackingId ;
    private  $_data;
    private  $_errors;
    /**
     * @return $_sourceTrackingId
     */
    public function getSourceTrackingId() {
        return $this->_sourceTrackingId;
    }
    /**
     * @param sourceTrackingId the sourceTrackingId to set
     */
    public function  setSourceTrackingId($sourceTrackingId) {
        $this->_sourceTrackingId = $sourceTrackingId;
    }
    /**
     * @return  $_errors
     */
    public function getErrors() {
        return $this->_errors;
    }
    /**
     * @param errors the errors to set
     */
    public function  setErrors($errors) {
        $this->_errors = $errors;
    }
    /**
     * @return  $_data
     */
    public function getData($data) {
        return $this->_data;
    }
    /**
     * @param data the data to set
     */
    public function setData($data) {
        $this->_data = $data;
    }
}

