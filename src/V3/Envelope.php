<?php

namespace Tobycroft\AossSdk\V3;

/**
 * V3 开放接口凭证信封（AES-256-GCM）
 *
 * 与 AOSSTP8 服务端 extend/Aoss/Envelope.php 严格对齐：
 *   kid  = substr(sha256(secret), 0, 8)
 *   key  = hkdf_sha256(secret, 32, 'aoss.v3.envelope')
 *   auth = 'v1.' . base64url(iv[12] ‖ ciphertext ‖ tag[16])
 *   plaintext = secret . '|' . ts，AAD = 'v1'
 *
 * secret 本身即共享密钥，仅参与本地派生/加密，绝不明文传输。
 * PHP hash_hkdf 在 salt 省略时等价于全零 salt（RFC 5869），与服务端一致。
 */
class Envelope
{
    public const VERSION = 'v1';

    private const KDF_INFO = 'aoss.v3.envelope';
    private const IV_LEN = 12;
    private const TAG_LEN = 16;

    /** 由 secret 派生 8 位小写十六进制定位标识 */
    public static function kid(string $secret): string
    {
        return substr(hash('sha256', $secret), 0, 8);
    }

    /** 由 secret 派生 32 字节 AES-256 密钥 */
    public static function deriveKey(string $secret): string
    {
        return hash_hkdf('sha256', $secret, 32, self::KDF_INFO);
    }

    /**
     * 生成凭证信封，形如 v1.xxxxx；失败返回空串。
     * @param string $secret 凭证，同时作为加密密钥
     * @param string|int $ts 秒级 Unix 时间戳
     */
    public static function seal(string $secret, string|int $ts): string
    {
        if ($secret === '') {
            return '';
        }
        $iv = random_bytes(self::IV_LEN);
        $tag = '';
        $cipher = openssl_encrypt(
            $secret . '|' . (string)$ts,
            'aes-256-gcm',
            self::deriveKey($secret),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::VERSION,
            self::TAG_LEN
        );
        if ($cipher === false || strlen($tag) !== self::TAG_LEN) {
            return '';
        }
        return self::VERSION . '.' . rtrim(strtr(base64_encode($iv . $cipher . $tag), '+/', '-_'), '=');
    }
}
