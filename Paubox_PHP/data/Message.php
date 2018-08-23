<?php
namespace Model;


class Message 
{
    private $recipients = array();
    private $bcc = array();
    private $header;
    private $allowNonTLS = false;
    private $content;
    private $attachments  = array();
    /**
     * @return mixed
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param mixed $recipients
     */
    public function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param mixed $bcc
     */
    public function setBcc(array $bcc)
    {
        $this->bcc = $bcc;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     */
    public function setHeader(Header $header)
    {
        $this->header = $header;
    }

    /**
     * @return boolean
     */
    public function isAllowNonTLS()
    {
        return $this->allowNonTLS;
    }

    /**
     * @param boolean $allowNonTLS
     */
    public function setAllowNonTLS($allowNonTLS)
    {
        $this->allowNonTLS = $allowNonTLS;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
    }    


}

