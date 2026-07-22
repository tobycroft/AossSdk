# 验证码 (Captcha)

## 概述

AOSS 提供多种验证码方案：

| 类型 | 前端交互 | 生成方法 | 验证方法 | 适用场景 |
|------|---------|---------|---------|---------|
| 文本验证码（数学/数字/中文/字母） | 用户填写文字 | `math()` `number()` `chinese()` `text()` | `check()` `check_in_time()` | 登录、注册、评论 |
| 动态 GIF 验证码 | 用户填写文字 | `gif_text()` `gif_fast()` `gif_number()` 等 | `check()` `check_in_time()` | 防爬虫增强 |
| 滑动拼图验证码 | 用户拖动滑块 | `slide()` | `slide_check()` | 登录、支付、重要操作 |
| 点击验证码 | 用户依次点击图标 | `click()` | `click_check()` | 高安全场景 |

---

## 包导入

```php
use Tobycroft\AossSdk\Captcha;
use Tobycroft\AossSdk\CaptchaRet;
```

## 构造方法

```php
public function __construct(string $token, string $remote_url = "")
```

| 参数 | 类型 | 说明 |
|------|------|------|
| token | string | 项目 token |
| remote_url | string | 可选，自定义远程 API 地址 |

---

## 一、文本验证码

### 生成验证码图片

| 方法 | 说明 | 返回类型 |
|------|------|---------|
| `math($ident)` | 数学算式验证码（如 "3+5"） | `GdImage\|false` |
| `number($ident)` | 数字验证码 | `GdImage\|false` |
| `chinese($ident)` | 中文验证码 | `GdImage\|false` |
| `text($ident)` | 字母+数字混合 | `GdImage\|false` |

**参数：**
- `$ident` — 验证码标识，建议用 session id 或用户唯一标识。**每次调用必须使用新的 ident，防止验证码被复用**。

**使用示例（Laravel 控制器）：**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tobycroft\AossSdk\Captcha;

class CaptchaController extends Controller
{
    protected $captcha;

    public function __construct()
    {
        $this->captcha = new Captcha('your-project-token');
    }

    public function getCaptcha(Request $request)
    {
        $ident = $request->session()->getId();
        $gd = $this->captcha->math($ident);

        if (!$gd) {
            return response('生成验证码失败', 500);
        }

        return response()->stream(function () use ($gd) {
            imagepng($gd);
            imagedestroy($gd);
        }, 200, ['Content-Type' => 'image/png']);
    }

    public function verifyCaptcha(Request $request)
    {
        $ident = $request->session()->getId();
        $code = $request->input('code');

        $ret = $this->captcha->check($ident, $code);

        return response()->json([
            'code' => $ret->getCode(),
            'msg' => $ret->getEcho(),
        ]);
    }
}
```

### 验证验证码

```php
public function check($ident, $code): CaptchaRet
```

| 参数 | 类型 | 说明 |
|------|------|------|
| ident | string | 验证码标识，与生成时一致 |
| code | string | 用户输入的验证码 |

**CaptchaRet 返回：**

| 方法 | 说明 |
|------|------|
| `getCode()` | 0 表示成功，其他为失败 |
| `getEcho()` | 成功/错误信息 |
| `isSuccess()` | 是否成功 |

```php
$ret = $captcha->check($ident, $code);
if ($ret->getCode() == 0) {
    echo "验证通过";
} else {
    echo "验证失败: " . $ret->getEcho();
}
```

### 带有效期验证

```php
public function check_in_time($ident, $code, $validtime_in_second): CaptchaRet
```

```php
// 验证码在 5 分钟内有效
$ret = $captcha->check_in_time($ident, $code, 300);
```

---

## 二、动态 GIF 验证码

GIF 验证码返回 GIF 二进制字符串，适合防爬虫。

| 方法 | 说明 |
|------|------|
| `gif_text($ident)` | 字母+数字，1 秒一帧 |
| `gif_fast($ident)` | 字母+数字，0.5 秒一帧 |
| `gif_number($ident)` | 纯数字，1 秒一帧 |
| `gif_number_fast($ident)` | 纯数字，0.5 秒一帧 |
| `gif_letters($ident)` | 纯字母，1 秒一帧 |
| `gif_letters_fast($ident)` | 纯字母，0.5 秒一帧 |

**使用示例：**

```php
$captcha = new Captcha('your-project-token');
$gif = $captcha->gif_number_fast('user-session-id');

