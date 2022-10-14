<?php

namespace Tobycroft\AossSdk;

use CURLFile;

class Aoss
{
    protected string $remote_url = "http://upload.tuuz.cc:81";
    protected string $send_url;
    protected string $send_path = "/v1/file/index";
    protected string $send_token = "?token=";
    protected string $token;
    protected string $mode;

    /**
     * @discription 构建传入token,token可以发送邮件到aoss@tuuz.cc获取
     * @param $token
     * @param $mode
     * @param $remote_url
     */
    public function __construct($token, $mode = "complete", $remote_url = "")
    {
        $this->send_url = $remote_url;
        $this->token = $token;
        $this->mode = $mode;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path . "/up_complete";
            $this->send_url .= $this->send_token . $this->token;
        }
    }


    public static function raw_post($send_url, $postData): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }


    /**
     * @send("文件地址","文件类型","文件名称")
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @param $send_url
     * @return bool|string
     */
    public static function curl_send_file($real_path, $mime_type, $file_name, $send_url): string|bool
    {
        $postData = [
            'file' => new CURLFile(realpath($real_path), $mime_type, $file_name)
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }


    /**
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossSimpleRet|AossCompleteRet
     */
    public function send($real_path, $mime_type, $file_name): AossSimpleRet|AossCompleteRet
    {
        return match ($this->mode) {
            "complete" => self::send_file_complete($this->send_url, $real_path, $mime_type, $file_name),
            default => self::send_file_url($this->send_url, $real_path, $mime_type, $file_name),
        };
    }

    /**
     * @param $md5
     * @return AossCompleteRet
     */
    public function md5($md5): AossCompleteRet
    {
        return self::check_file_complete($this->remote_url . $this->send_path . "/md5" . $this->send_token . $this->token, $md5);
    }


    /**
     * @param $send_url
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossSimpleRet
     */
    public static function send_file_url($send_url, $real_path, $mime_type, $file_name): AossSimpleRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $send_url);
        return new AossSimpleRet($response);
    }

    /**
     * @param $send_url
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossCompleteRet
     */
    public static function send_file_complete($send_url, $real_path, $mime_type, $file_name): AossCompleteRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $send_url);
        return new AossCompleteRet($response);
    }

    /**
     * @param $send_url
     * @param $md5
     * @return AossCompleteRet
     */
    public static function check_file_complete($send_url, $md5): AossCompleteRet
    {
        $postData = [
            'md5' => $md5
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);
        return new AossCompleteRet($response);
    }
}

