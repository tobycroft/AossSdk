<?php

namespace Tobycroft\AossSdk\V3;

/**
 * POST /v3/file/token/temp_url 的响应（AOSSTP8 v2.8.0+）
 *
 * 签发由客户端指定有效期的 file 临时取件码；url 与 file_url 同值，
 * expires_in 为有效期秒数，expired_at 为服务端到期时间戳。
 * 远程 Bucket 不支持临时分发，服务端返回 403。
 */
class TempUrlRet
{
    public mixed $error = null;
    public int $id = 0;
    public string $md5 = '';
    public string $url = '';
    public string $file_url = '';
    public int $expires_in = 0;
    public int $expired_at = 0;

    public function __construct($response)
    {
        $json = json_decode((string)$response, true);
        if (empty($json) || !isset($json['code'])) {
            $this->error = $response;
            return;
        }
        if ((int)$json['code'] !== 0) {
            $this->error = $json['echo'] ?? 'unknown error';
            return;
        }
        $data = $json['data'] ?? [];
        $this->id = (int)($data['id'] ?? 0);
        $this->md5 = (string)($data['md5'] ?? '');
        $this->url = (string)($data['url'] ?? '');
        $this->file_url = (string)($data['file_url'] ?? '');
        $this->expires_in = (int)($data['expires_in'] ?? 0);
        $this->expired_at = (int)($data['expired_at'] ?? 0);
    }

    public function isSuccess(): bool
    {
        return empty($this->error);
    }

    public function getError(): mixed
    {
        return $this->error;
    }
}
