# 微信小程序

## 概述

通过 AOSS 平台调用微信小程序相关接口，包括获取手机号、生成 Scheme 链接、生成小程序码。

## 包导入

```php
use Tobycroft\AossSdk\WechatWxa;
use Tobycroft\AossSdk\WechatWxaPhoneRet;
use Tobycroft\AossSdk\WechatWxaSchemeRet;
use Tobycroft\AossSdk\WechatWxaUnlimitedRet;
```

## 构造方法

```php
// 继承自 Aoss，需要通过继承类使用
```

## 方法

### getuserphonenumber

```php
public function getuserphonenumber(string $code): WechatWxaPhoneRet
```

通过前端获取的 code 换取用户手机号。

| 参数 | 类型 | 说明 |
|------|------|------|
| code | string | 前端调用 `getPhoneNumber` 获取的 code |

**返回值 WechatWxaPhoneRet：**

| 属性 | 类型 | 说明 |
|------|------|------|
| $phoneNumber | string | 带区号手机号 |
| $purePhoneNumber | string | 纯手机号 |
| $countryCode | string | 国家代码 |
| $watermark | array | 水印信息 |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

### generatescheme

```php
public function generatescheme(string $path, string $query, bool $is_expire, int $expire_interval): WechatWxaSchemeRet
```

生成小程序 Scheme 链接。

| 参数 | 类型 | 说明 |
|------|------|------|
| path | string | 小程序页面路径 |
| query | string | 页面参数 |
| is_expire | bool | 是否过期 |
| expire_interval | int | 过期时间间隔（秒） |

**返回值 WechatWxaSchemeRet：**

| 属性 | 类型 | 说明 |
|------|------|------|
| $openlink | string | 生成的 Scheme 链接 |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

### create_wxa_unlimited_file

```php
public function create_wxa_unlimited_file(string $data, string $page): string|bool
```

生成无限量小程序码，返回文件路径。

| 参数 | 类型 | 说明 |
|------|------|------|
| data | string | 场景值/参数 |
| page | string | 小程序页面路径 |

### create_wxa_unlimited_base64

```php
public function create_wxa_unlimited_base64(string $data, string $page): string|bool
```

生成无限量小程序码，返回 base64 字符串。

### create_wxa_unlimited_raw

```php
public function create_wxa_unlimited_raw(string $data, string $page): GdImage|bool
```

生成无限量小程序码，返回 GD 图像资源。

## 使用示例

### 获取手机号

```php
$wxa = new WechatWxa('your-open-token');
$ret = $wxa->getuserphonenumber($code);

if ($ret->isSuccess()) {
    echo $ret->phoneNumber;       // 188****8888
    echo $ret->purePhoneNumber;   // 18888888888
    echo $ret->countryCode;      // 86
}
```

### 生成 Scheme 链接

```php
$wxa = new WechatWxa('your-open-token');
$ret = $wxa->generatescheme('pages/index/index', 'scene=abc', true, 86400);

if ($ret->isSuccess()) {
    echo $ret->openlink; // weixin://dl/business/...
}
```

### 生成小程序码

```php
$wxa = new WechatWxa('your-open-token');

// 返回文件路径
$file = $wxa->create_wxa_unlimited_file('scene_id=123', 'pages/index/index');

// 返回 base64
$b64 = $wxa->create_wxa_unlimited_base64('scene_id=123', 'pages/index/index');
echo '<img src="' . $b64 . '">';

// 返回 GD 资源
$gd = $wxa->create_wxa_unlimited_raw('scene_id=123', 'pages/index/index');
if ($gd) {
    imagepng($gd, './wxa.png');
}
```