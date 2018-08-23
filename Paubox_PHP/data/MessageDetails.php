<?php
namespace data;

/**
 *
 * @author ADMIN
 *        
 */
class MessageDetails
{

    private $_id;
    private $_message_deliveries;
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
     * @return  $_message_deliveries
     */
    public function getMessage_deliveries() {
        return $this->_message_deliveries;
    }
    /**
     * @param message_deliveries the message_deliveries to set
     */
    public function  setMessage_deliveries($message_deliveries) {
        $this->_message_deliveries = $message_deliveries;
    }
    
}

