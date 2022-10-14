<?php

namespace Tobycroft\AossSdk\WechatRequestBuilder;

class WechatFunc
{

    public const Wxa = "/v1/wechat/wxa/";
    public const WxaBusiness = "/v1/wechat/wxa/business/";

    public function __construct(self $any)
    {
        return $any;
    }
}