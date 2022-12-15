<?php

namespace Tobycroft\AossSdk\WechatRequestBuilder;

class WechatRouter
{

    public static string $jscode2session = '/v1/wechat/sns/jscode2session';

    public static string $GetWxacodeUnlimit_file = "/v1/wechat/wxa/unlimited_file";
    public static string $GetWxacodeUnlimit_raw = "/v1/wechat/wxa/unlimited_raw";
    public static string $GetWxacodeUnlimit_base64 = "/v1/wechat/wxa/unlimited_base64";
    public static string $GetUserPhoneNumber = "/v1/wechat/wxa/getuserphonenumber";
    public static string $GenerateScheme = "/v1/wechat/wxa/generatescheme";

    public static string $user_list = "/v1/wechat/offiaccount/user_list";
    public static string $user_info = "/v1/wechat/offiaccount/user_info";
    public static string $openid_url = "/v1/wechat/offiaccount/openid_url";
    public static string $uniform_send = "/v1/wechat/offiaccount/uniform_send";
    public static string $template_send = "/v1/wechat/offiaccount/template_send";
    public static string $template_send_miniprogram = "/v1/wechat/offiaccount/template_send_miniprogram";
    public static string $uniform_send_more = "/v1/wechat/offiaccount/uniform_send_more";
    public static string $ticket_signature = '/v1/wechat/ticket/signature';

}