header('Content-Type: image/gif');
echo $gif;
```

---

## 三、滑动拼图验证码（推荐）

滑动拼图验证码需要前后端配合：**后端负责生成和校验，前端负责渲染图片和滑块交互。**

### 后端：生成验证码

```php
public function slide($ident): array|false
```

**返回数据结构：**

```php
[
    'bg' => 'data:image/png;base64,...',   // 背景图（带缺口）
    'block' => 'data:image/png;base64,...',// 滑块图（拼图块）
    'y' => 50,                              // 滑块纵坐标
    'bg_width' => 300,                      // 背景图宽度
    'bg_height' => 150,                      // 背景图高度
    'block_size' => 40,                      // 滑块边长
    'pad_top' => 5,                          // 滑块图上边距
    'pad_left' => 5,                         // 滑块图左边距
]
```

### 后端：验证验证码

```php
public function slide_check($ident, $x): CaptchaRet
```

| 参数 | 类型 | 说明 |
|------|------|------|
| ident | string | 与生成时一致的标识 |
| x | int | 滑块最终水平位置（像素） |

---

### 前后端完整示例：ThinkPHP 8 + 原生 HTML/JS

**后端控制器 `app/controller/CaptchaController.php`：**

```php
<?php

namespace app\controller;

use think\Response;
use Tobycroft\AossSdk\Captcha;

class CaptchaController
{
    public function slideCreate(): Response
    {
        $captcha = new Captcha('your-project-token');
        $ident = 'slide_' . uniqid() . '_' . mt_rand();

        $data = $captcha->slide($ident);
        if (!$data) {
            return json(['code' => 500, 'echo' => '生成验证码失败']);
        }

        return json([
            'code' => 0,
            'ident' => $ident,
            'data' => $data,
        ]);
    }

