# 微信登录（SNS）

## 概述

通过 AOSS 平台调用微信小程序 `jscode2session` 接口，获取用户 OpenID 和 Session Key。

## 包导入

```php
use Tobycroft\AossSdk\WechatSns;
use Tobycroft\AossSdk\WechatSnsRet;
```

## 构造方法

```php
// 继承自 WechatWxa，通过 WechatSns 使用
```

## 方法

### jscode2Session

```php
public function jscode2Session(string $js_code, string $grant_type): WechatSnsRet
```

通过微信小程序登录 code 换取 session 信息。

| 参数 | 类型 | 说明 |
|------|------|------|
| js_code | string | 前端 `wx.login()` 获取的 code |
| grant_type | string | 授权类型，通常为 `authorization_code` |

**返回值 WechatSnsRet：**

| 属性 | 类型 | 说明 |
|------|------|------|
| $openid | string | 用户 OpenID |
| $unionid | string | 用户在开放平台的唯一标识 |
| $session_key | string | 会话密钥 |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

## 使用示例

```php
$sns = new WechatSns('your-open-token');
$ret = $sns->jscode2Session($js_code, 'authorization_code');

if ($ret->isSuccess()) {
    $openid = $ret->openid;
    $unionid = $ret->unionid;
    $session_key = $ret->session_key;

    // 生成自定义登录态
    $token = md5($openid . $session_key . time());
    // 保存到缓存/数据库...
}
```