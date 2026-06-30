# 短信服务（ASMS）

## 概述

通过 AOSS 平台发送短信。

## 包导入

```php
use Tobycroft\AossSdk\Asms;
use Tobycroft\AossSdk\AsmsCompleteRet;
```

## 构造方法

```php
public function __construct(string $name, string $token)
```

| 参数 | 类型 | 说明 |
|------|------|------|
| name | string | 短信签名名称 |
| token | string | 项目 Open Token |

## 方法

### sms_send

```php
public function sms_send(string $phone, string $quhao, string $text, string $ip): AsmsCompleteRet
```

发送短信。

| 参数 | 类型 | 说明 |
|------|------|------|
| phone | string | 手机号 |
| quhao | string | 区号 |
| text | string | 短信内容 |
| ip | string | 用户 IP 地址 |

## 返回值

### AsmsCompleteRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

```php
$asms = new Asms('your-sign-name', 'your-open-token');
$ret = $asms->sms_send('13800138000', '86', '您的验证码是 123456', '127.0.0.1');

if ($ret->isSuccess()) {
    echo '发送成功';
} else {
    echo $ret->getError();
}
```