    public function slideCheck(): Response
    {
        $captcha = new Captcha('your-project-token');
        $ident = request()->post('ident');
        $x = intval(request()->post('x'));

        $ret = $captcha->slide_check($ident, $x);
        return json([
            'code' => $ret->getCode(),
            'echo' => $ret->getEcho(),
        ]);
    }
}
```

**前端页面 `resources/view/captcha/slide.html`：**

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>滑动拼图验证码</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f5f5f5; }
        .captcha-container { position: relative; width: 300px; height: 190px; border: 1px solid #ccc; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); overflow: hidden; }
        .captcha-bg { width: 300px; height: 150px; display: block; position: absolute; top: 0; left: 0; }
        .captcha-block { position: absolute; left: 0; top: 0; cursor: grab; z-index: 10; transition: opacity 0.3s ease; }
        .captcha-block:active { cursor: grabbing; }
        .captcha-slider { position: absolute; bottom: 0; left: 0; width: 100%; height: 40px; background: #f5f5f5; border-top: 1px solid #eee; }
        .slider-handle { width: 40px; height: 100%; background: #409eff; color: white; text-align: center; line-height: 40px; cursor: grab; position: absolute; font-size: 16px; border-radius: 2px; transition: background-color 0.2s ease; user-select: none; }
        .slider-handle:active { cursor: grabbing; background: #66b1ff; }
        .slider-handle.success { background: #67c23a; }
        .slider-handle.error { background: #f56c6c; }
        .slide-tip { text-align: center; margin-top: 12px; color: #666; min-height: 24px; }
        .slide-status { margin-top: 8px; text-align: center; height: 24px; transition: all 0.3s ease; }
        .slide-status.success { color: #67c23a; font-weight: bold; }
        .slide-status.error { color: #f56c6c; font-weight: bold; }
    </style>
</head>
<body>
    <div>
        <div class="captcha-container">
            <img class="captcha-bg" src="" alt="验证码背景">
            <img class="captcha-block" src="" alt="验证码块" style="display:none;">
            <div class="captcha-slider"><div class="slider-handle">👉</div></div>
        </div>
        <div class="slide-tip">请拖动下方滑块将拼图拼合</div>
        <div class="slide-status"></div>
    </div>

    <script>
    let captchaData = null;
    let captchaIdent = '';
    let startX = 0;
    let startLeft = 0;

    const bgImg = document.querySelector('.captcha-bg');
    const blockImg = document.querySelector('.captcha-block');
    const sliderHandle = document.querySelector('.slider-handle');
    const statusEl = document.querySelector('.slide-status');

    function generate() {
        fetch('/captcha/slide/create', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    captchaData = data.data;
                    captchaIdent = data.ident;
                    captchaData.pad_top = captchaData.pad_top || 0;
                    captchaData.pad_left = captchaData.pad_left || 0;

                    bgImg.src = captchaData.bg;
                    blockImg.style.width = captchaData.block_size + 'px';
                    blockImg.style.height = captchaData.block_size + 'px';
                    blockImg.style.top = (captchaData.y - captchaData.pad_top) + 'px';
                    blockImg.style.left = (-captchaData.pad_left) + 'px';
                    blockImg.onload = function() { blockImg.style.display = 'block'; };
                    blockImg.src = captchaData.block;

                    sliderHandle.style.left = '0px';
                    sliderHandle.classList.remove('success', 'error');
                    statusEl.textContent = '';
                    statusEl.className = 'slide-status';
                } else {
                    statusEl.textContent = data.echo || '生成验证码失败';
                    statusEl.className = 'slide-status error';
                }
            });
    }

    function startDrag(e) {
        if (!captchaData) return;
        startX = e.clientX || (e.touches && e.touches[0].clientX);
        startLeft = parseInt(sliderHandle.style.left) || 0;
    }

    function drag(e) {
        if (!captchaData) return;
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const deltaX = clientX - startX;
        const newLeft = Math.max(0, Math.min(startLeft + deltaX, captchaData.bg_width - captchaData.block_size));
        sliderHandle.style.left = newLeft + 'px';
        blockImg.style.left = (newLeft - captchaData.pad_left) + 'px';
    }

    function endDrag(e) {
        if (!captchaData) return;
        const finalX = parseInt(sliderHandle.style.left) || 0;

        const formData = new FormData();
        formData.append('ident', captchaIdent);
        formData.append('x', finalX);

        fetch('/captcha/slide/check', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    sliderHandle.classList.add('success');
                    statusEl.textContent = '✅ 验证成功！';
                    statusEl.className = 'slide-status success';
                } else {
                    sliderHandle.classList.add('error');
                    statusEl.textContent = '❌ ' + (data.echo || '验证失败') + '，正在刷新...';
                    statusEl.className = 'slide-status error';
                    setTimeout(generate, 1500);
                }
            });
    }

    sliderHandle.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', endDrag);
    sliderHandle.addEventListener('touchstart', startDrag, { passive: true });
    document.addEventListener('touchmove', drag, { passive: true });
    document.addEventListener('touchend', endDrag);

    generate();
    </script>
</body>
</html>
```

---

## 四、点击验证码（安全级别最高）

点击验证码要求用户根据提示**依次点击图片中的特定图标**，需前后端配合。

### 后端：生成验证码

```php
public function click($ident): array|false
```

**返回数据结构：**

```php
[
    'bg' => 'data:image/png;base64,...',   // 背景图（带有多个可点击图标）
    'tip' => '请依次点击：A、B、C',         // 给用户看的提示文字
    'targets_count' => 3,                   // 需要点击的图标数量
    'bg_width' => 300,                      // 背景图宽度
    'bg_height' => 200,                      // 背景图高度
]
```

### 后端：验证验证码

```php
public function click_check($ident, array $clicks): CaptchaRet
```

