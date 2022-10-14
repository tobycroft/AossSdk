<?php

namespace Tobycroft\AossSdk;

class AossCompleteRet
{
    public string $response;
    protected mixed $error = null;
    public mixed $data = [];
    public mixed $name = "";
    public mixed $path = "";
    public mixed $mime = "";
    public mixed $size = 0;
    public mixed $ext = "";
    public mixed $md5 = "";
    public mixed $sha1 = "";
    public mixed $src = "";
    public mixed $url = "";//有http-url
    public mixed $surl = "";//无http-url
    public int $width = 0;
    public int $height = 0;
    public int $duration = 0;
    public mixed $duration_str = "";
    public mixed $bitrate = 0;

    public function __construct($response)
    {
        $this->response = $response;
        $json = json_decode($response, true);
        if (empty($json) || !isset($json["code"])) {
            $this->error = $response;
        } else {
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
                $this->error = $json["echo"];
            }
        }
    }

    public function isSuccess(): bool
    {
        return empty($this->error);
    }

    /**
     * @return mixed
     */
    public function getError(): mixed
    {
        return $this->error;
    }
}

