<?php
namespace Paubox\Mail;

class MessageDetails
{

    private $_id;
    private $_messageDeliveries;
    /**
     * @return  $_id
     */
    public function getId() {
        return $this->_id;
    }
    /**
     * @param id the id to set
     */
    public function  setId($id) {
        $this->_id = $id;
    }
    /**
     * @return  $_messageDeliveries
     */
    public function getMessageDeliveries() {
        return $this->_messageDeliveries;
    }
    /**
     * @param messageDeliveries the messageDeliveries to set
     */
    public function  setMessageDeliveries($messageDeliveries) {
        $this->_messageDeliveries = $messageDeliveries;
    }

}

