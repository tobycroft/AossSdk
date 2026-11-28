# AOSS 基础文件上传

## 概述

AOSS SDK 提供文件上传功能，支持完整模式（返回全部文件信息）和简化模式（只返回 URL）。

## 包导入

```php
use Tobycroft\AossSdk\Aoss;
use Tobycroft\AossSdk\AossCompleteRet;
use Tobycroft\AossSdk\AossSimpleRet;
```

## 构造方法

```php
public function __construct(string $token, string $mode = "complete", string $remote_url = "")
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | 项目 Open Token |
| mode | string | `complete` 返回完整文件信息，`simple` 只返回 URL，默认 `complete` |
| remote_url | string | 可选，自定义远程地址 |

## 方法

### send

```php
public function send(string $real_path, string $mime_type, string $file_name): AossSimpleRet|AossCompleteRet
```

上传文件，根据构造时的 `mode` 返回对应类型。

| 参数 | 类型 | 说明 |
|------|------|------|
| real_path | string | 文件本地物理路径 |
| mime_type | string | 文件 MIME 类型 |
| file_name | string | 文件名 |

### md5

```php
public function md5(string $md5): AossCompleteRet
```

通过 MD5 查询已存在的文件信息。

| 参数 | 类型 | 说明 |
|------|------|------|
| md5 | string | 文件 MD5 |

### send_file_url (static)

```php
public static function send_file_url(string $send_url, string $real_path, string $mime_type, string $file_name): AossSimpleRet
```

直接上传到指定 URL，返回简化结果。

### send_file_complete (static)

```php
public static function send_file_complete(string $send_url, string $real_path, string $mime_type, string $file_name): AossCompleteRet
```

直接上传到指定 URL，返回完整结果。

### check_file_complete (static)

```php
public static function check_file_complete(string $send_url, string $md5): AossCompleteRet
```

通过 MD5 查询文件信息。

## 返回值

### AossCompleteRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| $data | array | 完整数据 |
| $name | string | 文件名 |
| $path | string | 文件存储路径 |
| $mime | string | MIME 类型 |
| $size | int | 文件大小 |
| $ext | string | 文件扩展名 |
| $md5 | string | MD5 |
| $sha1 | string | SHA1 |
| $src | string | 源路径 |
| $url | string | 完整 URL |
| $surl | string | 相对路径 |
| $width | int | 图片宽度 |
| $height | int | 图片高度 |
| $duration | int | 音视频时长（秒） |
| $duration_str | string | 时长格式化 |
| $bitrate | int | 比特率 |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

### AossSimpleRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| url() | string | 获取上传后的 URL |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

### 完整模式上传

```php
$aoss = new Aoss('your-open-token', 'complete');
$ret = $aoss->send('/path/to/local/file.jpg', 'image/jpeg', 'file.jpg');

if ($ret->isSuccess()) {
    echo $ret->url;          // 完整访问地址
    echo $ret->md5;          // 文件 MD5
    echo $ret->width;        // 图片宽度
    echo $ret->height;       // 图片高度
    echo $ret->duration;     // 音视频时长
} else {
    echo $ret->getError();
}
```

### 简化模式上传

```php
$aoss = new Aoss('your-open-token', 'simple');
$ret = $aoss->send('/path/to/file.pdf', 'application/pdf', 'file.pdf');

if ($ret->isSuccess()) {
    echo $ret->url(); // 返回文件 URL
}
```

### MD5 查询

```php
$aoss = new Aoss('your-open-token');
$ret = $aoss->md5('d41d8cd98f00b204e9800998ecf8427e');

if ($ret->isSuccess()) {
    echo $ret->url;
}
```

### MD5 拉取模式

先让前端直传，后端通过 MD5 获取文件信息：

```php
// 前端获取 token，直传文件后得到 MD5
// 后端查询完成信息返回给业务

$aoss = new Aoss('your-open-token');
$ret = $aoss->md5($md5_from_frontend);

if ($ret->isSuccess()) {
    $file_info = [
        'name' => $ret->name,
        'size' => $ret->size,
        'md5' => $ret->md5,
        'url' => $ret->url,
        'width' => $ret->width,
        'height' => $ret->height,
    ];
    // 保存到数据库...
}
```

## 自定义地址

```php
$aoss = new Aoss('your-open-token', 'complete', 'https://custom.example.com:433');
```

## SNI 域名直连（内网 IP）

内网环境访问 AOSSTP8 时，域名解析可能仍指向公网。通过 `set_sni` 可以保持域名不变，只把连接地址指向内网 IP：

- 请求 URL、Host 头、TLS SNI 与证书校验仍使用原域名；
- 端口一致时通过 `CURLOPT_RESOLVE` 替换解析地址；内网端口不同时通过 `CURLOPT_CONNECT_TO` 替换连接目标，均不会放宽证书校验；
- 域名可带 scheme/端口，SDK 只取主机名；对全部基于 `Aoss` 的客户端（含 `V3\*`）生效。

```php
use Tobycroft\AossSdk\Aoss;

// 初始化阶段调用即可
Aoss::set_sni('upload.tuuz.cc', '10.0.0.8');

// 内网端口与外网端口不一致时，额外指定实际连接端口
// URL 仍为 upload.tuuz.cc:433，实际连接到 10.0.0.8:8443
Aoss::set_sni('upload.tuuz.cc', '10.0.0.8', 8443);

// 批量设置（值支持 "IP" 或 "IP:端口"）
Aoss::set_snis([
    'upload.tuuz.cc'   => '10.0.0.8',
    'aoss.example.com' => '127.0.0.1',
    'port.example.com' => '10.0.0.9:8443',
]);

// 移除单个域名（IP 传空串）
Aoss::set_sni('upload.tuuz.cc', '');

// 清除全部 SNI 配置
Aoss::clear_sni();
```

| 方法 | 说明 |
|------|------|
| `set_sni(string $domain, string $ip, ?int $port = null): void` | 为域名设置连接 IP，可选指定实际连接端口；`$ip` 为空时移除该域名 |
| `set_snis(array $map): void` | 批量设置，值支持 `IP` 或 `IP:端口` |
| `clear_sni(): void` | 清除全部配置，恢复默认 DNS 解析 |
| `apply_sni($ch, string $url): void` | 给自定义 cURL 句柄注入 SNI 覆盖（可选） |