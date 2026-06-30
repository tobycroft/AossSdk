# 直播云（LCIC）

## 概述

通过 AOSS 平台调用阿里云直播云（LCIC）接口，快速创建、修改、删除直播房间。

## 包导入

```php
use Tobycroft\AossSdk\Lcic;
use Tobycroft\AossSdk\Lcic\Ret\LcicUserAutoRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicRoomCreateRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicRoomModifyRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicRoomDeleteRet;
use Tobycroft\AossSdk\Lcic\Ret\LcicRoomUrlRet;
```

## 构造方法

```php
public function __construct(string $token)
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | 项目 Open Token |

## 方法

### CreateUser

```php
public function CreateUser(string|int $Name, mixed $OriginId, string $Avatar): LcicUserAutoRet
```

自动创建用户（已存在则返回）。

| 参数 | 类型 | 说明 |
|------|------|------|
| Name | string | 用户在直播间显示名称 |
| OriginId | mixed | 用户在你系统中的标识符 |
| Avatar | string | 用户头像 URL |

### RoomCreate

```php
public function RoomCreate(string|int $TeacherId, int $StartTime, int $EndTime, string $Name): LcicRoomCreateRet
```

创建直播房间。

| 参数 | 类型 | 说明 |
|------|------|------|
| TeacherId | string\|int | 讲师在你系统中的 ID |
| StartTime | int | 开始时间（Unix 时间戳） |
| EndTime | int | 结束时间（Unix 时间戳） |
| Name | string | 直播房间名称 |

### RoomModify

```php
public function RoomModify(string|int $RoomId, string|int $TeacherId, int $StartTime, int $EndTime, string $Name): LcicRoomModifyRet
```

修改直播房间信息。

| 参数 | 类型 | 说明 |
|------|------|------|
| RoomId | string\|int | 房间 ID |
| TeacherId | string\|int | 讲师在你系统中的 ID |
| StartTime | int | 开始时间（Unix 时间戳） |
| EndTime | int | 结束时间（Unix 时间戳） |
| Name | string | 直播房间名称 |

### RoomDelete

```php
public function RoomDelete(string|int $RoomId): LcicRoomDeleteRet
```

删除直播房间。

| 参数 | 类型 | 说明 |
|------|------|------|
| RoomId | string\|int | 房间 ID |

### RoomUrl

```php
public function RoomUrl(string|int $OriginId, string|int $TeacherId): LcicRoomUrlRet
```

获取学生端直播房间链接。

| 参数 | 类型 | 说明 |
|------|------|------|
| OriginId | string\|int | 学生 ID |
| TeacherId | string\|int | 讲师 ID |

## 使用示例

### 创建直播

```php
$lcic = new Lcic('your-open-token');

// 创建讲师用户
$userRet = $lcic->CreateUser('张老师', 1001, 'https://example.com/avatar.jpg');
if ($userRet->isSuccess()) {
    $userId = $userRet->getUserId();
}

// 创建直播房间
$start = strtotime('2024-06-30 19:00:00');
$end = strtotime('2024-06-30 21:00:00');

$roomRet = $lcic->RoomCreate(1001, $start, $end, 'PHP入门直播课');
if ($roomRet->isSuccess()) {
    $roomId = $roomRet->getRoomId();
    echo '创建成功: ' . $roomId;
}
```

### 获取学生观看链接

```php
$lcic = new Lcic('your-open-token');
$ret = $lcic->RoomUrl(2001, 1001); // 学生 2001 看老师 1001 的课

if ($ret->isSuccess()) {
    echo $ret->getUrl();
}
```

### 修改直播信息

```php
$lcic = new Lcic('your-open-token');
$ret = $lcic->RoomModify($roomId, 1001, $start, $end, 'PHP入门直播课（更新）');

if ($ret->isSuccess()) {
    echo '修改成功';
}
```

### 删除直播

```php
$lcic = new Lcic('your-open-token');
$ret = $lcic->RoomDelete($roomId);

if ($ret->isSuccess()) {
    echo '删除成功';
}
```