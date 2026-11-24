<?php

namespace Tobycroft\AossSdk\V3;

/**
 * 上传准备结果：先查 MD5，命中即秒传；未命中则带上标准上传所需的 token/upload_url
 *
 * 调用方只需判断 $ret->hit：
 *   - true  ：直接用 $ret->path / $ret->url，不要提交文件
 *   - false ：用 $ret->upload_url 以 multipart 提交 file 字段（老流程不变）
 */
class UploadPrepareRet
{
    public mixed $error = null;
    public bool $hit = false;
    public string $md5 = '';
    public string $path = '';
    public string $url = '';
    public string $surl = '';
    public string $token = '';
    public string $expired_at = '';
    public string $upload_url = '';

    public static function fromCheck(FileCheckRet $check, string $md5): self
    {
        $ret = new self();
        $ret->md5 = $md5 !== '' ? $md5 : $check->md5;
        $ret->hit = $check->exists;
        $ret->path = $check->path;
        $ret->url = $check->url;
        $ret->surl = $check->surl;
        return $ret;
    }

    public static function fromUrl(\Tobycroft\AossSdk\FileUrlRet $url, string $md5): self
    {
        $ret = new self();
        $ret->md5 = $md5;
        $ret->token = $url->token;
        $ret->expired_at = $url->expired_at;
        $ret->upload_url = $url->upload_url;
        return $ret;
    }

    public static function fromError(mixed $error, string $md5 = ''): self
    {
        $ret = new self();
        $ret->error = $error;
        $ret->md5 = $md5;
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

    /** 未命中且已拿到上传地址时，调用方需要真正提交文件 */
    public function needUpload(): bool
    {
        return !$this->hit && $this->upload_url !== '';
    }
}
