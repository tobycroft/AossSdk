<?php

namespace Aoss;

class Aoss
{
    private string $remote_url = "http://upload.tuuz.cc:81";
    private string $send_url = "";
    private string $send_path = "/v1/file/index";
    protected string $send_token = "?token=";
    private string $token = "";
    private string $mode = "";

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

    /*
     * send("文件地址","文件类型","文件名称")
     */
    public function send($real_path, $mime_type, $file_name)
    {
        return match ($this->mode) {
            "complete" => self::send_file_complete($this->send_url, $real_path, $mime_type, $file_name),
            default => self::send_file_url($this->send_url, $real_path, $mime_type, $file_name),
        };
    }

    public function md5($md5): AossCompleteRet
    {
        return self::check_file_complete($this->remote_url . $this->send_path . "/md5" . $this->send_token . $this->token, $md5);
    }


    public static function send_file_url($send_url, $real_path, $mime_type, $file_name): AossSimpleRet
    {
        $postData = [
            'file' => new \CURLFile(realpath($real_path), $mime_type, $file_name)
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);
        return new AossSimpleRet($response);
    }

    public static function send_file_complete($send_url, $real_path, $mime_type, $file_name): AossCompleteRet
    {
        $postData = [
            'file' => new \CURLFile(realpath($real_path), $mime_type, $file_name)
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

class AossSimpleRet
{
    public mixed $error = null;
    public mixed $data = "";

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
            return $this;
        }
        if ($json["code"] == "0") {
            $this->data = $json["data"]["url"];
        } else {
            $this->error = $json["data"];
        }
        return $this;
    }
}

class AossCompleteRet
{
    public mixed $error = null;
    public mixed $data = [];
    public mixed $name = "";
    public mixed $path = "";
    public mixed $mime = "";
    public mixed $size = 0;
    public mixed $ext = "";
    public mixed $md5 = "";
    public mixed $sha1 = "";
    public mixed $src = "";
    public mixed $url = "";
    public mixed $surl = "";
    public int $width = 0;
    public int $height = 0;
    public int $duration = 0;
    public mixed $duration_str = "";
    public mixed $bitrate = 0;

    public function __construct($response)
    {
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
            return $this;
        }
        if ($json["code"] == "0") {
            $this->data = $json["data"];
            $this->name = $this->data["name"];
            $this->path = $this->data["path"];
            $this->mime = $this->data["mime"];
            $this->size = $this->data["size"];
            $this->ext = $this->data["ext"];
            $this->md5 = $this->data["md5"];
            $this->sha1 = $this->data["sha1"];
            $this->src = $this->data["src"];
            $this->url = $this->data["url"];
            $this->surl = $this->data["surl"];
            $this->duration = $this->data["duration"];
            $this->duration_str = $this->data["duration_str"];
            $this->bitrate = $this->data["bitrate"];
            $this->width = $this->data["width"];
            $this->height = $this->data["height"];
        } else {
            $this->error = $json["data"];
        }
        return $this;
    }
}