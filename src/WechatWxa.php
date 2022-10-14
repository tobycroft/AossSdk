<?php

namespace Tobycroft\AossSdk;

use GdImage;

class WechatWxa extends Aoss
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

    public function create_wxa_unlimited_file(string $data, $page): string|bool
    {
        $ret = new WechatWxaRet(self::raw_post($this->send_url, [
            "data" => $data,
            "page" => $page,
        ]));
        if (!$ret->isSuccess()) {
            return false;
        }
        return $ret->file();
    }

    public function create_wxa_unlimited_base64(string $data, $page): string|bool
    {
        $ret = new WechatWxaRet(self::raw_post($this->send_url, [
            "data" => $data,
            "page" => $page,
        ]));
        if (!$ret->isSuccess()) {
            return false;
        }
        return $ret->base64();
    }

    public function create_wxa_unlimited_raw(string $data, $page): GdImage|bool
    {
        $ret = new WechatWxaRet(self::raw_post($this->send_url, [
            "data" => $data,
            "page" => $page,
        ]));
        if (!$ret->isSuccess()) {
            return false;
        }
        return imagecreatefromstring($ret->response);
    }

}
