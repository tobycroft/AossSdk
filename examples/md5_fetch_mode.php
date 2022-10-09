<?php

use Tobycroft\AossSdk\Aoss;

/*
 * your_token can be get by sending request email
 * complete mode can gave your the datas as much as possible with video/audio analyze
 */
$Aoss = new Aoss("your_token", "complete");
$md5_data = $Aoss->md5("md5 sign here");
if (empty($md5_data->error)) {
    $file_info = [
        'uid' => session('user_auth.uid'),
        'name' => $md5_data->name,
        'mime' => $md5_data->mime,
        'path' => $md5_data->url,
        'ext' => $md5_data->ext,
        'size' => $md5_data->size,
        'md5' => $md5_data->md5,
        'sha1' => $md5_data->sha1,
        'thumb' => "",
        'module' => "remote",
        'width' => $md5_data->width,
        'height' => $md5_data->height,
        'driver' => "remote",
    ];
    // 写入数据库
    if (AttachmentModel::create($file_info)) {
        $data = [
            'code' => 1,
            'info' => '同步成功',
            'class' => 'success',
            'id' => $md5_data->url,
            'path' => $md5_data->url,
            'data' => $md5_data->data,
        ];
        return json($data);
    } else {
        $this->error('文件同步失败');
    }
} else {
    $this->error('需要上传文件');
}