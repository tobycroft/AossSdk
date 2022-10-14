<?php

namespace Tobycroft\AossSdk\WechatRequestBuilder;

class WechatMode
{
    public string $GetWxacodeUnlimit_file = "unlimited_file";
    public string $GetWxacodeUnlimit_raw = "unlimited_raw";
    public string $GetWxacodeUnlimit_base64 = "unlimited_base64";
    public string $GetUserPhoneNumber = "getuserphonenumber";

    public function __construct(self $any)
    {
        return $this->$any;
    }
}