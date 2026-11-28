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
     * SNI 域名直连映射：域名 => ['ip' => 连接 IP, 'port' => 连接端口|null]。
     *
     * 请求 URL、Host 头与 TLS SNI/证书校验仍使用域名，仅把实际连接地址替换为
     * 指定 IP（可选指定内网端口），适用于内网访问公网域名的场景。
     *
     * @var array<string, array{ip: string, port: int|null}>
     */
    protected static array $sni_map = [];

    /**
     * 为指定域名配置连接 IP（可传内网 IP 或 127.0.0.1）。
     * $domain 可带 scheme/端口，SDK 只取主机名；$ip 传空串表示移除该域名的配置。
     * $port 为可选的实际连接端口：内网端口与外网不一样时传入，留空沿用 URL 中的端口。
     */
    public static function set_sni(string $domain, string $ip, ?int $port = null): void
    {
        $host = self::sni_host($domain);
        if ($host === '') {
            return;
        }
        if (trim($ip) === '') {
            unset(self::$sni_map[$host]);
            return;
        }
        $target = self::sni_target($ip, $port);
        if ($target['ip'] === '') {
            unset(self::$sni_map[$host]);
            return;
        }
        self::$sni_map[$host] = $target;
    }

    /**
     * 批量设置域名到 IP 的映射。
     * 值支持 "IP" 或 "IP:端口"；需要显式端口时也可用 set_sni()。
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

    /**
     * 解析连接目标：优先使用显式端口，其次识别 "IP:端口" 写法。
     *
     * @return array{ip: string, port: int|null}
     */
    protected static function sni_target(string $ip, ?int $port = null): array
    {
        $ip = trim($ip);
        if ($port === null && preg_match('/^\[([^\]]+)\]:(\d+)$/', $ip, $m)) {
            $ip = $m[1];
            $port = (int)$m[2];
        } elseif ($port === null && preg_match('/^([^:]+):(\d+)$/', $ip, $m)) {
            $ip = $m[1];
            $port = (int)$m[2];
        }
        return [
            'ip' => trim($ip, '[]'),
            'port' => $port !== null && $port > 0 ? $port : null,
        ];
    }

    /** 给 cURL 句柄注入连接地址覆盖，域名不匹配时不做任何处理。 */
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
        $target = self::$sni_map[$key];
        $ip = str_contains($target['ip'], ':') ? '[' . trim($target['ip'], '[]') . ']' : $target['ip'];
        if ($target['port'] === null || $target['port'] === (int)$port) {
            // 端口不变：仅替换解析地址，Host/SNI 保持域名。
            curl_setopt($ch, CURLOPT_RESOLVE, [$host . ':' . $port . ':' . $ip]);
            return;
        }
        // 端口不同：用 CONNECT_TO 替换连接目标，Host/SNI 仍使用原域名与原端口。
        curl_setopt($ch, CURLOPT_CONNECT_TO, [$host . ':' . $port . ':' . $ip . ':' . $target['port']]);
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