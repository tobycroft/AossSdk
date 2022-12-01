<?php

namespace Tobycroft\AossSdk\WechatRequestBuilder;

class WechatMode
{

    public static string $jscode2session = 'jscode2session';

    public static string $GetWxacodeUnlimit_file = "unlimited_file";
    public static string $GetWxacodeUnlimit_raw = "unlimited_raw";
    public static string $GetWxacodeUnlimit_base64 = "unlimited_base64";
    public static string $GetUserPhoneNumber = "getuserphonenumber";
    public static string $GenerateScheme = "generatescheme";

    public static string $user_list = "user_list";
    public static string $user_info = "user_info";
    public static string $openid_url = "openid_url";
    public static string $uniform_send = "uniform_send";
    public static string $template_send = "template_send";
    public static string $template_send_miniprogram = "template_send_miniprogram";
    public static string $uniform_send_more = "uniform_send_more";


}