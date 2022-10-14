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

$md5_data = $Aoss->md5("your_file_md5_here");
if (empty($md5_data->isSuccess())) {
    if ($file_exists = Model::get(['md5' => "your_file_md5_here"])) {
        return "already_uploaded";
    }
}
$send_ret = $Aoss->send("your_realpath_to_the_file", "mime_of_the_file", "fileName_like_xxx.jpg");
if (($send_ret->isSuccess())) {
    echo $send_ret->getError();
    exit();
}
var_dump($send_ret->url, $send_ret->name, $send_ret->md5);

