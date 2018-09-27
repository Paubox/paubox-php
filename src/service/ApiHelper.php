<?php

class ApiHelper
{

    function callToAPIByPost($uri, $auth_header, $request_body)
    {
        $header['accept'] = "application/json";

        if (null != $auth_header) {
            $header['Authorization'] = $auth_header;
        }

        $response = \Httpful\Request::post($uri)->sendsJson()
            ->addHeaders($header)
            ->body($request_body)
            ->send();

        return $response->raw_body;
    }

    function callToAPIByGet($uri, $auth_header)
    {
        $header['accept'] = "application/json";
        if (null != $auth_header) {
            $header['Authorization'] = $auth_header;
        }

        $response = \Httpful\Request::get($uri)->sendsJson()
            ->addHeaders($header)
            ->send();

        return $response->raw_body;
    }
}

?>