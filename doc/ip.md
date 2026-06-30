# IP 范围校验

## 概述

通过 AOSS 平台进行 IP 地址范围校验，判断 IP 是否在指定国家/省份范围内。

## 包导入

```php
use Tobycroft\AossSdk\Ip;
use Tobycroft\AossSdk\Ip\Ret\IpRet;
```

## 构造方法

```php
public function __construct(string $token)
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | 项目 Open Token |

## 方法

### IpRange

```php
public function IpRange(string|int $country, string $province, string $ip): IpRet
```

校验 IP 是否在指定国家/省份范围内。

| 参数 | 类型 | 说明 |
|------|------|------|
| country | string\|int | 国家代码 |
| province | string | 省份 |
| ip | string | 要校验的 IP 地址 |

## 返回值

### IpRet

| 属性/方法 | 类型 | 说明 |
|-----------|------|------|
| isSuccess() | bool | 是否成功 |
| getError() | mixed | 获取错误信息 |

## 使用示例

```php
$ip = new Ip('your-open-token');
$ret = $ip->IpRange('CN', '广东', '192.168.1.1');

if ($ret->isSuccess()) {
    echo 'IP 在允许范围内';
} else {
    echo 'IP 不在允许范围内';
    echo $ret->getError();
}
```