<?php

namespace Tobycroft\AossSdk;

class WechatSns extends Aoss
{
    protected string $mode;

    public function __construct($token)
    {
        $this->token = $token;

        $this->send_path = "/v1/wechat/sns/jscode";

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }

    public function jscode2Session(string $js_code, $grant_type): WechatSnsRet
    {
        $postData = [
            "js_code" => $js_code,
            "grant_type" => $grant_type
        ];
        return new WechatSnsRet(self::raw_post($this->send_url, $postData));
    }


}
