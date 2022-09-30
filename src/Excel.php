<?php

use Tobycroft\AossSdk\Aoss;
use Tobycroft\AossSdk\ExcelCompleteRet;

class Excel extends Aoss
{
    protected string $send_path = "/v1/excel/index";

    public function __construct($token, $remote_url = "")
    {
        $this->send_url = $remote_url;
        $this->token = $token;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path . "/index";
            $this->send_url .= $this->send_token . $this->token;
        }
    }

    public function send_excel($send_url, $real_path, $mime_type, $file_name): ExcelCompleteRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $send_url);
        return new ExcelCompleteRet($response);
    }
}