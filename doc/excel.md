# Excel 文件上传与创建

## 概述

通过 AOSS 平台上传 Excel 文件或基于数据生成 Excel 文件，支持去重和直接下载。

## 包导入

```php
use Tobycroft\AossSdk\Excel\Excel;
use Tobycroft\AossSdk\Excel\ExcelCompleteRet;
use Tobycroft\AossSdk\Excel\ExcelCreateRet;
```

## 构造方法

```php
public function __construct(string $token)
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | 项目 Open Token |

## 方法

### send_excel

```php
public function send_excel(string $real_path, string $mime_type, string $file_name): ExcelCompleteRet
```

上传 Excel 文件。

| 参数 | 类型 | 说明 |
|------|------|------|
| real_path | string | 文件本地物理路径 |
| mime_type | string | 文件 MIME 类型 |
| file_name | string | 文件名 |

### send_md5

```php
public function send_md5(string $md5): ExcelCompleteRet
```

通过 MD5 查询已存在的 Excel 文件信息。

### create_excel_download_directly

```php
public function create_excel_download_directly(array $data): string|bool
```

基于二维数组数据生成 Excel 文件并直接下载，返回文件内容。

### create_excel_fileurl

```php
public function create_excel_fileurl(array $data): ExcelCreateRet
```

基于二维数组数据创建 Excel 文件，返回文件 URL。

## 返回值

### ExcelCompleteRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| $data | array | 完整数据 |
| $name | string | 文件名 |
| $path | string | 存储路径 |
| $mime | string | MIME 类型 |
| $size | int | 文件大小 |
| $ext | string | 扩展名 |
| $md5 | string | MD5 |
| $sha1 | string | SHA1 |
| $url | string | 完整 URL |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

### ExcelCreateRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| file_url() | string | 生成的 Excel 文件 URL |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

### 上传 Excel 文件

```php
$excel = new Excel('your-open-token');
$ret = $excel->send_excel('/path/to/data.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'data.xlsx');

if ($ret->isSuccess()) {
    echo $ret->url;
    echo $ret->md5;
}
```

### MD5 去重查询

```php
$excel = new Excel('your-open-token');
$ret = $excel->send_md5('d41d8cd98f00b204e9800998ecf8427e');

if ($ret->isSuccess()) {
    echo $ret->url; // 已存在，直接使用
}
```

### 创建 Excel 文件

```php
$excel = new Excel('your-open-token');

$data = [
    ['姓名', '年龄', '城市'],
    ['张三', 25, '北京'],
    ['李四', 30, '上海'],
    ['王五', 28, '广州'],
];

// 直接下载
$content = $excel->create_excel_download_directly($data);

// 获取文件 URL
$ret = $excel->create_excel_fileurl($data);
if ($ret->isSuccess()) {
    echo $ret->file_url();
}
```