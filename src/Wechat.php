<?php

namespace Tobycroft\AossSdk;

use GdImage;

class Wechat extends Aoss
{
    protected string $mode;

    public function __construct($token, $wechatFunc, $wechatMode, WechatRequestBuilder\WechatFunc $func, WechatRequestBuilder\WechatMode $mode)
    {
        $this->token = $token;

        $this->send_path = $func->$wechatFunc . $mode->$wechatMode;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }

    public function create_wxa_unlimited_file(string $data, $page): GdImage|false
    {
        $response = self::raw_post($this->send_url, [
            "data" => $data,
            "page" => $page,
        ]);
        return imagecreatefromstring($response);
    }

}
