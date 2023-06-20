<?php

namespace Tobycroft\AossSdk\Excel;

class ExcelCreateRet
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
                $this->data = $json["data"];
            } else {
                $this->error = $json["echo"];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getError(): mixed
    {
        return $this->error;
    }

    public function isSuccess(): bool
    {
        return empty($this->error);
    }

    public function file_url(): string
    {
        return $this->data;
    }
}