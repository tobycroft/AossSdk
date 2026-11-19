<?php

namespace Tobycroft\AossSdk\Excel;


use Tobycroft\AossSdk\Aoss;

/**
 * v1 Excel 接口客户端（?token= 明文鉴权）
 *
 * @deprecated 建议迁移到 V3 信封鉴权版本 \Tobycroft\AossSdk\V3\Excel：
 *             同等功能（send_excel / send_md5 / create_excel_download_directly /
 *             create_excel_fileurl），Secret 不走 URL 明文传输，凭证由后台
 *             「项目管理 v3」签发。本类暂保留可用，后续版本将正式移除。
 */
class Excel extends Aoss
{
    protected string $send_path = "/v1/excel";

    /**
     * @deprecated 请改用 \Tobycroft\AossSdk\V3\Excel::__construct
     */
    public function __construct($token)
    {
        $this->token = $token;

        $this->send_url = $this->remote_url;
    }

    public function buildUrl($wechatRouter)
    {
        $this->send_path = $wechatRouter;

        $this->send_url = $this->remote_url;
        $this->send_url .= $this->send_path;
        $this->send_url .= $this->send_token . $this->token;
    }

    /**
     * @deprecated 请改用 \Tobycroft\AossSdk\V3\Excel::send_excel
     */
    public function send_excel($real_path, $mime_type, $file_name): ExcelCompleteRet
    {
        $this->send_url .= $this->send_path . '/index/dp';
        $this->send_url .= $this->send_token . $this->token;
        $response = self::curl_send_file($real_path, $mime_type, $file_name, $this->send_url);
        return new ExcelCompleteRet($response);
    }

    /**
     * @deprecated 请改用 \Tobycroft\AossSdk\V3\Excel::send_md5
     */
    public function send_md5($md5): ExcelCompleteRet
    {
        $this->send_url .= $this->send_path . '/search/md5';
        $this->send_url .= $this->send_token . $this->token;
        $response = self::raw_post($this->send_url, ["md5" => $md5]);
        return new ExcelCompleteRet($response);
    }

    /**
     * @deprecated 请改用 \Tobycroft\AossSdk\V3\Excel::create_excel_download_directly
     */
    public function create_excel_download_directly(array $data)
    {
        $this->send_url .= $this->send_path . '/index/create';
        $this->send_url .= $this->send_token . $this->token;
        $response = self::raw_post($this->send_url, ['data' => json_encode($data, 320)]);
        return $response;
    }

    /**
     * @deprecated 请改用 \Tobycroft\AossSdk\V3\Excel::create_excel_fileurl
     */
    public function create_excel_fileurl(array $data): ExcelCreateRet
    {
        $this->send_url .= $this->send_path . '/index/create_file';
        $this->send_url .= $this->send_token . $this->token;
        $response = self::raw_post($this->send_url, ['data' => json_encode($data, 320)]);
        return new ExcelCreateRet($response);
    }
}