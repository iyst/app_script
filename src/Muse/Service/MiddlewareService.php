<?php
namespace Muse\Service;

class MiddlewareService
{

    public static function checkCmd()
    {
        $params = getCmdParams(config('SCRIPT_OPTIONS'));
        if( isset($params[S_OPTION_DIR])){
            if($params[S_OPTION_DIR] == '' ) self::outPut('请输入目录地址');
            if(!is_dir(WORK_PATH.'/'.$params[S_OPTION_DIR])) self::outPut('目录地址不存在');
        }

        if( isset($params[S_OPTION_METHOD]) ){
            if( $params[S_OPTION_METHOD] == '') self::outPut('请输入要执行的方法');
            if( !in_array($params[S_OPTION_METHOD],[S_METHOD_UPDATE,S_METHOD_ADD]) )
                self::outPut('方法参数错误');
        }else{
            $params[S_OPTION_METHOD] = S_METHOD_UPDATE;
        }

        if( isset($params[S_OPTION_SCREEN] )){
            if( $params[S_OPTION_SCREEN] == '') self::outPut('请输入屏幕参数');
            if( !in_array($params[S_OPTION_SCREEN],[S_SCREEN_V,S_SCREEN_H]) )
                self::outPut('屏幕参数错误');
        } else{
            $params[S_OPTION_SCREEN] = S_SCREEN_V;
        }

        if( isset($params[S_OPTION_SONG_FILE]) ) {
            if( $params[S_OPTION_SONG_FILE] == '') self::outPut('请输入错误歌曲文件地址');
            if( !file_exists($params[S_OPTION_SONG_FILE]) ) self::outPut('错误歌曲文件不存在');
        }

        if( isset($params[S_OPTION_DIR_FILE]) ) {
            if ($params[S_OPTION_DIR_FILE] == '') self::outPut('请输入错误目录文件地址');
            if (!file_exists($params[S_OPTION_DIR_FILE])) self::outPut('错误目录文件不存在');
        }

        if(isset($params[S_OPTION_POINT])) {
            if ($params[S_OPTION_POINT] == '') $params[S_OPTION_POINT] = S_POINT_OFF;
            if (!in_array($params[S_OPTION_POINT],[S_POINT_OFF,S_POINT_ON]))
                self::outPut('断点参数错误');
        }
        if(isset($params[S_OPTION_UPLOAD])) {
            if ($params[S_OPTION_UPLOAD] == '') $params[S_OPTION_UPLOAD] = S_UPLOAD_ON;
            if (!in_array($params[S_OPTION_UPLOAD],[S_UPLOAD_OFF,S_UPLOAD_ON]))
                self::outPut('是否上传参数错误');
        }
        if(isset($params[S_OPTION_ZIP])) {
            if ($params[S_OPTION_ZIP] == '') $params[S_OPTION_ZIP] = S_ZIP_ON;
            if (!in_array($params[S_OPTION_ZIP],[S_ZIP_OFF,S_ZIP_ON]))
                self::outPut('压缩参数错误');
        }
        if(isset($params[S_OPTION_C])) {
            if ($params[S_OPTION_C] == '') $params[S_OPTION_ZIP] = S_C_ON;
            if (!in_array($params[S_OPTION_C],[S_C_OFF,S_C_ON]))
                self::outPut('压缩参数错误');
        }
        return $params;
    }

    private static function outPut($msg)
    {
        echo $msg."\n";
        exit;
    }
}