<?php

namespace Tobycroft\AossSdk;

class WechatSnsRet
{
    public mixed $response;
    public mixed $unionid;
    public mixed $session_key;
    public mixed $openid;
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
            $this->unionid = $this->data["unionid"];
            $this->session_key = $this->data["session_key"];
            $this->openid = $this->data["openid"];
        } else {
            $this->error = $json["data"];
        }
    }

    public function unionid(): string
    {
        return $this->unionid;
    }

    public function session_key(): string
    {
        return $this->session_key;
    }

    public function openid(): string
    {
        return $this->openid;
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