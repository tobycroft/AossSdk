<?php

namespace Tobycroft\AossSdk\Lcic\Ret;

class LcicRoomUrlRet
{
    public mixed $response;
    protected string $error;
    protected mixed $data;

    protected string $url_web;
    protected string $url_pc;

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
            $this->url_web = $this->data["web"];
            $this->url_pc = $this->data["pc"];
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

    public function GetUrlWeb(): string
    {
        return $this->url_web;
    }

    public function GetUrlPc(): string
    {
        return $this->url_pc;
    }
}