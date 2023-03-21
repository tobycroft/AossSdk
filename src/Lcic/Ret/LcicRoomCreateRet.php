<?php

namespace Tobycroft\AossSdk\Lcic\Ret;

class LcicRoomCreateRet
{
    public mixed $response;
    protected string $error;
    protected mixed $data;

    protected string $RoomId;

    public function __construct(string $response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
            return $this;
        }
        if ($json["code"] == "0") {
            $this->data = $json["data"];
            $this->RoomId = $this->data["RoomId"];
        } else {
            $this->error = $json["echo"];
        }
    }

    /**
     * @return mixed
     */
    public function getError(): string
    {
        return $this->error;
    }

    public function isSuccess(): bool
    {
        return empty($this->error);
    }

    public function GetRoomId(): string
    {
        return $this->RoomId;
    }
}