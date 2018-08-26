<?php
namespace data;

/**
 *
 * @author ADMIN
 *        
 */
class Errors
{
    private $code;
    private $title;
    private $details;
    /**
     * @return $_code
     */
    public function  getCode() {
        return $this->code;
    }
    /**
     * @param code the code to set
     */
    public function setCode($code) {
        $this->code = $code;
    }
    /**
     * @return $_title
     */
    public function getTitle() {
        return $this->title;
    }
    /**
     * @param title the title to set
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    /**
     * @return $_details
     */
    public function getDetails() {
        return $this->details;
    }
    /**
     * @param details the details to set
     */
    public function setDetails($details) {
        $this->details = $details;
    }
    
}

