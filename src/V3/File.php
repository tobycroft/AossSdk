<?php

namespace Tobycroft\AossSdk\V3;

use Tobycroft\AossSdk\Aoss;
use Tobycroft\AossSdk\FileHashRet;
use Tobycroft\AossSdk\FileRet;
use Tobycroft\AossSdk\FileUrlRet;

/**
 * V3 文件上传开放接口（AOSSTP8 /v3/file/token/*）PHP 客户端
 *
 * 功能与旧版 File（/v2/file/token/*，md5(token+ts) 签名）一致：
 *   - getUploadToken           换取一次性上传 token
 *   - getUploadUrl             换取一次性 token + 完整上传地址
 *   - getUploadHashUrl         换取 hash 模式上传地址（上传后仅返回 md5）
 *   - getUploadedFileUrlByHash 按 md5 查询文件完整信息
 *
 * 鉴权改为 V3 凭证信封（见 Envelope），请求头 X-Aoss-Ts / X-Aoss-Kid / X-Aoss-Auth，
 * secret 永不明文传输。项目须在 AOSSTP8 后台「项目管理 v3」绑定一个 ao_oss 存储项目
 * （oss_token），未绑定时签发接口返回未配置。
 *
 * 前端直传阶段沿用一次性 token（upload_url），与 V2 行为一致——直传请求无法携带
 * 自定义鉴权头，也不能把 secret 交给前端；信封鉴权发生在签发 token 的环节。
 *
 * 响应结构与旧版 File 客户端复用（FileRet / FileUrlRet / FileHashRet）。
 */
class File extends Aoss
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

    /** 换取一次性上传 token POST /v3/file/token/create */
    public function getUploadToken(): FileRet
    {
        return new FileRet($this->call('/v3/file/token/create', []));
    }

    /**
     * 换取一次性 token + 完整上传地址 POST /v3/file/token/upload_url
     * @param string $ip 可选，上传来源 IP（写入 ao_attachment_token.ip）
     */
    public function getUploadUrl(string $ip = ''): FileUrlRet
    {
        $post = $ip !== '' ? ['ip' => $ip] : [];
        return new FileUrlRet($this->call('/v3/file/token/upload_url', $post));
    }

    /** 换取 hash 模式上传地址 POST /v3/file/token/upload_url_hash */
    public function getUploadHashUrl(): FileUrlRet
    {
        return new FileUrlRet($this->call('/v3/file/token/upload_url_hash', []));
    }

    /** 按 md5/hash 查询文件完整信息 POST /v3/file/token/hash_query */
    public function getUploadedFileUrlByHash(string $hash): FileHashRet
    {
        return new FileHashRet($this->call('/v3/file/token/hash_query', ['hash' => $hash]));
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
}
