<?php

use Tobycroft\AossSdk\Aoss;

class Excel extends Aoss
{
    protected string $send_path = "/v1/excel/index";

    public function __construct($token, $remote_url = "")
    {
        $this->send_url = $remote_url;
        $this->token = $token;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path . "/index";
            $this->send_url .= $this->send_token . $this->token;
        }
    }
}