| 参数 | 类型 | 说明 |
|------|------|------|
| ident | string | 与生成时一致的标识 |
| clicks | array | 点击坐标数组，格式 `[['x' => 100, 'y' => 80], ['x' => 200, 'y' => 120]]` |

---

### 前后端完整示例：ThinkPHP 8 + 原生 HTML/JS

**后端控制器 `app/controller/CaptchaController.php`：**

```php
<?php

namespace app\controller;

use think\Response;
use Tobycroft\AossSdk\Captcha;

class CaptchaController
{
    public function clickCreate(): Response
    {
        $captcha = new Captcha('your-project-token');
        $ident = 'click_' . uniqid() . '_' . mt_rand();

        $data = $captcha->click($ident);
        if (!$data) {
            return json(['code' => 500, 'echo' => '生成验证码失败']);
        }

        return json([
            'code' => 0,
            'ident' => $ident,
            'data' => $data,
        ]);
    }

    public function clickCheck(): Response
    {
        $captcha = new Captcha('your-project-token');
        $ident = request()->post('ident');
        $clicks = json_decode(request()->post('clicks', '[]'), true);

        $ret = $captcha->click_check($ident, $clicks);
        return json([
            'code' => $ret->getCode(),
            'echo' => $ret->getEcho(),
        ]);
    }
}
```

