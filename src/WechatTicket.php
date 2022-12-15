<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\WechatRequestBuilder\WechatRouter;

class WechatTicket extends Aoss
{
    public function signature(string $code): WechatWxaPhoneRet
    {
        $this->buildUrl(WechatRouter::$ticket_signature);
        $ret = new WechatWxaPhoneRet(
            self::raw_post($this->send_url,
                [
                    'code' => $code,
                ]
            )
        );
        return $ret;
    }

    public function buildUrl($wechatMode)
    {
        $this->send_path = $wechatMode;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }


}