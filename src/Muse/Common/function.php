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

function getExportName($dir,$name)
{
    $dirs = explode('/',$dir);
    $name = substr($name,0,strpos($name,'.'));
    if(!$name || count($dirs) == 1) return null;
    return implode('_',$dirs).'_'.$name;
}
function createDir($dir)
{
    if(!is_dir($dir)) mkdir($dir);
}

function read($file)
{
    $handle = fopen($file,'r');
}