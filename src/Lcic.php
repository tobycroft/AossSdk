<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\Lcic\Ret\LcicRoomCreateRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicRoomModifyRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicRoomUrlRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicUserAutoRet;
use Tobycroft\AossSdk\Lcic\Url\LcicRouter;

class Lcic extends Aoss
{

    public function __construct()
    {

    }

    /**
     * @param string $Name 用户在直播间的显示名称
     * @param $OriginId 用户在你系统中的标识符
     * @param $Avatar 用户头像url地址
     * @return LcicUserAutoRet
     */
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

    /**
     * @param string $TeacherId 教师在你系统中的id
     * @param $StartTime 开始时间int
     * @param $EndTime 结束时间int
     * @param $Name 直播房间的名称
     * @return LcicRoomCreateRet
     */
    public function RoomCreate(string $TeacherId, $StartTime, $EndTime, $Name): LcicRoomCreateRet
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

    /**
     * @param string $RoomId 房间ID
     * @param $TeacherId 老师ID
     * @param $StartTime 开始时间int
     * @param $EndTime 结束时间int
     * @param $Name 房间名称
     * @return LcicRoomModifyRet
     */
    public function RoomModify(string $RoomId, $TeacherId, $StartTime, $EndTime, $Name): LcicRoomModifyRet
    {
        $ret = new LcicRoomModifyRet(
            self::raw_post($this->remote_url . LcicRouter::lcic_room_modify . $this->send_token . $this->token,
                [
                    'RoomId' => $RoomId,
                    'TeacherId' => $TeacherId,
                    'StartTime' => $StartTime,
                    'EndTime' => $EndTime,
                    'Name' => $Name,
                ]
            )
        );
        return $ret;
    }


    /**
     * @param string $OriginId 学生id
     * @param $TeacherId 老师id
     * @return LcicRoomCreateRet
     */
    public function RoomUrl(string $OriginId, $TeacherId): LcicRoomUrlRet
    {
        $ret = new LcicRoomUrlRet(
            self::raw_post($this->remote_url . LcicRouter::lcic_room_link . $this->send_token . $this->token,
                [
                    'OriginId' => $OriginId,
                    'TeacherId' => $TeacherId,
                ]
            )
        );
        return $ret;
    }
}