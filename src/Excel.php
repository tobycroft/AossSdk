<?php

namespace Tobycroft\AossSdk;


class Excel extends Aoss
{
    protected string $send_path = "/v1/excel/index";

    public function __construct($token)
    {
        $this->token = $token;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path . "/dp";
        $this->send_url .= $this->send_token . $this->token;
    }

    public function send_excel($real_path, $mime_type, $file_name): ExcelCompleteRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $this->send_url);
        return new ExcelCompleteRet($response);
    }
}