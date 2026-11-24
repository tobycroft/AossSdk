<?php

namespace Tobycroft\AossSdk\V3;

/**
 * POST /v3/file/token/md5_check 的响应
 *
 * exists=true 时 path/url/surl/file 为系统内既有文件信息，可直接当秒传成功；
 * exists=false 表示系统内没有该文件，调用方应继续走标准上传流程。
 */
class FileCheckRet
{
    public mixed $error = null;
    public bool $exists = false;
    public string $path = '';
    public string $url = '';
    public string $surl = '';
    public string $md5 = '';
    public array $file = [];

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
        $this->exists = (bool)($data['exists'] ?? false);
        $this->path = (string)($data['path'] ?? '');
        $this->url = (string)($data['url'] ?? '');
        $this->surl = (string)($data['surl'] ?? '');
        $this->md5 = (string)($data['md5'] ?? '');
        $this->file = is_array($data['file'] ?? null) ? $data['file'] : [];
    }

    public static function fromError(mixed $error): self
    {
        $ret = new self('');
        $ret->error = $error;
        return $ret;
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
