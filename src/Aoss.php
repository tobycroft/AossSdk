<?php

namespace Tobycroft\AossSdk;

use CURLFile;

class Aoss
{
    public string $remote_url = 'https://upload.tuuz.cc:433';
    protected string $send_url;
    protected string $send_path = "/v1/file/index";
    protected string $send_token = "?token=";
    protected string $token;
    protected string $mode;

    /**
     * SNI 域名直连映射：域名 => 连接 IP。
     *
     * 请求 URL、Host 头与 TLS SNI/证书校验仍使用域名，仅通过 CURLOPT_RESOLVE
     * 把实际连接地址替换为指定 IP，适用于内网访问公网域名的场景。
     *
     * @var array<string, string>
     */
    protected static array $sni_map = [];

    /**
     * 为指定域名配置连接 IP（可传内网 IP 或 127.0.0.1）。
     * $domain 可带 scheme/端口，SDK 只取主机名；$ip 传空串表示移除该域名的配置。
     */
    public static function set_sni(string $domain, string $ip): void
    {
        $host = self::sni_host($domain);
        if ($host === '') {
            return;
        }
        if ($ip === '') {
            unset(self::$sni_map[$host]);
            return;
        }
        self::$sni_map[$host] = trim($ip);
    }

    /**
     * 批量设置域名到 IP 的映射。
     *
     * @param array<string, string> $map
     */
    public static function set_snis(array $map): void
    {
        foreach ($map as $domain => $ip) {
            self::set_sni((string)$domain, (string)$ip);
        }
    }

    /** 清除全部 SNI 配置，恢复默认 DNS 解析。 */
    public static function clear_sni(): void
    {
        self::$sni_map = [];
    }

    /** 从域名或 URL 中提取小写主机名。 */
    protected static function sni_host(string $domain): string
    {
        $domain = trim($domain);
        if ($domain === '') {
            return '';
        }
        $url = str_contains($domain, '://') ? $domain : 'https://' . $domain;
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return '';
        }
        return strtolower(rtrim(trim($host, '[]'), '.'));
    }

    /** 给 cURL 句柄注入 SNI 解析覆盖（域名:端口:IP），域名不匹配时不做任何处理。 */
    public static function apply_sni($ch, string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return;
        }
        $key = strtolower(rtrim($host, '.'));
        if (!isset(self::$sni_map[$key])) {
            return;
        }
        $port = parse_url($url, PHP_URL_PORT);
        if (!$port) {
            $port = strtolower((string)parse_url($url, PHP_URL_SCHEME)) === 'http' ? 80 : 443;
        }
        $ip = self::$sni_map[$key];
        if (str_contains($ip, ':')) {
            $ip = '[' . trim($ip, '[]') . ']';
        }
        curl_setopt($ch, CURLOPT_RESOLVE, [$host . ':' . $port . ':' . $ip]);
    }

    /**
     * @discription 构建传入token,token可以发送邮件到aoss@tuuz.cc获取
     * @param $token
     * @param $mode
     * @param $remote_url
     */
    public function __construct($token, $mode = "complete", $remote_url = "")
    {
        $this->send_url = $remote_url;
        $this->token = $token;
        $this->mode = $mode;

        if (empty($remote_url)) {
            $this->send_url = $this->remote_url;
            $this->send_url .= $this->send_path . "/up_complete";
            $this->send_url .= $this->send_token . $this->token;
        }
    }


    public static function raw_post($send_url, $postData): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        self::apply_sni($ch, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        return $response;
    }


    /**
     * @send("文件地址","文件类型","文件名称")
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @param $send_url
     * @return bool|string
     */
    public static function curl_send_file($real_path, $mime_type, $file_name, $send_url): string|bool
    {
        $postData = [
            'file' => new CURLFile(realpath($real_path), $mime_type, $file_name)
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        self::apply_sni($ch, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        return $response;
    }


    /**
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossSimpleRet|AossCompleteRet
     */
    public function send($real_path, $mime_type, $file_name): AossSimpleRet|AossCompleteRet
    {
        return match ($this->mode) {
            "complete" => self::send_file_complete($this->send_url, $real_path, $mime_type, $file_name),
            default => self::send_file_url($this->send_url, $real_path, $mime_type, $file_name),
        };
    }

    /**
     * @param $md5
     * @return AossCompleteRet
     */
    public function md5($md5): AossCompleteRet
    {
        return self::check_file_complete($this->remote_url . $this->send_path . "/md5" . $this->send_token . $this->token, $md5);
    }


    /**
     * @param $send_url
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossSimpleRet
     */
    public static function send_file_url($send_url, $real_path, $mime_type, $file_name): AossSimpleRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $send_url);
        return new AossSimpleRet($response);
    }

    /**
     * @param $send_url
     * @param $real_path
     * @param $mime_type
     * @param $file_name
     * @return AossCompleteRet
     */
    public static function send_file_complete($send_url, $real_path, $mime_type, $file_name): AossCompleteRet
    {
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $send_url);
        return new AossCompleteRet($response);
    }

    /**
     * @param $send_url
     * @param $md5
     * @return AossCompleteRet
     */
    public static function check_file_complete($send_url, $md5): AossCompleteRet
    {
        $postData = [
            'md5' => $md5
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_url);
        self::apply_sni($ch, $send_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        return new AossCompleteRet($response);
    }
}