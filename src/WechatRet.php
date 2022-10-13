<?php

namespace Tobycroft\AossSdk;

class WechatRet
{
    public mixed $error = null;
    public mixed $data = [];

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
            return $this;
        }
        if ($json["code"] == "0") {
            $this->data = $json["data"];
        } else {
            $this->error = $json["data"];
        }
        return $this;
    }

    public function file($response): string
    {
        return $this->data;
    }
}