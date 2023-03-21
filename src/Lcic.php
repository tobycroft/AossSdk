<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\Lcic\Ret\LcicRoomCreateRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicUserAutoRet;
use Tobycroft\AossSdk\Lcic\Url\LcicRouter;

class Lcic extends Aoss
{

    public function __construct()
    {

    }

    public function CreateUser(string $Name, $OriginId, $Avatar): LcicUserAutoRet
    {

        $ret = new LcicUserAutoRet(
            self::raw_post($this->remote_url . LcicRouter::lcic_user_auto . $this->send_token . $this->token,
                [
                    'Name' => $Name,
                    'OriginId' => $OriginId,
                    'Avatar' => $Avatar,
                ]
            )
        );
        return $ret;
    }

    public function CreateRoom(string $TeacherId, $StartTime, $EndTime, $Name): LcicRoomCreateRet
    {
        $ret = new LcicRoomCreateRet(
            self::raw_post($this->remote_url . LcicRouter::lcic_room_create . $this->send_token . $this->token,
                [
                    'TeacherId' => $TeacherId,
                    'StartTime' => $StartTime,
                    'EndTime' => $EndTime,
                    'Name' => $Name,
                ]
            )
        );
        return $ret;
    }
}