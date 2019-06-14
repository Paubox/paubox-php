<?php
namespace Paubox\Mail;


class Message
{
    private $_recipients = array();
    private $_cc = array();
    private $_bcc = array();
    private $_header;
    private $_allowNonTLS = false;
    private $_content;
    private $_attachments  = array();
    private $_forceSecureNotification;
    
    /**
     * @return mixed
     */
    public function getRecipients()
    {
        return $this->_recipients;
    }

    /**
     * @param mixed $recipients
     */
    public function setRecipients(array $recipients)
    {
        $this->_recipients = $recipients;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->_bcc;
    }

    /**
     * @param mixed $bcc
     */
    public function setBcc(array $bcc)
    {
        $this->_bcc = $bcc;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * @param mixed $header
     */
    public function setHeader(Header $header)
    {
        $this->_header = $header;
    }

    /**
     * @return boolean
     */
    public function isAllowNonTLS()
    {
        return $this->_allowNonTLS;
    }

    /**
     * @param boolean $allowNonTLS
     */
    public function setAllowNonTLS($allowNonTLS)
    {
        $this->_allowNonTLS = $allowNonTLS;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @param mixed $content
     */
    public function setContent(Content $content)
    {
        $this->_content = $content;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments(array $attachments)
    {
        $this->_attachments = $attachments;
    }

    /**
     * @return mixed
     */
    public function getForceSecureNotification()
    {
        return $this->_forceSecureNotification;
    }
    
    /**
     * @param mixed $forceSecureNotification
     */
    public function setForceSecureNotification($forceSecureNotification){
        $this->_forceSecureNotification = $forceSecureNotification;
    }
    
    /**
     * @return mixed
     */
    public function getCc()
    {
        return $this->_cc;
    }
    
    /**
     * @param mixed $cc
     */
    public function setCc(array $cc)
    {
        $this->_cc = $cc;
    }

}

