<?php

namespace Tobycroft\AossSdk\V3;

/**
 * V3 接口统一响应封装
 *
 * AOSSTP8 返回 {code, data, echo}，code=0 时 data 为上游原样响应。
 * 沿用本 SDK 各 Ret 类的 isSuccess()/getError() 习惯。
 */
class AiRet
{
    public string $response;
    protected int $code = -1;
    protected string $echo = '';
    protected mixed $error = null;
    protected mixed $data = null;

    public function __construct(string $response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (!is_array($json) || !isset($json['code'])) {
            $this->error = $response;
            return;
        }
        $this->code = (int)$json['code'];
        $this->echo = (string)($json['echo'] ?? '');
        if ($this->code === 0) {
            $this->data = $json['data'] ?? [];
        } else {
            $this->error = $this->echo !== '' ? $this->echo : $json;
        }
    }

    public function isSuccess(): bool
    {
        return $this->error === null && $this->code === 0;
    }

    public function getError(): mixed
    {
        return $this->error;
    }

    /** 上游原样响应（code=0 时有效），通常为关联数组 */
    public function getData(): mixed
    {
        return $this->data;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getEcho(): string
    {
        return $this->echo;
    }
}
