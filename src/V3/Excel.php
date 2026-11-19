<?php

namespace Tobycroft\AossSdk\V3;

use Tobycroft\AossSdk\Excel\ExcelCompleteRet;
use Tobycroft\AossSdk\Excel\ExcelCreateRet;

/**
 * V3 Excel 开放接口（AOSSTP8 /v3/excel/*）PHP 客户端
 *
 * 功能与旧版 Excel（/v1/excel/*，token 鉴权）一致，鉴权改为 V3 凭证信封：
 * 请求头 X-Aoss-Ts / X-Aoss-Kid / X-Aoss-Auth，Secret 永不明文传输。
 * 项目凭证由 AOSSTP8 后台「项目管理 v3」签发（ao_v3_project）。
 *
 * 响应统一为 {code,data,echo}；create_excel_download_directly 成功时
 * 返回 xlsx 二进制原文，失败时返回 JSON 错误串。
 */
class Excel extends \Tobycroft\AossSdk\Aoss
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
     * 上传表格文件并解析（首行作 key）POST /v3/excel/index/dp
     */
    public function send_excel(string $real_path, string $mime_type, string $file_name): ExcelCompleteRet
    {
        $response = self::envelope_curl_send_file($real_path, $mime_type, $file_name, $this->baseUrl . '/v3/excel/index/dp', $this->secret);
        return new ExcelCompleteRet($response);
    }

    /**
     * 按已上传附件 md5 解析（带服务端缓存）POST /v3/excel/search/md5
     */
    public function send_md5(string $md5): ExcelCompleteRet
    {
        $response = self::envelope_raw_post($this->baseUrl . '/v3/excel/search/md5', $this->secret, ['md5' => $md5]);
        return new ExcelCompleteRet($response);
    }

    /**
     * 二维数组生成 xlsx 并直接返回二进制原文 POST /v3/excel/index/create
     * 成功返回 xlsx 字节流（可直接落盘/输出），失败返回 JSON 错误串。
     */
    public function create_excel_download_directly(array $data): bool|string
    {
        return self::envelope_raw_post($this->baseUrl . '/v3/excel/index/create', $this->secret, ['data' => json_encode($data, 320)]);
    }

    /**
     * 二维数组生成 xlsx 落盘并返回可下载 URL POST /v3/excel/index/create_file
     */
    public function create_excel_fileurl(array $data): ExcelCreateRet
    {
        $response = self::envelope_raw_post($this->baseUrl . '/v3/excel/index/create_file', $this->secret, ['data' => json_encode($data, 320)]);
        return new ExcelCreateRet($response);
    }

    /** 带 V3 信封鉴权头的表单 POST */
    protected static function envelope_raw_post(string $url, string $secret, array $postData): bool|string
    {
        $ts = time();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Aoss-Ts: ' . $ts,
            'X-Aoss-Kid: ' . Envelope::kid($secret),
            'X-Aoss-Auth: ' . Envelope::seal($secret, $ts),
        ]);
        $response = curl_exec($ch);
        return $response;
    }

    /** 带 V3 信封鉴权头的 multipart 文件 POST */
    protected static function envelope_curl_send_file(string $real_path, string $mime_type, string $file_name, string $url, string $secret): string|bool
    {
        $ts = time();
        $postData = [
            'file' => new \CURLFile(realpath($real_path), $mime_type, $file_name),
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Aoss-Ts: ' . $ts,
            'X-Aoss-Kid: ' . Envelope::kid($secret),
            'X-Aoss-Auth: ' . Envelope::seal($secret, $ts),
        ]);
        $response = curl_exec($ch);
        return $response;
    }
}
