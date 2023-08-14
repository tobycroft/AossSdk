<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\Ip\Url\IpRouter;
use Tobycroft\AossSdk\Lcic\Ret\LcicUserAutoRet;

class Ip extends Aoss
{

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function IpRange(string|int $country, $province, $ip): LcicUserAutoRet
    {

        $ret = new LcicUserAutoRet(
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