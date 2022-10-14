<?php

namespace Tobycroft\AossSdk;

use GdImage;
use Tobycroft\AossSdk\WechatRequestBuilder\WechatFunc;
use Tobycroft\AossSdk\WechatRequestBuilder\WechatMode;

class WechatWxa extends Aoss
{
    protected string $mode;


    public function getuserphonenumber(string $code): WechatWxaPhoneRet
    {
        $this->buildUrl(WechatFunc::Wxa, WechatMode::$GetUserPhoneNumber);
        $ret = new WechatWxaPhoneRet(
            self::raw_post($this->send_url,
                [
                    "code" => $code,
                ]
            )
        );
        return $ret;
    }

    public function create_wxa_unlimited_file(string $data, $page): string|bool
    {
        $this->buildUrl(WechatFunc::Wxa, WechatMode::$GetWxacodeUnlimit_file);
        $ret = new WechatWxaUnlimitedRet(self::raw_post($this->send_url, [
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
        $this->buildUrl(WechatFunc::Wxa, WechatMode::$GetWxacodeUnlimit_base64);
        $ret = new WechatWxaUnlimitedRet(self::raw_post($this->send_url, [
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
        $this->buildUrl(WechatFunc::Wxa, WechatMode::$GetWxacodeUnlimit_raw);
        $ret = new WechatWxaUnlimitedRet(self::raw_post($this->send_url, [
            "data" => $data,
            "page" => $page,
        ]));
        if (!$ret->isSuccess()) {
            return false;
        }
        return imagecreatefromstring($ret->response);
    }

}
