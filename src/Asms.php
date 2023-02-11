<?php

namespace Tobycroft\AossSdk;

class Asms extends Aoss
{
    protected string $remote_url = "https://upload.tuuz.cc:444";
    protected string $send_path = '/v1/image/create';
    protected string $send_url;
    protected string $name;

    public function __construct($name, $remote_url = '')
    {
        $this->send_url = $remote_url;
        $this->name = $name;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
        }
    }


    /**
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossSimpleRet|AossCompleteRet
     */
    public function sms_send($real_path, $mime_type, $file_name): AossSimpleRet|AossCompleteRet
    {
        return match ($this->mode) {
            "complete" => self::send_file_complete($this->send_url, $real_path, $mime_type, $file_name),
            default => self::send_file_url($this->send_url, $real_path, $mime_type, $file_name),
        };
    }


}