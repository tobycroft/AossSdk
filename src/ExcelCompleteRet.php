<?php

namespace Tobycroft\AossSdk;

class ExcelCompleteRet
{
    public string $response;
    protected mixed $error = null;
    protected mixed $data = [];
    protected mixed $column = [];

    public function __construct($response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
        } else {
            if ($json["code"] == "0") {
                $this->data = $json["data"];
                if (count($this->data) > 0) {
                }
                foreach ($this->data[0] as $key => $value) {
                    $this->column[] = $key;
                }
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

    public function getExcelJson(): array
    {
        return $this->data;
    }

    public function getExcelColumn(): array
    {
        return $this->column;
    }
}