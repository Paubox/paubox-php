<?php
namespace Paubox\Forms;

class PauboxFormsException extends \Exception
{
    private $statusCode;
    private $url;
    private $responseBody;

    public function __construct($message, $statusCode = null, $url = null, $responseBody = null, $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->url = $url;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
