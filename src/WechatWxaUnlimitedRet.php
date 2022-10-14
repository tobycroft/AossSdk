<?php

namespace Tobycroft\AossSdk;

class WechatWxaUnlimitedRet
{
    public mixed $response;
    protected string $error;
    protected mixed $data;

    public function __construct(string $response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
            return $this;
        }
        if ($json["code"] == "0") {
            $this->data = $json["data"];
        } else {
            $this->error = $json["echo"];
        }
    }

    public function file(): string
    {
        return $this->data;
    }

    public function base64(): string
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getError(): string
    {
        return $this->error;
    }

    public function isSuccess(): bool
    {
        return empty($this->error);
    }
}