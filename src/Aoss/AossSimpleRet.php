<?php

namespace Tobycroft\AossSdk\Aoss;

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