<?php


class Response
{
    private $data;
    private $code;
    private $message;

    function __construct($data, $code = 0, $message = 'ok')
    {
        $this->data = $data;
        $this->code = $code;
        $this->message = $message;
    }

    function send()
    {
        return [
            'data' => $this->data,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}