**前端页面 `resources/view/captcha/click.html`：**

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>点击验证码</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f5f5f5; }
        .click-captcha-wrapper { text-align: center; }
        .click-captcha-container { position: relative; width: 300px; height: 200px; border: 1px solid #ccc; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); cursor: default; display: inline-block; user-select: none; -webkit-user-select: none; }
        .click-captcha-bg { width: 300px; height: 200px; display: block; }
        .click-marker { position: absolute; width: 22px; height: 22px; line-height: 22px; text-align: center; background: rgba(64, 158, 255, 0.8); color: white; border-radius: 50%; font-size: 12px; font-weight: bold; transform: translate(-50%, -50%); pointer-events: none; z-index: 10; animation: marker-pop 0.2s ease-out; }
        @keyframes marker-pop { 0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0; } 100% { transform: translate(-50%, -50%) scale(1); opacity: 1; } }
        .click-tip { margin-top: 12px; font-size: 15px; color: #333; font-weight: bold; min-height: 22px; }
        .click-count { margin-top: 4px; font-size: 13px; color: #999; min-height: 20px; }
        .click-status { margin-top: 8px; font-size: 14px; min-height: 20px; transition: all 0.3s ease; }
        .click-status.success { color: #67c23a; font-weight: bold; }
        .click-status.error { color: #f56c6c; font-weight: bold; }
        .click-reload-btn { margin-top: 10px; padding: 8px 16px; background: #409eff; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background 0.3s ease; }
        .click-reload-btn:hover { background: #66b1ff; }
        .click-reload-btn:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="click-captcha-wrapper">
        <div class="click-captcha-container">
            <img class="click-captcha-bg" src="" alt="验证码背景">
        </div>
        <div class="click-tip"></div>
        <div class="click-count"></div>
        <div class="click-status"></div>
        <button class="click-reload-btn">刷新验证码</button>
    </div>

    <script>
    let captchaData = null;
    let captchaIdent = '';
    let userClicks = [];
    let targetCount = 0;

    const bgImg = document.querySelector('.click-captcha-bg');
    const tipEl = document.querySelector('.click-tip');
    const clickCountEl = document.querySelector('.click-count');
    const statusEl = document.querySelector('.click-status');
    const reloadBtn = document.querySelector('.click-reload-btn');
    const container = document.querySelector('.click-captcha-container');

    function generate() {
        userClicks = [];
        const markers = container.querySelectorAll('.click-marker');
        markers.forEach(m => m.remove());

        fetch('/captcha/click/create', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    captchaData = data.data;
                    captchaIdent = data.ident;
                    targetCount = captchaData.targets_count;
                    bgImg.src = captchaData.bg;
                    tipEl.textContent = captchaData.tip;
                    clickCountEl.textContent = '还需点击 ' + targetCount + ' 个';
                    statusEl.textContent = '';
                    statusEl.className = 'click-status';
                } else {
                    statusEl.textContent = data.echo || '生成验证码失败';
                    statusEl.className = 'click-status error';
                }
            });
    }

    function drawMarker(x, y, num) {
        const marker = document.createElement('div');
        marker.className = 'click-marker';
        marker.style.left = x + 'px';
        marker.style.top = y + 'px';
        marker.textContent = num;
        container.appendChild(marker);
    }

    function verify() {
        const formData = new FormData();
        formData.append('ident', captchaIdent);
        formData.append('clicks', JSON.stringify(userClicks));

        fetch('/captcha/click/check', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    statusEl.textContent = '✅ 验证成功！';
                    statusEl.className = 'click-status success';
                } else {
                    statusEl.textContent = '❌ ' + (data.echo || '验证失败') + '，正在刷新...';
                    statusEl.className = 'click-status error';
                    setTimeout(generate, 1500);
                }
            });
    }

    container.addEventListener('click', function(e) {
        if (!captchaData) return;
        const rect = container.getBoundingClientRect();
        const x = Math.round(e.clientX - rect.left);
        const y = Math.round(e.clientY - rect.top);
        if (y >= captchaData.bg_height) return;

        userClicks.push({ x: x, y: y });
        drawMarker(x, y, userClicks.length);

        const remaining = targetCount - userClicks.length;
        if (remaining > 0) {
            clickCountEl.textContent = '还需点击 ' + remaining + ' 个';
        } else {
            clickCountEl.textContent = '正在验证...';
            verify();
        }
    });

    reloadBtn.addEventListener('click', function() {
        if (reloadBtn.disabled) return;
        reloadBtn.disabled = true;
        generate();
        setTimeout(function() { reloadBtn.disabled = false; }, 500);
    });

    generate();
    </script>
</body>
</html>
```

---

## 五、通用接口参数

### 文本/GIF 验证码

| 步骤 | 接口 | 参数 | 返回 |
|------|------|------|------|
| 生成 | `POST /v1/captcha/text/{type}` | `token`, `ident` | PNG 图片二进制 / GIF 二进制 |
| 校验 | `POST /v1/captcha/auth/check` | `token`, `ident`, `code` | `{ code, echo }` |
| 校验（带有效期） | `POST /v1/captcha/auth/check_in_time` | `token`, `ident`, `code`, `second` | `{ code, echo }` |

### 滑动拼图验证码

| 步骤 | 接口 | 参数 | 返回 |
|------|------|------|------|
| 生成 | `POST /v1/captcha/slide/create` | `token`, `ident` | `{ code, data: { bg, block, y, bg_width, bg_height, block_size, pad_top, pad_left } }` |
| 校验 | `POST /v1/captcha/slide/check` | `token`, `ident`, `x` | `{ code, echo }` |

### 点击验证码

| 步骤 | 接口 | 参数 | 返回 |
|------|------|------|------|
| 生成 | `POST /v1/captcha/click/create` | `token`, `ident` | `{ code, data: { bg, tip, targets_count, bg_width, bg_height } }` |
| 校验 | `POST /v1/captcha/click/check` | `token`, `ident`, `clicks`（JSON 字符串） | `{ code, echo }` |

---

## 注意事项

1. **Ident 唯一**：每次调用 `create` / `slide` / `click` 时，务必使用新的 ident，防止验证码被复用攻击
2. **Token 安全**：生产环境建议 token 由后端管理，不要直接暴露在前端 JS 中；可通过后端代理转发请求
3. **响应式适配**：上述前端示例中 `.captcha-container` 固定为 300px。如需移动端适配，可改为 `width: 100%` 并动态调整尺寸
4. **触摸支持**：示例已内置 `touchstart/touchmove/touchend`，可直接在移动端使用
5. **HTTP 缓存**：验证码接口返回默认禁止缓存，前端 fetch 时请不要添加缓存头