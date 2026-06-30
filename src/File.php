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