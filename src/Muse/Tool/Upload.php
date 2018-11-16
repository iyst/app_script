<?php
namespace Muse\Tool;
use OSS\OssClient;

class Upload
{
    public static function uploadOss($file,$object)
    {
        $oss = new OssClient(
            config('OSS.ACCESS_KEY_ID'),
            config('OSS.ACCESS_KEY_SECRET'),
            config('OSS.ENDPOINT')
        );
        $fileData = $oss->uploadFile(config('OSS.BUCKET'),$object,$file);
        return $fileData['info']['url'];
    }

}