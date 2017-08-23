<?php

namespace TeamChris;


class Error
{
    private $message;
    private $status;

    public function __construct($status, $message)
    {
        $this->message = $message;
        $this->status = $status;
    }

    public function toArray()
    {
        return ["error" => [
            "status" => $this->status,
            "message" => $this->message
        ]];
    }
}