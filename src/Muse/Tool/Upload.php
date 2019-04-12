<?php
namespace Muse\Tool;
use OSS\OssClient;

class Upload
{
    private $ossObject;
    public function __construct()
    {
        $this->ossObject = new OssClient(
            config('OSS.ACCESS_KEY_ID'),
            config('OSS.ACCESS_KEY_SECRET'),
            config('OSS.ENDPOINT')
        );
    }

    public function uploadOss($file,$object)
    {
        $fileData = $this->ossObject->uploadFile(config('OSS.BUCKET'),$object,$file);
        return $fileData['info']['url'];
    }

}