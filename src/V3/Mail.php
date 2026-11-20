<?php

namespace Tobycroft\AossSdk\V3;

/**
 * V3 邮件开放接口（AOSSTP8 /v3/mail/send/*）PHP 客户端
 *
 * 服务端提供三种内容类型：
 *   sendCaptcha   验证码：主题/正文由后台模板统一维护，调用方只提供验证码（≤16 位）
 *   sendMarketing 营销：模板统一维护，调用方只替换 {变量名} 关键内容（如链接）
 *   sendCustom    自定义：主题与正文完全由调用方提供
 *
 * 鉴权采用 V3 凭证信封（见 Envelope），请求头 X-Aoss-Ts / X-Aoss-Kid / X-Aoss-Auth，
 * secret 永不明文传输。项目须在 AOSSTP8 后台授权至少一台邮件服务器；
 * 使用模板的类型还须先在后台「邮件管理 → 邮件模板」配置并启用模板。
 */
class Mail extends \Tobycroft\AossSdk\Aoss
{
    /** 服务端签发的 64 位十六进制凭证，同时作为信封密钥 */
    protected string $secret;
    protected string $baseUrl;

    /**
     * @param string $secret 由 AOSSTP8 后台「项目管理 v3」签发，请通过配置注入，勿硬编码
     * @param string $baseUrl V3 开放接口域名根；留空则复用 SDK 公用地址 Aoss::$remote_url
     */
    public function __construct(string $secret, string $baseUrl = '')
    {
        $this->secret = $secret;
        $this->baseUrl = rtrim($baseUrl !== '' ? $baseUrl : $this->remote_url, '/');
    }

    /**
     * 验证码邮件 POST /v3/mail/send/captcha
     *
     * $code 为 1~16 位验证码；$template 为后台模板标识，模板须含 {code} 占位符。
     * options 支持 cc, alt_body, from_addr, from_name, server_id。
     */
    public function sendCaptcha(string $to, string $template, string $code, array $options = []): MailRet
    {
        $post = ['to' => $to, 'template' => $template, 'code' => $code];
        self::copyOptions($post, $options, ['cc', 'alt_body', 'from_addr', 'from_name', 'server_id']);
        return new MailRet($this->call('/v3/mail/send/captcha', $post));
    }

    /**
     * 营销邮件 POST /v3/mail/send/marketing
     *
     * $vars 为变量名到值的映射（如 ['link' => 'https://example.com']），
     * 与后台模板中的 {变量名} 占位符一一对应，最多 50 项、单值最长 1000 字符。
     * options 支持 cc, alt_body, from_addr, from_name, server_id。
     */
    public function sendMarketing(string $to, string $template, array $vars, array $options = []): MailRet
    {
        $post = ['to' => $to, 'template' => $template, 'vars' => self::jsonString($vars)];
        self::copyOptions($post, $options, ['cc', 'alt_body', 'from_addr', 'from_name', 'server_id']);
        return new MailRet($this->call('/v3/mail/send/marketing', $post));
    }

    /**
     * 自定义邮件 POST /v3/mail/send/custom
     *
     * options 支持 cc, alt_body, is_html, from_addr, from_name, server_id。
     */
    public function sendCustom(string $to, string $subject, string $body, array $options = []): MailRet
    {
        $post = ['to' => $to, 'subject' => $subject, 'body' => $body];
        self::copyOptions($post, $options, ['cc', 'alt_body', 'is_html', 'from_addr', 'from_name', 'server_id']);
        return new MailRet($this->call('/v3/mail/send/custom', $post));
    }

    /** 发起表单请求，返回原始响应字符串；网络错误抛异常 */
    protected function call(string $path, array $post): string
    {
        $ts = time();
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Aoss-Ts: ' . $ts,
            'X-Aoss-Kid: ' . Envelope::kid($this->secret),
            'X-Aoss-Auth: ' . Envelope::seal($this->secret, $ts),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            throw new \RuntimeException('V3 请求失败: ' . $error);
        }
        return $response;
    }

    /** 结构化字段编码为 JSON 字符串；已是字符串则原样返回 */
    private static function jsonString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** 透传简单标量字段，仅复制 options 中实际存在的键 */
    private static function copyOptions(array &$post, array $options, array $keys): void
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $post[$key] = $options[$key];
            }
        }
    }
}
