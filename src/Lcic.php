<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\Lcic\Ret\LcicUserAuto;
use Tobycroft\AossSdk\Lcic\Url\LcicRouter;

class Lcic extends Aoss
{

    public function __construct()
    {

    }

    public function CreateUser(string $Name, $OriginId, $Avatar): LcicUserAuto
    {
        $ret = new LcicUserAuto(
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