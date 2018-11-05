<?php
function env($key){
    if(!$key) return null;
    return getenv($key);
}

/**
 * @param $key
 * @return mixed|null
 */
function config($key)
{
    global $conf;
    $key = strtoupper($key);
    if(!strstr($key,'.')) {
        foreach ($conf as $item) {
            return !$item[$key] ? null : str_replace(' ','\ ',$item[$key]);
        }
    }
    $confParam = explode('.',$key);
    if(count($confParam)<=1) return null;
    if(!$conf[$confParam[0]] || !( $val = $conf[$confParam[0]][$confParam[1]])) return null;
    return $val;
}