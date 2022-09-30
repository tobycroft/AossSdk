<?php

namespace Tobycroft\AossSdk;

class Image extends Aoss
{
    protected string $send_path = "/v1/image/create";

    public function __construct($token, $remote_url = "")
    {
        $this->send_url = $remote_url;
        $this->token = $token;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path . "/canvas";
            $this->send_url .= $this->send_token . $this->token;
        }
    }

    public function send_image($send_url, $real_path, $mime_type, $file_name): ImageRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $send_url);
        return new ImageRet($response);
    }
}