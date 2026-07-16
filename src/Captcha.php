<?php

namespace Tobycroft\AossSdk;

use GdImage;

class Captcha extends Aoss
{
    protected string $send_path = "/v1/captcha";

    public function __construct($token, $remote_url = "")
    {
        $this->send_url = $remote_url;
        $this->token = $token;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
        }
    }

    /**
     * 验证验证码是否正确
     */
    public function check($ident, $code): CaptchaRet
    {
        $ret = self::raw_post(
            $this->send_url . $this->send_path . "/auth/check" . $this->send_token . $this->token,
            [
                'ident' => $ident,
                'code' => $code,
            ]
        );
        return new CaptchaRet($ret);
    }

    /**
     * 验证验证码，并限制有效时间（秒）
     */
    public function check_in_time($ident, $code, $validtime_in_second): CaptchaRet
    {
        $ret = self::raw_post(
            $this->send_url . $this->send_path . "/auth/check_in_time" . $this->send_token . $this->token,
            [
                'ident' => $ident,
                'code' => $code,
                'second' => $validtime_in_second,
            ]
        );
        return new CaptchaRet($ret);
    }

    /**
     * 数学算式验证码
     */
    public function math($ident): GdImage|false
    {
        return $this->getStaticImage("/text/math", $ident);
    }

    /**
     * 数字验证码
     */
    public function number($ident): GdImage|false
    {
        return $this->getStaticImage("/text/number", $ident);
    }

    /**
     * 中文验证码
     */
    public function chinese($ident): GdImage|false
    {
        return $this->getStaticImage("/text/chinese", $ident);
    }

    /**
     * 文本（字母+数字）验证码
     */
    public function text($ident): GdImage|false
    {
        return $this->getStaticImage("/text/text", $ident);
    }

    /**
     * 动态 GIF 文本验证码（字母+数字，1秒一帧）
     * 返回 GIF 二进制字符串
     */
    public function gif_text($ident): string|false
    {
        return $this->getGifImage("/gif/text", $ident);
    }

    /**
     * 动态 GIF 文本验证码（字母+数字，0.5秒一帧）
     * 返回 GIF 二进制字符串
     */
    public function gif_fast($ident): string|false
    {
        return $this->getGifImage("/gif/fast", $ident);
    }

    /**
     * 动态 GIF 数字验证码（1秒一帧）
     * 返回 GIF 二进制字符串
     */
    public function gif_number($ident): string|false
    {
        return $this->getGifImage("/gif/number", $ident);
    }

    /**
     * 动态 GIF 数字验证码（0.5秒一帧）
     * 返回 GIF 二进制字符串
     */
    public function gif_number_fast($ident): string|false
    {
        return $this->getGifImage("/gif/number_fast", $ident);
    }

    /**
     * 动态 GIF 纯字母验证码（1秒一帧）
     * 返回 GIF 二进制字符串
     */
    public function gif_letters($ident): string|false
    {
        return $this->getGifImage("/gif/letters", $ident);
    }

    /**
     * 动态 GIF 纯字母验证码（0.5秒一帧）
     * 返回 GIF 二进制字符串
     */
    public function gif_letters_fast($ident): string|false
    {
        return $this->getGifImage("/gif/letters_fast", $ident);
    }

    private function getStaticImage($path, $ident): GdImage|false
    {
        $response = self::raw_post(
            $this->send_url . $this->send_path . $path . $this->send_token . $this->token,
            ['ident' => $ident]
        );
        $decoded = @imagecreatefromstring($response);
        if ($decoded === false) {
            $json = json_decode($response, true);
            if (!empty($json) && isset($json["code"]) && $json["code"] == "0") {
                return false;
            }
            return false;
        }
        return $decoded;
    }

    private function getGifImage($path, $ident): string|false
    {
        $response = self::raw_post(
            $this->send_url . $this->send_path . $path . $this->send_token . $this->token,
            ['ident' => $ident]
        );
        if (str_starts_with($response, 'GIF')) {
            return $response;
        }
        $json = json_decode($response, true);
        if (!empty($json) && isset($json["code"]) && $json["code"] == "0") {
            return $response;
        }
        return false;
    }
}