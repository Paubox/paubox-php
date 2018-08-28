<?php

class ApiHelper
{

    function callToAPIByPost($uri, $authHeader, $requestBody)
    {
        $header['accept'] = "application/json";

        if (null != $authHeader) {
            $header['Authorization'] = $authHeader;
        }

        // set the request data
        $body['data'] = $requestBody;

        $response = \Httpful\Request::post($uri)->sendsJson()
            ->addHeaders($header)
            ->body($requestBody)
            ->send();

        return $response->raw_body;
    }

    function callToAPIByGet($uri, $authHeader)
    {
        $header['accept'] = "application/json";
        if (null != $authHeader) {
            $header['Authorization'] = $authHeader;
        }

        $response = \Httpful\Request::get($uri)->sendsJson()
            ->addHeaders($header)
            ->send();

        return $response->raw_body;
    }
}

?>