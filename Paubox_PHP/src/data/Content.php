<?php
namespace data;


class Content
{
    private $_plainText;
    private $_htmlText;
    /**
     * @return mixed
     */
    public function getPlainText()
    {
        return $this->_plainText;
    }

    /**
     * @param mixed $plainText
     */
    public function setPlainText($plainText)
    {
        $this->_plainText = $plainText;
    }

    /**
     * @return mixed
     */
    public function getHtmlText()
    {
        return $this->_htmlText;
    }

    /**
     * @param mixed $htmlText
     */
    public function setHtmlText($htmlText)
    {
        $this->_htmlText = $htmlText;
    }

}

