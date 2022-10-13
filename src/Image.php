<?php

namespace Tobycroft\AossSdk;

use GdImage;
use Tobycroft\AossSdk\ImageRequestbuilder\ImageCreateImg;
use Tobycroft\AossSdk\ImageRequestbuilder\ImageCreateText;

class Image extends Aoss
{
    protected string $send_path = "/v1/image/create";

    public function __construct($token, $remote_url = "")
    {
        $this->send_url = $remote_url;
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
        $ret = new ImageRet(self::raw_post($this->send_url, [
            "data" => $data
        ]));
        return $ret->base64();
    }

}