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
 *   - checkMd5                 秒传前置：按 md5 查系统内是否已有文件（跨项目）
 *   - prepareUpload/prepareUploadByMd5 命中直接返回 path，未命中返回标准上传地址
 *   - getUploadedFileUrlByHash 按 md5 查询文件完整信息（当前 Bucket 分组）
 *   - getTempUrl                按 md5/hash 签发客户端指定 ttl 的临时地址（v2.8.0+）
 *   - delete / restore          软删（移入 upload/recycle/YYYYMMDD/）与恢复
 *
 * 地址口径：url / file_url 均为 /attachment 或 /file 虚拟地址，不暴露真实落盘路径；
 * hash 查询默认给 attachment 永久地址，传 ttl 或调 getTempUrl 时 file_url 为 file 临时地址。
 *
 * 鉴权改为 V3 凭证信封（见 Envelope），请求头 X-Aoss-Ts / X-Aoss-Kid / X-Aoss-Auth，
 * secret 永不明文传输。存储由后台「存储管理 V3 → 存储项目」授权的 Bucket 决定：
 *   - 不传 bucket：使用该项目设定的默认 Bucket（兼容旧版调用）；
 *   - 传 bucket：必须是该项目已授权的 Bucket（按 Bucket 名称），否则签发接口返回 403。
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

    /**
     * 换取一次性上传 token POST /v3/file/token/create
     * @param string $bucket 可选，目标 Bucket 名称；留空用后台设定的默认 Bucket
     */
    public function getUploadToken(string $bucket = ''): FileRet
    {
        return new FileRet($this->call('/v3/file/token/create', $this->bucketPost($bucket)));
    }

    /**
     * 换取一次性 token + 完整上传地址 POST /v3/file/token/upload_url
     * @param string $ip 可选，上传来源 IP（写入 ao_attachment_token.ip）
     * @param string $bucket 可选，目标 Bucket 名称；留空用后台设定的默认 Bucket
     */
    public function getUploadUrl(string $ip = '', string $bucket = ''): FileUrlRet
    {
        $post = $this->bucketPost($bucket);
        if ($ip !== '') {
            $post['ip'] = $ip;
        }
        return new FileUrlRet($this->call('/v3/file/token/upload_url', $post));
    }

    /**
     * 换取 hash 模式上传地址 POST /v3/file/token/upload_url_hash
     * @param string $bucket 可选，目标 Bucket 名称；留空用后台设定的默认 Bucket
     */
    public function getUploadHashUrl(string $bucket = ''): FileUrlRet
    {
        return new FileUrlRet($this->call('/v3/file/token/upload_url_hash', $this->bucketPost($bucket)));
    }

    /** 老调用不传 bucket 时不带该字段，服务端自动回退默认 Bucket */
    private function bucketPost(string $bucket): array
    {
        return $bucket !== '' ? ['bucket' => $bucket] : [];
    }

    /**
     * 秒传前置查询：md5 是否已存在于系统（跨项目）POST /v3/file/token/md5_check
     *
     * 未命中返回 exists=false（不抛错），可直接用于流程分支。
     */
    public function checkMd5(string $md5): FileCheckRet
    {
        $md5 = strtolower(trim($md5));
        if ($md5 === '') {
            return FileCheckRet::fromError('需要md5字段');
        }
        return new FileCheckRet($this->call('/v3/file/token/md5_check', ['md5' => $md5]));
    }

    /**
     * 上传准备（已算好 md5）：命中直接给 path，未命中自动申请上传 token。
     *
     * @param string $md5 32 位 md5
     * @param string $ip 可选上传来源 IP
     * @param string $bucket 可选目标 Bucket，留空走服务端默认 Bucket
     */
    public function prepareUploadByMd5(string $md5, string $ip = '', string $bucket = ''): UploadPrepareRet
    {
        $md5 = strtolower(trim($md5));
        if ($md5 === '') {
            return UploadPrepareRet::fromError('需要md5字段');
        }
        $check = $this->checkMd5($md5);
        if (!$check->isSuccess()) {
            return UploadPrepareRet::fromError($check->getError(), $md5);
        }
        if ($check->exists) {
            return UploadPrepareRet::fromCheck($check, $md5);
        }
        $url = $this->getUploadUrl($ip, $bucket);
        if (!$url->isSuccess()) {
            return UploadPrepareRet::fromError($url->getError(), $md5);
        }
        return UploadPrepareRet::fromUrl($url, $md5);
    }

    /**
     * 上传准备（本地文件）：先算 md5 再查系统，命中秒传，未命中返回标准上传地址。
     *
     * 用法：
     *   $ret = $file->prepareUpload('/path/to/a.jpg', '', 'images');
     *   if ($ret->isSuccess() && $ret->hit) { echo $ret->url; }        // 秒传
     *   elseif ($ret->needUpload()) { ... POST $ret->upload_url ... } // 标准上传
     */
    public function prepareUpload(string $filePath, string $ip = '', string $bucket = ''): UploadPrepareRet
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return UploadPrepareRet::fromError('文件不存在或不可读: ' . $filePath);
        }
        $md5 = md5_file($filePath);
        if ($md5 === false) {
            return UploadPrepareRet::fromError('无法计算文件 MD5: ' . $filePath);
        }
        return $this->prepareUploadByMd5($md5, $ip, $bucket);
    }

    /**
     * 按 md5/hash 查询文件完整信息 POST /v3/file/token/hash_query
     *
     * @param int $ttl 可选，>0 时服务端把 file_url 改签为有效期 ttl 秒的 file 临时地址；
     *                 0/留空时 file_url 为 attachment 永久地址。上限由服务端 max_ttl 约束。
     */
    public function getUploadedFileUrlByHash(string $hash, int $ttl = 0): FileHashRet
    {
        $post = ['hash' => $hash];
        if ($ttl > 0) {
            $post['ttl'] = $ttl;
        }
        return new FileHashRet($this->call('/v3/file/token/hash_query', $post));
    }

    /**
     * 按 md5/hash 签发临时地址 POST /v3/file/token/temp_url（AOSSTP8 v2.8.0+）
     *
     * @param string $hash 文件 md5/hash（也可为 ao_attachment.id）
     * @param int $ttl 有效期秒数，1~服务端 max_ttl（默认 604800）；<=0 时不带该字段，用服务端默认值
     */
    public function getTempUrl(string $hash, int $ttl = 0): TempUrlRet
    {
        $post = ['hash' => $hash];
        if ($ttl > 0) {
            $post['ttl'] = $ttl;
        }
        return new TempUrlRet($this->call('/v3/file/token/temp_url', $post));
    }

    /** 软删：把文件移入服务端 upload/recycle/YYYYMMDD/ POST /v3/file/token/delete */
    public function delete(string $hash): FileActionRet
    {
        return new FileActionRet($this->call('/v3/file/token/delete', ['hash' => $hash]));
    }

    /** 恢复：从回收站移回原路径 POST /v3/file/token/restore */
    public function restore(string $hash): FileActionRet
    {
        return new FileActionRet($this->call('/v3/file/token/restore', ['hash' => $hash]));
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
