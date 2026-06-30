# 文件上传临时 Token (File Token)

## 概述

v2 版本文件上传采用临时 Token 机制，避免固定 Token 暴露给前端。

**流程：**
1. 后端通过 SDK 调用 AOSSTP8 的 `/v2/file/token/create` 获取临时 Token
2. 后端将临时 Token 返回给客户端（浏览器/App）
3. 客户端使用临时 Token 直传文件至 `/v2/file/index/upfull?token=<临时Token>`
4. 上传完成后，临时 Token 立即失效

## 包导入

```php
use Tobycroft\AossSdk\File;
```

## 构造方法

```php
public function __construct(string $appid, string $token, string $remote_url = '')
```

| 参数 | 类型 | 说明 |
|------|------|------|
| appid | string | 项目 AppID |
| token | string | 项目 Open Token（后端密钥，不可暴露给前端） |
| remote_url | string | 可选，自定义远程地址，默认 `https://upload.tuuz.cc:444` |

## 方法

### setRemoteUrl

```php
public function setRemoteUrl(string $remote_url): self
```

动态修改远程地址，支持链式调用。

### getUploadToken

```php
public function getUploadToken(): FileRet
```

获取临时上传 Token。

**返回值 `FileRet`：**

| 属性 | 类型 | 说明 |
|------|------|------|
| token | string | 临时上传 Token |
| expired_at | string | 过期时间 |
| error | mixed | 错误信息，成功时为 null |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

### 基本用法

```php
use Tobycroft\AossSdk\File;

$file = new File('your-appid', 'your-open-token');
$ret = $file->getUploadToken();

if ($ret->isSuccess()) {
    echo $ret->token;       // 临时 token
    echo $ret->expired_at;  // 过期时间
} else {
    echo $ret->getError();
}
```

### 自定义地址

```php
// 方式一：构造函数传入
$file = new File('appid', 'token', 'https://custom.example.com:444');

// 方式二：链式调用
$file = (new File('appid', 'token'))
    ->setRemoteUrl('https://custom.example.com:444');

$ret = $file->getUploadToken();
```

### 完整控制器示例

```php
<?php

namespace app\controller;

use Tobycroft\AossSdk\File;

class Upload
{
    public function token()
    {
        $file = new File('your-appid', 'your-open-token');
        $ret = $file->getUploadToken();

        if (!$ret->isSuccess()) {
            return json(['code' => -1, 'msg' => $ret->getError()]);
        }

        return json([
            'code' => 0,
            'data' => [
                'token'      => $ret->token,
                'expired_at' => $ret->expired_at,
                'upload_url' => 'https://upload.tuuz.cc:444/v2/file/index/upfull',
            ],
        ]);
    }
}
```

## 前端使用临时 Token 上传

```javascript
// 1. 从后端获取临时 token
const resp = await fetch('/api/upload/token');
const { token } = await resp.json();

// 2. 使用临时 token 直传文件
const formData = new FormData();
formData.append('file', fileInput.files[0]);

const uploadResp = await fetch(
    `https://upload.tuuz.cc:444/v2/file/index/upfull?token=${token}`,
    { method: 'POST', body: formData }
);

const result = await uploadResp.json();
console.log('上传结果:', result.data);
```

## 签名机制

SDK 内部自动生成签名：

```
sign = MD5(Appid + Token + timestamp)
```

- `timestamp` 为 Unix 时间戳
- 服务端验证签名和时间戳有效性（5 分钟内）
- 临时 Token 有效期 5 分钟，一次性使用