<?php

namespace Tobycroft\AossSdk\WechatRequestBuilder;

class WechatRouter
{

    //小程序登录
    public const jscode2session = '/v1/wechat/sns/jscode2session';

    //小程序qrcode
    public const GetWxacodeUnlimit_file = "/v1/wechat/wxa/unlimited_file";
    public const GetWxacodeUnlimit_raw = "/v1/wechat/wxa/unlimited_raw";
    public const GetWxacodeUnlimit_base64 = "/v1/wechat/wxa/unlimited_base64";
    public const GetUserPhoneNumber = "/v1/wechat/wxa/getuserphonenumber";
    public const GenerateScheme = "/v1/wechat/wxa/generatescheme";

    //officalaccount
    public const user_list = "/v1/wechat/offiaccount/user_list";
    public const user_info = "/v1/wechat/offiaccount/user_info";
    public const openid_url = "/v1/wechat/offiaccount/openid_url";
    public const uniform_send = "/v1/wechat/offiaccount/uniform_send";
    public const template_send = "/v1/wechat/offiaccount/template_send";
    public const template_send_miniprogram = "/v1/wechat/offiaccount/template_send_miniprogram";
    public const uniform_send_more = "/v1/wechat/offiaccount/uniform_send_more";

    //ticket
    public const ticket_signature = '/v1/wechat/ticket/signature';

}