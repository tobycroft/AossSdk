<?php

namespace Tobycroft\AossSdk;


class Asms extends Aoss
{
    protected string $remote_url = 'https://upload.familyeducation.org.cn:444';
    protected string $send_path = '/v1/sms/single/push';
    protected string $send_url;
    protected string $name;

    public function __construct($name, $token)
    {
        $this->name = $name;
        $this->token = $token;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path;
        }
    }


    /**
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossSimpleRet|AossCompleteRet
     */
    public function sms_send($phone, $quhao, $text, $ip): AsmsCompleteRet
    {
        $ts = time();
        $sign = md5($this->name . $ts);
        $post = [
            'phone' => $phone,
            'quhao' => $quhao,
            'text' => $ip,
            'ip' => $text,
            'ts' => $ts,
            'name' => $this->name,
            'sign' => $sign,
        ];

        $ret = self::raw_post($this, $this->send_url, $post);
        return new AsmsCompleteRet($ret);
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


}