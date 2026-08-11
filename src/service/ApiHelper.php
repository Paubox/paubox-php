<?php
namespace Paubox\Service;

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

    function callToAPIByPostWithResponse($uri, $auth_header, $request_body)
    {
        $header['accept'] = "application/json";
        if (null != $auth_header) {
            $header['Authorization'] = $auth_header;
        }

        $response = \Httpful\Request::post($uri)->sendsJson()
            ->addHeaders($header)
            ->body($request_body)
            ->send();

        return $response;
    }

    function callToAPIByGetWithResponse($uri, $auth_header)
    {
        $header['accept'] = "application/json";
        if (null != $auth_header) {
            $header['Authorization'] = $auth_header;
        }

        $response = \Httpful\Request::get($uri)->sendsJson()
            ->addHeaders($header)
            ->send();

        return $response;
    }

    function callToAPIByPutWithResponse($uri, $auth_header, $request_body)
    {
        $header['accept'] = "application/json";
        if (null != $auth_header) {
            $header['Authorization'] = $auth_header;
        }

        $response = \Httpful\Request::put($uri)->sendsJson()
            ->addHeaders($header)
            ->body($request_body)
            ->send();

        return $response;
    }
}

?>