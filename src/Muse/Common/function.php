<?php

/**
 * 处理文件名称
 * @param $name
 * @param bool $isExt
 * @return array|null
 */
function handleFileName($name,$isExt = true)
{
    if ( !$name ) return null;
    $firstIndex = strpos($name , ' ');
    $fileName   = explode(' ',substr($name,0,strrpos($name,'.')));
    $ext        = substr($name,strrpos($name,'.'),strlen($name));
    if(count($fileName)>1 )
    {
        $name_str ='';
        for($i=1;$i<=count($fileName)-1;$i++){
            $name_str .= $fileName[$i];
        }
        $fileName = trim($name_str);
    }else{
        $fileName = trim($fileName[0]);
    }
    $fileName = $isExt ? $fileName.$ext : $fileName;
    return ['sort' => substr( $name ,0,$firstIndex),'name' => $fileName];
}
/**
 * 获取env配置方法
 * @param $key
 * @return array|false|null|string
 */
function env($key){
    if(!$key) return null;
    return getenv($key);
}
/**
 * 获取配置文件方法
 * @param $key 不区分大小写 例如 db.host 也可以 host
 * @return mixed|null
 */
function config($key)
{
    global $conf;$key = strtoupper($key);
    if(!strstr($key,'.')) {
        if($conf[$key]) return str_replace(' ','\ ',$conf[$key]);
        foreach ($conf as $item) {
            return !$item[$key] ? null : str_replace(' ','\ ',$item[$key]);
        }
    }
    $confParam = explode('.',$key);if(count($confParam)<=1) return null;
    if(!$conf[$confParam[0]] || !( $val = str_replace(' ','\ ',$conf[$confParam[0]][$confParam[1]]))) return null;
    return $val;
}

/**
 * @param $key
 * @return array
 */
function getCmdParams($key)
{
    return getopt(implode(':',$key).':');
}

/**
 * @param $file
 * @param $data
 */
function write($file,$data)
{
    $handle = fopen($file,'a+');
    fwrite($handle,$data);
    fclose($handle);
}
function removeFile($path)
{
    if(file_exists($path)) unlink($path);
}

/**
 * 返回导出文件名称
 * @param $dir
 * @param $sort
 * @param $name
 * @return array|bool
 */
function getExportName($dir,$sort,$name)
{
    //根目录
    $rootPath   = WORK_PATH.'/';

    if(!$dir || !$name) return false;
    $dirs = explode('/',$dir);

    if(count($dirs) == 1) return false;

    $fileName = substr($name,0,strpos($name,'.'));
    if( !$fileName ) return false;
    $ext      = substr($name,strpos($name,'.'),strlen($name));
    $ret =  [
        'dir' => $rootPath.$dir.DIRECTORY_SEPARATOR,
        'fullName' =>  implode('_',$dirs).'_'.$fileName,
        'fileName' => $fileName,
        'ext'   =>  $ext,
        'sort'  => $sort,
        'fileDir' => $dir,
        ];;
    return $ret;
}

/**
 * 创建目录
 * @param $dir
 */
function createDir($dir)
{
    if(!is_dir($dir)) mkdir($dir);
}

/**
 * 创建一个唯一的id号
 * @return string
 */
function createCode()
{
    return substr(md5(mt_rand(10000, 99999)),0,7).'-'.substr(md5(mt_rand(10000, 99999)),0,7).'-'.substr(md5(mt_rand(10000, 99999)),0,7);
}

/**
 * @param $path
 * @param $fullName
 */
function copyLRMid($path,$fullName)
{
    $left   = $path.DIRECTORY_SEPARATOR.$fullName.'_left.mid';
    $right  = $path.DIRECTORY_SEPARATOR.$fullName.'_right.mid';
    $mid    = $path.DIRECTORY_SEPARATOR.$fullName.'.mid';
    if(!file_exists($left) && !file_exists($right)){
        copy($mid,$left);
        copy($mid,$right);
    }
}

/**
 * @param $rootPath
 * @param $filePath
 * @return array
 */
function compressZip($rootPath,$filePath)
{
    $zip        = new ZipArchive();
    $code       = createCode();
    $zipName    = $rootPath.DIRECTORY_SEPARATOR.$code.'.zip';
    if ( $zip->open($zipName, ZIPARCHIVE::CREATE) )
    {
        $handle = opendir($filePath);
        while (($filename = readdir($handle)) !== false)
        {
            if ($filename != "." && $filename != "..")
            {
                $zip->addFile($filePath .DIRECTORY_SEPARATOR.$filename, $filename);
            }
        }
        @closedir($handle);
    }
    $zip->close();
    return ['name' =>$zipName,'code'=>$code];
}

function clearExt($name)
{
    $name = str_replace('.mscx','',$name);
    $name = str_replace('.mscz','',$name);
    return $name;
}

/**
 * @param $dirName
 * @return bool
 */
function removeDir($dirName)
{
    if(!is_dir($dirName))
    {
        return false;
    }
    $handle = @opendir($dirName);
    while(($file = @readdir($handle)) !== false)
    {
        if($file != '.' && $file != '..')
        {
            $dir = $dirName . '/' . $file;
            is_dir($dir) ? remove_dir($dir) : @unlink($dir);
        }
    }
    closedir($handle);
    return rmdir($dirName) ;
}