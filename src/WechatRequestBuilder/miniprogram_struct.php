<?php

class miniprogram_struct
{
    public $appid;
    public $pagepath;

    public function __construct(string $appid, string $pagepath)
    {
        $this->appid = $appid;
        $this->pagepath = $pagepath;
        return $this;
    }
}
