<?php
namespace data;

/**
 *
 * @author ADMIN
 *        
 */
class Errors
{
    private $_code;
    private $_title;
    private $_details;
    /**
     * @return $_code
     */
    public function  getCode() {
        return $this->_code;
    }
    /**
     * @param code the code to set
     */
    public function setCode($code) {
        $this->_code = $code;
    }
    /**
     * @return $_title
     */
    public function getTitle() {
        return $this->_title;
    }
    /**
     * @param title the title to set
     */
    public function setTitle($title) {
        $this->_title = $title;
    }
    /**
     * @return $_details
     */
    public function getDetails() {
        return $this->_details;
    }
    /**
     * @param details the details to set
     */
    public function setDetails($details) {
        $this->_details = $details;
    }
    
}

