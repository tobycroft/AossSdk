<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\Ip\Ret\IpRet;
use Tobycroft\AossSdk\Ip\Url\IpRouter;

class Ip extends Aoss
{

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function IpRange(string|int $country, $province, $ip): IpRet
    {

        $ret = new IpRet(
            self::raw_post($this->remote_url . IpRouter::check . $this->send_token . $this->token,
                [
                    'country' => $country,
                    'province' => $province,
                    'ip' => $ip,
                ]
            )
        );
        return $ret;
    }
}