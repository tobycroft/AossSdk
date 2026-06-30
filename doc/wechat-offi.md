# 微信公众号

## 概述

通过 AOSS 平台调用微信公众号接口，包括模板消息推送、用户管理、网页授权。

## 包导入

```php
use Tobycroft\AossSdk\WechatOffi;
use Tobycroft\AossSdk\WechatOffiPush;
use Tobycroft\AossSdk\WechatOffiUserInfo;
use Tobycroft\AossSdk\WechatOffiUserList;
use Tobycroft\AossSdk\WechatOffiOpenUrl;
```

## 构造方法

```php
// 继承自 Aoss，需要通过 WechatOffi 使用
```

## 方法

### template_send

```php
public function template_send(string $openid, string $template_id, string $url, array $data, string $client_msg_id = null): WechatOffiPush
```

发送模板消息。

| 参数 | 类型 | 说明 |
|------|------|------|
| openid | string | 用户 OpenID |
| template_id | string | 模板 ID |
| url | string | 点击跳转 URL |
| data | array | 模板数据 |
| client_msg_id | string | 可选，防重入 ID |

### template_send_miniprogram

```php
public function template_send_miniprogram(string $openid, string $template_id, string $url, \miniprogram_struct $miniprogram_struct, array $data, string $client_msg_id = null): WechatOffiPush
```

发送带小程序跳转的模板消息。

### uniform_send

```php
public function uniform_send(string $openid, string $template_id, string $url, array $data): WechatOffiPush
```

统一消息发送（公众号和小程序互通）。

### uniform_send_more

```php
public function uniform_send_more(array $openids, string $template_id, string $url, array $data): WechatOffiPush
```

批量发送统一消息。

### get_user_list

```php
public function get_user_list(): WechatOffiUserList
```

获取关注用户列表。

**返回值 WechatOffiUserList：**

| 属性 | 类型 | 说明 |
|------|------|------|
| $openids | array | 用户 OpenID 列表 |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

### get_user_info

```php
public function get_user_info(string $openid): WechatOffiUserInfo
```

获取用户详细信息。

**返回值 WechatOffiUserInfo：**

| 属性 | 类型 | 说明 |
|------|------|------|
| $subscribe | int | 是否关注 |
| $openid | string | OpenID |
| $nickname | string | 昵称 |
| $sex | int | 性别 |
| $headimgurl | string | 头像 URL |
| $subscribe_time | int | 关注时间 |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

### get_openUrl

```php
public function get_openUrl(string $redirect_uri, string $response_type, string $scope, string $state): WechatOffiOpenUrl
```

获取网页授权 URL。

**返回值 WechatOffiOpenUrl：**

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| getUrl() | string | 授权跳转 URL |
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

## 返回值（通用）

### WechatOffiPush

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| isSuccess() | bool | 是否成功 |
| getError() | string | 获取错误信息 |

## 使用示例

### 发送模板消息

```php
$offi = new WechatOffi('your-open-token');

$ret = $offi->template_send(
    'openid_xxx',
    'template_id_xxx',
    'https://example.com/detail/123',
    [
        'first'    => ['value' => '您好，您有一条新消息'],
        'keyword1' => ['value' => '订单通知'],
        'keyword2' => ['value' => '2024-01-01 12:00:00'],
        'remark'   => ['value' => '感谢您的使用'],
    ]
);

if ($ret->isSuccess()) {
    echo '发送成功';
}
```

### 发送带小程序跳转的模板消息

```php
$mp = new \miniprogram_struct('wx_appid', 'pages/index/index');

$ret = $offi->template_send_miniprogram(
    'openid_xxx',
    'template_id_xxx',
    'https://example.com',
    $mp,
    ['first' => ['value' => '点击查看小程序']]
);
```

### 获取用户列表

```php
$ret = $offi->get_user_list();
if ($ret->isSuccess()) {
    foreach ($ret->openids as $openid) {
        echo $openid;
    }
}
```

### 获取用户信息

```php
$ret = $offi->get_user_info('openid_xxx');
if ($ret->isSuccess()) {
    echo $ret->nickname;
    echo $ret->headimgurl;
}
```

### 网页授权

```php
$ret = $offi->get_openUrl(
    'https://example.com/callback',
    'code',
    'snsapi_userinfo',
    'STATE'
);
if ($ret->isSuccess()) {
    // 重定向用户到授权页
    header('Location: ' . $ret->getUrl());
}
```