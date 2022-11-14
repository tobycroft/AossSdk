<?php

namespace Tobycroft\AossSdk;

class WechatOffiUserList
{
    public mixed $response;
    public array $openids;
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
            $this->openids = $this->data;
        } else {
            $this->error = $json["echo"];
        }
    }


    public function session_key(): string
    {
        return $this->session_key;
    }

    public function openids(): string
    {
        return $this->openids;
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