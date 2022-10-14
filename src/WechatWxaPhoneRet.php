<?php

namespace Tobycroft\AossSdk;

class WechatWxaPhoneRet
{
    public mixed $response;
    protected string $error;
    protected mixed $data;

    public mixed $phoneNumber;
    public mixed $purePhoneNumber;
    public mixed $countryCode;
    public mixed $watermark;


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
            $this->phoneNumber = $this->data["phoneNumber"];
            $this->purePhoneNumber = $this->data["purePhoneNumber"];
            $this->countryCode = $this->data["countryCode"];
            $this->watermark = $this->data["watermark"];
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