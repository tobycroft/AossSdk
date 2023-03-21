<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\LcicRequestBuilder\LcicRouter;

class Lcic extends Aoss
{

    public function __construct()
    {

    }

    public function CreateUser(string $Name, $OriginId, $Avatar): WechatWxaPhoneRet
    {
        $ret = new WechatWxaPhoneRet(
            self::raw_post(LcicRouter::lcic_user_auto,
                [
                    'Name' => $Name,
                    'OriginId' => $OriginId,
                    'Avatar' => $Avatar,
                ]
            )
        );
        return $ret;
    }

    public function CreateRoom(string $name)
    {

    }
}