<?php
namespace Muse\Tool;

class Log
{
    /**
     * @param $name
     * @return string
     */
    private static function init($name)
    {
        return LOG_PATH.DIRECTORY_SEPARATOR.$name.'.'.config('log.ext');
    }

    /**
     * @param $name
     * @param $data
     */
    public static function writeLog($name,$data)
    {
        write(self::init($name),$data.',  :  '.date('Y-m-d H:i:s',time())."\n");
        if($name != LOG_POINT) self::system($data);
    }

    /**
     * @param $name
     */
    public static function delLog($name)
    {
        $path  = self::init($name);
        if( file_exists($path) ) unlink($path);
    }
    /**
     * @param $data
     */
    public static function system($data)
    {
        $date       = date('Ymd',time());
        $file       = LOG_PATH.DIRECTORY_SEPARATOR.LOG_SYSTEM.'_'.$date.'.'.config('log.ext');
        $outLine    = $data.'  : '.date('Y-m-d H:i:s',time())."\n";
        write($file,$outLine);
    }
}