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
public function __construct(string $token, string $remote_url = '')
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | OSS Token（后端密钥，不可暴露给前端） |
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

### getUploadUrl

```php
public function getUploadUrl(): FileUrlRet
```

从 AOSSTP8 获取完整的上传地址（返回文件完整信息）。

**返回值 `FileUrlRet`：**

| 属性 | 类型 | 说明 |
|------|------|------|
| upload_url | string | 完整上传地址 |
| error | mixed | 错误信息，成功时为 null |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

### getUploadUrlHash

```php
public function getUploadUrlHash(): FileUrlRet
```

从 AOSSTP8 获取 Hash 模式的上传地址（上传后仅返回文件 MD5 哈希）。

**返回值 `FileUrlRet`：**

| 属性 | 类型 | 说明 |
|------|------|------|
| upload_url | string | Hash 上传地址 |
| error | mixed | 错误信息，成功时为 null |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

### queryByHash

```php
public function queryByHash(string $hash): FileHashRet
```

通过文件 MD5 哈希查询完整文件信息。

**返回值 `FileHashRet`：**

| 属性 | 类型 | 说明 |
|------|------|------|
| src | string | 文件相对路径 |
| url | string | 文件完整 URL |
| surl | string | 文件短路径 |
| name | string | 原始文件名 |
| mime | string | MIME 类型 |
| ext | string | 扩展名 |
| size | int | 文件大小 |
| md5 | string | MD5 哈希 |
| sha1 | string | SHA1 哈希 |
| width | int | 图片/视频宽度 |
| height | int | 图片/视频高度 |
| duration | float | 时长 |
| error | mixed | 错误信息 |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

### 基本用法

```php
use Tobycroft\AossSdk\File;

$file = new File('your-oss-token');
$ret = $file->getUploadToken();

if ($ret->isSuccess()) {
    echo $ret->token;       // 临时 token
    echo $ret->expired_at;  // 过期时间
} else {
    echo $ret->getError();
}
```

### 获取上传地址

```php
$file = new File('your-oss-token');
$ret = $file->getUploadUrl();

if ($ret->isSuccess()) {
    echo $ret->upload_url; // https://upload.tuuz.cc:433/v2/file/index/upfull
}
```

### Hash 模式上传（仅返回 MD5）

```php
$file = new File('your-oss-token');

// 1. 获取 Hash 上传地址
$urlRet = $file->getUploadUrlHash();
echo $urlRet->upload_url; // https://upload.tuuz.cc:433/v2/file/index/uphash

// 2. 客户端上传后获得 MD5 哈希
// 3. 通过哈希查询完整文件信息
$hashRet = $file->queryByHash('abc123def456...');
if ($hashRet->isSuccess()) {
    echo $hashRet->url;  // 完整文件 URL
    echo $hashRet->size; // 文件大小
}
```

### 自定义地址

```php
// 方式一：构造函数传入
$file = new File('your-oss-token', 'https://custom.example.com:444');

// 方式二：链式调用
$file = (new File('your-oss-token'))
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
        $file = new File('your-oss-token');
        $ret = $file->getUploadToken();

        if (!$ret->isSuccess()) {
            return json(['code' => -1, 'msg' => $ret->getError()]);
        }

        $urlRet = $file->getUploadUrl();

        return json([
            'code' => 0,
            'data' => [
                'token'      => $ret->token,
                'expired_at' => $ret->expired_at,
                'upload_url' => $urlRet->isSuccess() ? $urlRet->upload_url : '',
            ],
        ]);
    }
}
```

## 前端使用临时 Token 上传

### 完整模式（返回文件地址）

```javascript
const resp = await fetch('/api/upload/token');
const { token, upload_url } = await resp.json();

const formData = new FormData();
formData.append('file', fileInput.files[0]);

const uploadResp = await fetch(
    `${upload_url}?token=${token}`,
    { method: 'POST', body: formData }
);

const result = await uploadResp.json();
console.log('上传结果:', result.data);
```

### Hash 模式（仅返回 MD5）

```javascript
const resp = await fetch('/api/upload/token');
const { token, upload_url_hash } = await resp.json();

const formData = new FormData();
formData.append('file', fileInput.files[0]);

const uploadResp = await fetch(
    `${upload_url_hash}?token=${token}`,
    { method: 'POST', body: formData }
);

const result = await uploadResp.json();
const hash = result.data; // 仅返回 MD5 哈希

// 后端通过 hash 查询完整文件信息
fetch('/api/upload/query-hash', {
    method: 'POST',
    body: JSON.stringify({ hash }),
});
```

## 签名机制

SDK 内部自动生成签名：

```
sign = MD5(Token + timestamp)
```

- `timestamp` 为 Unix 时间戳
- 服务端验证签名和时间戳有效性（5 分钟内）
- 临时 Token 有效期 5 分钟，一次性使用