<?php

namespace Tobycroft\AossSdk;

class ExcelCompleteRet
{
    public string $response;
    public mixed $error = null;
    public mixed $data = [];
    public mixed $column = [];

    public function __construct($response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
        } else {
            if ($json["code"] == "0") {
                $this->data = $json["data"];
                foreach ($this->data as $key => $value) {
                    $this->column[] = $value;
                }
            } else {
                $this->error = $json["data"];
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

    public function getExcelJson(): array
    {
        return $this->data;
    }

    public function getExcelColumn(): array
    {
        return $this->column;
    }
}