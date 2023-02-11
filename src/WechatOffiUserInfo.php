<?php

namespace Tobycroft\AossSdk;

class WechatOffiUserInfo
{
    public mixed $response;
    public int $subscribe;
    public string $openid;
    public string $nickname;
    public int $sex;
    public string $headimgurl;
    public int $subscribe_time;
    protected string $error;
    protected mixed $data;

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
            $this->subscribe = $this->data["subscribe"];
            $this->openid = $this->data["openid"];
            $this->nickname = $this->data["nickname"];
            $this->sex = $this->data["sex"];
            $this->headimgurl = $this->data["headimgurl"];
            $this->subscribe_time = $this->data["subscribe_time"];
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
}