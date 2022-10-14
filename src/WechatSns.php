<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\WechatRequestBuilder\WechatFunc;
use Tobycroft\AossSdk\WechatRequestBuilder\WechatMode;

class WechatSns extends WechatWxa
{

    public function jscode2Session(string $js_code, $grant_type): WechatSnsRet
    {
        $this->buildUrl(WechatFunc::Sns, WechatMode::$jscode2session);
        $postData = [
            "js_code" => $js_code,
            "grant_type" => $grant_type
        ];
        return new WechatSnsRet(self::raw_post($this->send_url, $postData));
    }


}
