<?php

use Tobycroft\AossSdk\Aoss;

/*
 * your_token can be get by sending request email
 * complete mode can gave your the datas as much as possible with video/audio analyze
 *
 * this example gave you a way of send files
 * 1.you can directly send the file to AOSS then retrieve the attach url finally send it to your own server.
 * 2.you can let the front-end sending the file to AOSS then let FE gave you the MD5 hash code which returns by AOSS, and then use this function to retrieve the truly url in Back-End,
 *   this can helps you to defend the XSS attack.
 */

$Aoss = new Aoss("your_token", "complete");
$md5_data = $Aoss->md5("md5 sign here");
if ($md5_data->isSuccess()) {
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
    if (Model::create($file_info)) {
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