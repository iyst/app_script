<?php
return [
    //数据库配置
    'DB'=>[
        'HOST'      => env('DB_HOST'),
        'DBNAME'    => env('DB_NAME'),
        'USER'      => env('DB_USER'),
        'PASSWD'    => env('DB_PASSWD'),
        'PORT'      => env('DB_PORT'),
        'PREFIX'    => env('DB_PREFIX')
    ],
    //日志配置
    'LOG' => [
        'EXT'   => 'log',
    ],
    //命令配置
    'CMD' => [
        'MU_HOR_COMMAND'  => env('MU_HOR_COMMAND'),
        'MU_VER_COMMAND' => env('MU_VER_COMMAND')
    ],
    //后缀名称
    'SONG_SOURCE_EXT' => [
        'mscx','mscz'
    ],

    /**
     * ------参数配置-------
     * d       string 要上传的目录 deme/或者 demos/demo1
     * m    string 方法 add 新增 update 修改
     * e    int 1 竖屏 2 横屏
     * s string 错误歌曲文件路径
     * r  string 错误目录文件路径
     * u    int 是否上传 0 不上传 1 上传
     * z       int  0 不压缩 1 压缩
     * p    int  0 不开启 1 开启断点续传
     * -----------------------
     */
    'OSS_DIR' => 'score_v6',
    'SCRIPT_OPTIONS' => ['d','m','e','p','s','r','u','z'],
    'CREATE_ANDROID_DATA'=> 0,
    'IS_CHANGE_SVG_COLOR' => 1,
    'OSS' =>[
        'ACCESS_KEY_ID' => env('OSS_ACCESS_KEY_ID'),
        'ACCESS_KEY_SECRET' => env('OSS_ACCESS_KEY_SECRET'),
        'ENDPOINT'  => env('OSS_ENDPOINT'),
        'BUCKET'    => env('OSS_BUCKET'),
    ],
];