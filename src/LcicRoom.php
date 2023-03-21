<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\LcicRequestBuilder\LcicRouter;

class LcicRoom extends Aoss
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

    public function create_room(string $name)
    {

    }
}