<?php

namespace Tobycroft\AossSdk;

use GdImage;
use Tobycroft\AossSdk\ImageRequestbuilder\ImageCreateImg;
use Tobycroft\AossSdk\ImageRequestbuilder\ImageCreateText;

class Wechat extends Aoss
{
    protected string $mode;

    public function __construct($token, $wechatFunc, $wechatMode, WechatRequestBuilder\WechatFunc $func, WechatRequestBuilder\WechatMode $mode)
    {
        $this->token = $token;

        $this->send_path = $func->$wechatFunc . $mode->$wechatMode;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }

    public function create_wxa_unlimited_file(string $data, $page): GdImage|false
    {
        $response = self::raw_post($this->send_url, [
            "data" => $data,
            "page" => $page,
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
