<?php
namespace data;

/**
 *
 * @author ADMIN
 *        
 */
class MessageData
{
    private $_message;
    
    /**
     * @return $_message
     */
    public function getMessage() {
        return $this->_message;
    }
    
    /**
     * @param message the message to set
     */
    public function setMessage($message) {
        $this->_message = $message;
    }
}

