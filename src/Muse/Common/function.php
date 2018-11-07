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
    $lastIndex  = $isExt ? strlen($name) : strrpos($name,'.')-2;
    return ['sort' => (int)substr( $name ,0,$firstIndex),'name' => trim(str_replace(' ','',substr($name,$firstIndex,$lastIndex)))];
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
        if($conf[$key]) return $conf[$key];
        foreach ($conf as $item) {
            return !$item[$key] ? null : str_replace(' ','\ ',$item[$key]);
        }
    }
    $confParam = explode('.',$key);if(count($confParam)<=1) return null;
    if(!$conf[$confParam[0]] || !( $val = $conf[$confParam[0]][$confParam[1]])) return null;
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
