<?php

namespace Tobycroft\AossSdk;

use Tobycroft\AossSdk\WechatRequestBuilder\WechatFunc;
use Tobycroft\AossSdk\WechatRequestBuilder\WechatMode;

class WechatOffi extends Aoss
{

    public function uniform_send(string $openid, $template_id, $url, array $data): WechatOffiPush
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$uniform_send);
        $postData = [
            'openid' => $openid,
            'url' => $url,
            'template_id' => $template_id,
            'data' => json_encode($data, 320),
        ];
        return new WechatOffiPush(self::raw_post($this->send_url, $postData));
    }

    public function template_send(string $openid, $template_id, $url, array $data, string $client_msg_id = null): WechatOffiPush
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$template_send);
        $postData = [
            'openid' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'data' => json_encode($data, 320),
            'client_msg_id' => $client_msg_id,
        ];
        return new WechatOffiPush(self::raw_post($this->send_url, $postData));
    }

    public function template_send_miniprogram(string $openid, $template_id, $url, \miniprogram_struct $miniprogram_struct, array $data, string $client_msg_id = null): WechatOffiPush
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$template_send_miniprogram);
        $postData = [
            'openid' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'miniprogram' => json_encode($miniprogram_struct, 320),
            'data' => json_encode($data, 320),
            'client_msg_id' => $client_msg_id,
        ];
        return new WechatOffiPush(self::raw_post($this->send_url, $postData));
    }

    public function uniform_send_more(array $openids, string $template_id, $url, array $data): WechatOffiPush
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$uniform_send_more);
        $postData = [
            'openids' => $openids,
            'url' => $url,
            'template_id' => $template_id,
            'data' => json_encode($data, 320),
        ];
        return new WechatOffiPush(self::raw_post($this->send_url, $postData));
    }

    public function buildUrl($wechatFunc, $wechatMode)
    {
        $this->send_path = $wechatFunc . $wechatMode;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }

    public function get_user_list(): WechatOffiUserList
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$user_list);
        $postData = [
        ];
        return new WechatOffiUserList(self::raw_post($this->send_url, $postData));
    }

    public function get_user_info(string $openid): WechatOffiUserInfo
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$user_info);
        $postData = [
            'openid' => $openid,
        ];
        return new WechatOffiUserInfo(self::raw_post($this->send_url, $postData));
    }

    public function get_openUrl(string $redirect_uri, $response_type, $scope, $state): WechatOffiOpenUrl
    {
        $this->buildUrl(WechatFunc::Offi, WechatMode::$openid_url);
        $postData = [
            'redirect_uri' => $redirect_uri,
            'response_type' => $response_type,
            'scope' => $scope,
            'state' => $state,
            'png' => false,
        ];
        return new WechatOffiOpenUrl(self::raw_post($this->send_url, $postData));
    }


}
