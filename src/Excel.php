<?php

namespace Tobycroft\AossSdk;


class Excel extends Aoss
{
    protected string $send_path = "/v1/excel";

    public function __construct($token)
    {
        $this->token = $token;

        $this->send_url = $this->remote_url;
    }

    public function send_excel($real_path, $mime_type, $file_name): ExcelCompleteRet
    {
        $this->send_url .= $this->send_path . '/index/dp';
        $this->send_url .= $this->send_token . $this->token;
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $this->send_url);
        return new ExcelCompleteRet($response);
    }

    public function send_md5($md5): ExcelCompleteRet
    {
        $this->send_url .= $this->send_path . '/search/md5';
        $this->send_url .= $this->send_token . $this->token;
        $response = self::raw_post($this->send_url, ["md5" => $md5]);
        return new ExcelCompleteRet($response);
    }
}