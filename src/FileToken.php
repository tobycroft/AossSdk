<?php

namespace Tobycroft\AossSdk;

class FileToken
{
    private string $remote_url = 'https://upload.tuuz.cc:444';
    private string $appid;
    private string $token;

    public function __construct(string $appid, string $token, string $remote_url = '')
    {
        $this->appid = $appid;
        $this->token = $token;
        if (!empty($remote_url)) {
            $this->remote_url = $remote_url;
        }
    }

    public function getUploadToken(): FileTokenRet
    {
        $timestamp = (string)time();
        $sign = md5($this->appid . $this->token . $timestamp);

        $postData = [
            'appid' => $this->appid,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ];

        $response = Aoss::raw_post($this->remote_url . '/v2/file/token/create', $postData);
        return new FileTokenRet($response);
    }
}

class FileTokenRet
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