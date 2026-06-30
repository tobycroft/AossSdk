# 微信 JS-SDK Ticket 签名

## 概述

通过 AOSS 平台获取微信 JS-SDK 签名，用于网页调用微信 JS 接口。

## 包导入

```php
use Tobycroft\AossSdk\WechatTicket;
use Tobycroft\AossSdk\WechatTicketSignatureRet;
```

## 构造方法

```php
// 继承自 Aoss，通过 WechatTicket 使用
```

## 方法

### signature

```php
public function signature(string $noncestr, int $timestamp, string $url): WechatTicketSignatureRet
```

生成 JS-SDK 签名。

| 参数 | 类型 | 说明 |
|------|------|------|
| noncestr | string | 随机字符串 |
| timestamp | int | 时间戳 |
| url | string | 当前页面 URL（不包含 # 之后部分） |

**返回值 WechatTicketSignatureRet：**

| 属性 | 类型 | 说明 |
|------|------|------|
| (signature) | mixed | 签名结果 |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

## 使用示例

```php
$ticket = new WechatTicket('your-open-token');

$noncestr = uniqid();
$timestamp = time();
$url = 'https://example.com/page';

$ret = $ticket->signature($noncestr, $timestamp, $url);

if ($ret->isSuccess()) {
    // 传递给前端 wx.config()
    $config = [
        'appId'     => 'your_appid',
        'timestamp' => $timestamp,
        'nonceStr'  => $noncestr,
        'signature' => $ret->isSuccess(),
    ];
} else {
    echo $ret->getError();
}
```