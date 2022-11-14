<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\WechatRequestBuilder\WechatFunc;
use Tobycroft\AossSdk\WechatRequestBuilder\WechatMode;

class WechatOffi extends Aoss
{
    protected string $mode;

    public function uniform_send(string $openid, $template_id, $url, $data): WechatOffiPush
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$template_push);
        $postData = [
            'openid' => $openid,
            'url' => $url,
            'template_id' => $template_id,
            'data' => $data,
        ];
        return new WechatOffiPush(self::raw_post($this->send_url, $postData));
    }

    public function buildUrl($wechatFunc, $wechatMode)
    {
        $this->send_path = $wechatFunc . $wechatMode;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }


}
