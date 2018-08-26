<?php
namespace Model;


class Content
{
    private $plainText;
    private $htmlText;
    /**
     * @return mixed
     */
    public function getPlainText()
    {
        return $this->plainText;
    }

    /**
     * @param mixed $plainText
     */
    public function setPlainText($plainText)
    {
        $this->plainText = $plainText;
    }

    /**
     * @return mixed
     */
    public function getHtmlText()
    {
        return $this->htmlText;
    }

    /**
     * @param mixed $htmlText
     */
    public function setHtmlText($htmlText)
    {
        $this->htmlText = $htmlText;
    }

}

