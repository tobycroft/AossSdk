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
$aoss = new Aoss('your-open-token', 'complete', 'https://custom.example.com:444');
```