<?php

namespace Tobycroft\AossSdk\Lcic\Ret;

class LcicUserAutoRet
{
    public mixed $response;
    protected string $error;
    protected mixed $data;

    protected string $UserId;
    protected string $Token;

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
            $this->UserId = $this->data["UserId"];
            $this->Token = $this->data["Token"];
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

    public function GetToken(): string
    {
        return $this->Token;
    }

    public function GetUserId(): string
    {
        return $this->UserId;
    }
}