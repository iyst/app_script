<?php
return [
    'DB'=>[
        'HOST'      => env('DB_HOST'),
        'DBNAME'    => env('DB_NAME'),
        'USER'      => env('DB_USER'),
        'PASSWD' => env('DB_PASSWD'),
        'PORT'   => env('DB_PORT'),
        'PREFIX' => env('DB_PREFIX')
    ],
    'LOG' => [

    ],
    'CMD' => [
        'MU_HOR_COMMAND'  => env('MU_HOR_COMMAND'),
        'MU_VER_COMMAND' => env('MU_VER_COMMAND')
    ],
    'WORK_PATH'=>'/Users/iyst/Downloads/song',

    'SONG_SOURCE_EXT' => [
        'mscx','mscz'
    ],
    'OSS' =>[
        'ACCESS_KEY_ID' => env('OSS_ACCESS_KEY_ID'),
        'ACCESS_KEY_SECRET' => env('OSS_ACCESS_KEY_SECRET'),
        'ENDPOINT'  => env('OSS_ENDPOINT'),
        'BUCKET'    => env('OSS_BUCKET'),
    ],
];