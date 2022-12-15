<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\WechatRequestBuilder\WechatRouter;

class WechatTicket extends Aoss
{
    public function signature(string $noncestr, $timestamp, $url): WechatTicketSignatureRet
    {
        $this->buildUrl(WechatRouter::$ticket_signature);
        $ret = new WechatTicketSignatureRet(
            self::raw_post($this->send_url,
                [
                    'noncestr' => $noncestr,
                    'timestamp' => $timestamp,
                    'url' => $url,
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