<?php
namespace data;

class Attachment
{
    private $_fileName;
    private $_contentType;
    private $_content;
    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;
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
    public function setContent($content)
    {
        $this->_content = $content;
    }

}

