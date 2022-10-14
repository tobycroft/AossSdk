<?php

namespace Tobycroft\AossSdk;

class WechatWxaSchemeRet
{
    public mixed $response;
    public mixed $openlink;
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
            $this->openlink = $this->data["openlink"];
        } else {
            $this->error = $json["echo"];
        }
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