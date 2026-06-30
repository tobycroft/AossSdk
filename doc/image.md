# 图片画布与二维码

## 概述

通过 AOSS 平台创建图片画布，支持文字、图片合成，以及生成条形码、二维码。

## 包导入

```php
use Tobycroft\AossSdk\Image;
use Tobycroft\AossSdk\ImageRet;
use Tobycroft\AossSdk\ImageRequestbuilder\ImageCreateImg;
use Tobycroft\AossSdk\ImageRequestbuilder\ImageCreateText;
```

## 构造方法

```php
public function __construct(string $token, string $remote_url = "")
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | 项目 Open Token |
| remote_url | string | 可选，自定义远程地址 |

## 方法

### create_canvas

```php
public function create_canvas(int $width, int $height, string $background = "ffffff", ImageCreateImg|ImageCreateText ...$data): GdImage|false
```

创建画布，可叠加图片和文字元素，返回 GD 图像资源。

| 参数 | 类型 | 说明 |
|------|------|------|
| width | int | 画布宽度 |
| height | int | 画布高度 |
| background | string | 背景色（十六进制），默认白色 |
| data | ImageCreateImg\|ImageCreateText | 可变参数，画布元素 |

### create_imgurl

```php
public function create_imgurl(int $width, int $height, string $background = "ffffff", ImageCreateImg|ImageCreateText ...$data): ImageRet
```

创建画布并返回图片 URL。

### create_barcode

```php
public function create_barcode(string $data): GdImage|false
```

生成条形码，返回 GD 图像资源。

### create_qr

```php
public function create_qr(string $data): GdImage|false
```

生成二维码，返回 GD 图像资源。

### create_qr_with_logo

```php
public function create_qr_with_logo(string $data, string $img_url): GdImage|false
```

生成带 Logo 的二维码。

### create_qr_b64

```php
public function create_qr_b64(string $data): string
```

生成二维码并返回 base64 字符串。

## 画布元素

### ImageCreateImg

```php
$img = new ImageCreateImg();
$img->type = "image";
$img->position = "mm"; // 位置单位
$img->x = 10;          // X 坐标
$img->y = 20;          // Y 坐标
$img->url = "https://example.com/image.png"; // 图片 URL
```

### ImageCreateText

```php
$text = new ImageCreateText();
$text->type = "text";
$text->position = "mm"; // 位置单位
$text->x = 10;          // X 坐标
$text->y = 20;          // Y 坐标
$text->text = "Hello";  // 文本内容
```

## 返回值

### ImageRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| url() | string | 图片 URL |
| base64() | string | 图片 base64 |
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

### 创建画布

```php
$image = new Image('your-open-token');

$img = new ImageCreateImg();
$img->url = 'https://example.com/logo.png';
$img->x = 0;
$img->y = 0;

$text = new ImageCreateText();
$text->text = 'Hello World';
$text->x = 100;
$text->y = 50;

$gd = $image->create_canvas(800, 600, 'ff0000', $img, $text);
if ($gd) {
    imagepng($gd, './output.png');
    imagedestroy($gd);
}
```

### 创建画布返回 URL

```php
$ret = $image->create_imgurl(800, 600, 'ffffff', $img, $text);
if ($ret->isSuccess()) {
    echo $ret->url();
}
```

### 生成二维码

```php
$image = new Image('your-open-token');

// 直接返回 GD 资源
$gd = $image->create_qr('https://example.com');
imagepng($gd, './qrcode.png');

// 返回 base64
$b64 = $image->create_qr_b64('https://example.com');
echo '<img src="' . $b64 . '">';

// 带 Logo 二维码
$gd = $image->create_qr_with_logo('https://example.com', 'https://example.com/logo.png');
```

### 生成条形码

```php
$gd = $image->create_barcode('1234567890');
imagepng($gd, './barcode.png');
```