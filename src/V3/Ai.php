<?php

namespace Tobycroft\AossSdk\V3;

/**
 * V3 开放接口（AOSSTP8 /v3/ai/*）PHP 客户端
 *
 * 鉴权采用 V3 凭证信封（见 Envelope），一律通过请求头传递
 * X-Aoss-Ts / X-Aoss-Kid / X-Aoss-Auth，不使用 V1/V2 的 ?token= 明文方式。
 * 服务端虽兼容表单/查询串传参，本客户端只用新版请求头方式。
 *
 * 结构化字段（messages/tools/input/thinking/image 等）按服务端约定以
 * JSON 字符串提交；响应统一为 {code,data,echo}，code=0 时 data 为上游原样响应。
 */
class Ai
{
    private const DEFAULT_BASE_URL = 'https://aoss.tuuz.cc';

    /** 服务端签发的 64 位十六进制凭证，同时作为信封密钥 */
    protected string $secret;
    protected string $baseUrl;

    /**
     * @param string $secret 由 AOSSTP8 后台签发，请通过配置注入，勿硬编码
     * @param string $baseUrl AOSSTP8 站点根地址，空则取默认值
     */
    public function __construct(string $secret, string $baseUrl = '')
    {
        $this->secret = $secret;
        $this->baseUrl = rtrim($baseUrl !== '' ? $baseUrl : self::DEFAULT_BASE_URL, '/');
    }

    /**
     * 对话补全 POST /v3/ai/chat/completions
     *
     * options 支持 model/temperature/max_tokens/top_p/stream/tools/tool_choice/
     * chat_template_kwargs。开启推理（OpenAI 格式）：
     *   ['chat_template_kwargs' => ['enable_thinking' => true]]
     */
    public function chatCompletions(array $messages, array $options = []): AiRet
    {
        $post = ['messages' => self::jsonString($messages)];
        self::copyOptions($post, $options, ['model', 'temperature', 'max_tokens', 'top_p', 'stream', 'tool_choice']);
        self::copyJsonOptions($post, $options, ['tools', 'chat_template_kwargs']);
        return new AiRet($this->call('/v3/ai/chat/completions', $post));
    }

    /**
     * Responses API POST /v3/ai/responses/index
     *
     * $input 可为字符串或结构化消息；options 支持 model/instructions/temperature/
     * max_output_tokens/top_p/stream/chat_template_kwargs（推理开关同上）。
     */
    public function responses(mixed $input, array $options = []): AiRet
    {
        $post = ['input' => self::jsonString($input)];
        self::copyOptions($post, $options, ['model', 'instructions', 'temperature', 'max_output_tokens', 'top_p', 'stream']);
        self::copyJsonOptions($post, $options, ['chat_template_kwargs']);
        return new AiRet($this->call('/v3/ai/responses/index', $post));
    }

    /**
     * Messages API（Anthropic-compatible）POST /v3/ai/messages/index
     *
     * options 支持 model/system/temperature/max_tokens/top_p/stream/thinking/
     * chat_template_kwargs。开启推理（Anthropic 格式，注意与 OpenAI 格式不同）：
     *   ['thinking' => ['type' => 'enabled', 'budget_tokens' => 2048]]
     */
    public function messages(array $messages, array $options = []): AiRet
    {
        $post = ['messages' => self::jsonString($messages)];
        self::copyOptions($post, $options, ['model', 'system', 'temperature', 'max_tokens', 'top_p', 'stream']);
        self::copyJsonOptions($post, $options, ['thinking', 'chat_template_kwargs']);
        return new AiRet($this->call('/v3/ai/messages/index', $post));
    }

    /**
     * 图片生成 POST /v3/ai/images/generations
     *
     * options 支持 model/ratio/return_base64/response_format/image/extra_body。
     * 图片默认模型为 agnes-image-2.5-flash，与对话模型相互独立。
     */
    public function imageGenerations(string $prompt, string $size, array $options = []): AiRet
    {
        $post = ['prompt' => $prompt, 'size' => $size];
        self::copyOptions($post, $options, ['model', 'ratio', 'return_base64', 'response_format']);
        self::copyJsonOptions($post, $options, ['image', 'extra_body']);
        return new AiRet($this->call('/v3/ai/images/generations', $post));
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
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
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

    /** 结构化字段编码为 JSON 字符串后写入表单 */
    private static function copyJsonOptions(array &$post, array $options, array $keys): void
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $post[$key] = self::jsonString($options[$key]);
            }
        }
    }
}
