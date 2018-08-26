<?php
namespace data;

/**
 *
 * @author ADMIN
 *        
 */
class MessageDeliveries
{
    private $_recipient ;
    private $_status ;
    /**
     * @return $_recipient
     */
    public function  getRecipient() {
        return $this->_recipient;
    }
    /**
     * @param recipient the recipient to set
     */
    public function setRecipient($recipient) {
        $this->_recipient = $recipient;
    }
    /**
     * @return  $_status
     */
    public function getStatus() {
        return $this->_status;
    }
    /**
     * @param status the status to set
     */
    public function setStatus($status) {
        $this->_status = $status;
    }
}

