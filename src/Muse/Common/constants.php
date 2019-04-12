<?php
//命令行参数配置
const S_OPTION_DIR = 'd';
const S_OPTION_METHOD ='m';
const S_OPTION_SCREEN = 'e';
const S_OPTION_SONG_FILE = 's';
const S_OPTION_DIR_FILE = 'r';
const S_OPTION_POINT = 'p';
const S_OPTION_UPLOAD = 'u';
const S_OPTION_ZIP = 'z';
const S_OPTION_C = 'c';

//命令行参数值
const S_C_ON = 1;
const S_C_OFF = 0;
//命令行参数值
const S_POINT_ON = 1;
const S_POINT_OFF = 0;
//屏幕
const S_SCREEN_V = 1; //竖屏
const S_SCREEN_H = 2; //横屏

const S_METHOD_ADD = 'add';
const S_METHOD_UPDATE= 'update';
//上传
const S_UPLOAD_ON = 1;
const S_UPLOAD_OFF = 0;
//zip
const S_ZIP_ON = 1;
const S_ZIP_OFF = 0;

//错误类型
const ERR_DIR_EMPTY  = '目录为空';
const ERR_DIR_EXISTE = '目录不存在';
const ERR_SONG_NAME  = '歌曲名称错误';
const ERR_SONG_EXPORT = '歌曲导出失败';
const ERR_UPLOAD_TIMEOUT = '歌曲上传失败';
const ERR_APP_ERROR = '程序错误';

const LOG_SYSTEM = 'system';
const LOG_SONG = 'song';
const LOG_DIR = 'dir';
const LOG_POINT = 'point';





