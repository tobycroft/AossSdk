<?php

namespace Tobycroft\AossSdk;

class AossSimpleRet
{
    public string $response;
    protected mixed $error = null;
    protected mixed $data = "";

    public function __construct($response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
        } else {
            if ($json["code"] == "0") {
                $this->data = $json["data"]["url"];
            } else {
                $this->error = $json["echo"];
            }
        }
    }

    public function url()
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return empty($this->error);
    }

    /**
     * @return mixed
     */
    public function getError(): mixed
    {
        return $this->error;
    }
}