<?php

namespace Tobycroft\AossSdk;

class File
{
    private string $remote_url = 'https://upload.tuuz.cc:444';
    private string $token;

    public function __construct(string $token, string $remote_url = '')
    {
        $this->token = $token;
        if (!empty($remote_url)) {
            $this->remote_url = $remote_url;
        }
    }

    public function setRemoteUrl(string $remote_url): self
    {
        $this->remote_url = $remote_url;
        return $this;
    }

    public function getUploadToken(): FileRet
    {
        $timestamp = (string)time();
        $sign = md5($this->token . $timestamp);

        $postData = [
            'token' => $this->token,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ];

        $response = Aoss::raw_post($this->remote_url . '/v2/file/token/create', $postData);
        return new FileRet($response);
    }

    public function getUploadUrl(): FileUrlRet
    {
        $timestamp = (string)time();
        $sign = md5($this->token . $timestamp);

        $postData = [
            'token' => $this->token,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ];

        $response = Aoss::raw_post($this->remote_url . '/v2/file/token/upload_url', $postData);
        return new FileUrlRet($response);
    }

    public function getUploadHashUrl(): FileUrlRet
    {
        $timestamp = (string)time();
        $sign = md5($this->token . $timestamp);

        $postData = [
            'token' => $this->token,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ];

        $response = Aoss::raw_post($this->remote_url . '/v2/file/token/upload_url_hash', $postData);
        return new FileUrlRet($response);
    }

    public function getUploadedFileUrlByHash(string $hash): FileHashRet
    {
        $timestamp = (string)time();
        $sign = md5($this->token . $timestamp);

        $postData = [
            'token' => $this->token,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'hash' => $hash,
        ];

        $response = Aoss::raw_post($this->remote_url . '/v2/file/token/hash_query', $postData);
        return new FileHashRet($response);
    }
}

class FileRet
{
    public mixed $error = null;
    public string $token = '';
    public string $expired_at = '';

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json['code'])) {
            $this->error = $response;
            return;
        }
        if ($json['code'] == 0) {
            $this->token = $json['data']['token'];
            $this->expired_at = $json['data']['expired_at'];
        } else {
            $this->error = $json['echo'] ?? 'unknown error';
        }
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

class FileUrlRet
{
    public mixed $error = null;
    public string $upload_url = '';

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json['code'])) {
            $this->error = $response;
            return;
        }
        if ($json['code'] == 0) {
            $this->upload_url = $json['data']['upload_url'];
        } else {
            $this->error = $json['echo'] ?? 'unknown error';
        }
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

class FileHashRet
{
    public mixed $error = null;
    public string $src = '';
    public string $url = '';
    public string $surl = '';
    public string $name = '';
    public string $mime = '';
    public string $path = '';
    public string $ext = '';
    public int $size = 0;
    public string $md5 = '';
    public string $sha1 = '';
    public int $width = 0;
    public int $height = 0;
    public float $duration = 0;
    public string $duration_str = '';
    public float $bitrate = 0;

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json['code'])) {
            $this->error = $response;
            return;
        }
        if ($json['code'] == 0) {
            $data = $json['data'];
            $this->src = $data['src'] ?? '';
            $this->url = $data['url'] ?? '';
            $this->surl = $data['surl'] ?? '';
            $this->name = $data['name'] ?? '';
            $this->mime = $data['mime'] ?? '';
            $this->path = $data['path'] ?? '';
            $this->ext = $data['ext'] ?? '';
            $this->size = $data['size'] ?? 0;
            $this->md5 = $data['md5'] ?? '';
            $this->sha1 = $data['sha1'] ?? '';
            $this->width = $data['width'] ?? 0;
            $this->height = $data['height'] ?? 0;
            $this->duration = $data['duration'] ?? 0;
            $this->duration_str = $data['duration_str'] ?? '';
            $this->bitrate = $data['bitrate'] ?? 0;
        } else {
            $this->error = $json['echo'] ?? 'unknown error';
        }
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