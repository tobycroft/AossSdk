<?php

namespace Tobycroft\AossSdk\V3;

/**
 * V3 文件删除/恢复接口返回
 */
class FileActionRet
{
    public mixed $error = null;
    public string $md5 = '';
    public int $deleted = 0;
    /** @var string[] */
    public array $warnings = [];

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json['code'])) {
            $this->error = $response;
            return;
        }
        if ((int)$json['code'] !== 0) {
            $this->error = $json['echo'] ?? 'unknown error';
            return;
        }
        $data = $json['data'] ?? [];
        $this->md5 = (string)($data['md5'] ?? '');
        $this->deleted = (int)($data['deleted'] ?? 0);
        $this->warnings = (array)($data['warnings'] ?? []);
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
