<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\WechatRequestBuilder\WechatFunc;
use Tobycroft\AossSdk\WechatRequestBuilder\WechatMode;

class WechatTicket extends Aoss
{
    public function signature(string $code): WechatWxaPhoneRet
    {
        $this->buildUrl(WechatFunc::Wxa, WechatMode::$GetUserPhoneNumber);
        $ret = new WechatWxaPhoneRet(
            self::raw_post($this->send_url,
                [
                    'code' => $code,
                ]
            )
        );
        return $ret;
    }

    public function buildUrl($wechatFunc, $wechatMode)
    {
        $this->send_path = $wechatFunc . $wechatMode;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }

}