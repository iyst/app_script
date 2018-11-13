<?php
namespace Muse\Tool;
use OSS\OssClient;

class Upload
{
    public static function uploadOss($file,$object)
    {
        $oss = new OssClient(
            config('ACCESS_KEY_ID'),
            config('ACCESS_KEY_SECRET'),
            config('ENDPOINT')
        );
        $fileData = $oss->uploadFile(config('BUCKET'),$object,$file);
        return $fileData['info']['url'];
    }

}