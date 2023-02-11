<?php

namespace Tobycroft\AossSdk;

class AsmsCompleteRet
{
    public string $response;
    public mixed $data = [];
    protected mixed $error = null;

    public function __construct($response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
        } else {
            if ($json["code"] == "0") {
                $this->data = $json["data"];
            } else {
                $this->error = $json["echo"];
            }
        }
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

