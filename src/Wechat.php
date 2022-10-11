<?php

namespace Tobycroft\AossSdk;

use GdImage;
use SplEnum;

class Wechat extends Aoss
{
    protected string $send_path = "/v1/wechat/";

    protected string $mode;

    public function __construct($token, Mode $mode)
    {
        $this->token = $token;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path . "/canvas";
            $this->send_url .= $this->send_token . $this->token;
        }
    }

    public function create_canvas($width, $height, $background = "ffffff", ImageCreateImg|ImageCreateText ...$data): GdImage|false
    {
        $response = self::raw_post($this->send_url, [
            "width" => $width,
            "height" => $height,
            "background" => $background,
            "data" => json_encode($data, 320)
        ]);
        return imagecreatefromstring($response);
    }

    public static function raw_post($send_url, $postData)
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

    public function create_imgurl($width, $height, $background = "ffffff", ImageCreateImg|ImageCreateText ...$data): ImageRet
    {
        $response = self::raw_post($this->send_url, [
            "width" => $width,
            "height" => $height,
            "background" => $background,
            "data" => json_encode($data, 320)
        ]);
        return new ImageRet($response);
    }

    public function create_barcode($data): GdImage|false
    {
        $response = self::raw_post($this->send_url, [
            "data" => $data
        ]);
        return imagecreatefromstring($response);
    }

    public function create_qr($data): GdImage|false
    {
        $response = self::raw_post($this->send_url, [
            "data" => $data
        ]);
        return imagecreatefromstring($response);
    }

    public function create_qr_with_logo($data, $img_url): GdImage|false
    {
        $response = self::raw_post($this->send_url, [
            "data" => $data,
            "url" => $img_url
        ]);
        return imagecreatefromstring($response);
    }

    public function create_qr_b64($data): string
    {
        return self::raw_post($this->send_url, [
            "data" => $data
        ]);
    }

}

class Mode extends SplEnum
{
    const GetWxacodeUnlimit_file = "wxa/unlimited_file";
    const GetWxacodeUnlimit_raw = "wxa/unlimited_raw";
    const GetWxacodeUnlimit_base64 = "wxa/unlimited_base64";
}

class ModeData extends SplEnum
{
    const File = "file";
}