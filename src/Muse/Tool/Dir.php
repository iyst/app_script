<?php
namespace Muse\Tool;
use Exception;

class Dir
{
    private $path;
    public function __construct($path = '')
    {
        $this->path = !$path ? env('WORK_PATH') : $path;
    }
    /**
     * 格式化源文件
     * @return mixed
     */
    public function formatFile()
    {
        $dir  = $this->readFile();
        foreach ( $dir as $d ) {
            $cPath      = $this->path.'/'.$d;
            if(!is_dir( $cPath )) continue;
            //读取子目录
            $childDir   = $this->readFile($cPath);$child  = [];
            foreach ($childDir as $c) {
                $child[$c] = [];
                //读取文件
                $file   = $this->readFile($cPath.'/'.$c,config('song_source_ext'));
                if(!$file) continue;
                $tmp    = [];
                foreach ($file as  $f) {
                    $f  = handleFileName($f,true);$tmp[$f['sort']]  = $f['name'];
                }
                ksort($tmp);$child[$c] = $tmp;
            }
            $format[$d] = $child;
        }
        return $format;
    }
    /**
     * 格式化源文件
     * @return mixed
     */
    public function formatFileCopy($rootPath = '',$ext = [])
    {
        $dir  = $this->readFile($rootPath);
        $ext  = (is_array($ext) && $ext) ? $ext : config('song_source_ext');
        foreach ( $dir as $d ) {
            $cPath      = $this->path.'/'.$d;
            if(!is_dir( $cPath )) continue;
            //读取子目录
            $childDir   = $this->readFile($cPath);$child  = [];
            foreach ($childDir as $c) {
                $child[$c] = [];
                //读取文件
                $file   = $this->readFile($cPath.'/'.$c,$ext);
                var_dump($cPath.'/'.$c);
                if(!$file) continue;
                $tmp    = [];
                foreach ($file as  $k=>$f) {
                    $tmp[$k] = $f;
                }
                $child[$c] = $tmp;
            }
            $format[$d] = $child;
        }
        return $format;
    }
    /**
     * 读取文件和目录
     * @param string $path 路径
     * @param array $filter 过滤格式
     * @return array
     * @throws Exception
     */
    public function readFile( $path = '',$filter = [])
    {
        $path = !$path ? $this->path : $path;
        if(!is_dir( $path )) throw new Exception('目录不存在');
        $handle = opendir( $path );
        while ( ($dir = readdir($handle)) != false ){
            if($dir == '..' || $dir =='.' || !$dir) continue;
            $index      = strpos($dir,'.');
            if($index == 0 && gettype($index) == 'integer') continue;
            if($filter) {
                if($this->checkFileExt($path.'/'.$dir,$filter)) $file[] = $dir;
            }else{
                $file[] = $dir;
            }
        }
        return $file;
    }
    /**
     * 验证文件后缀名称
     * @param $fileName
     * @param $ext
     * @return bool|void
     */
    public function checkFileExt($fileName,$ext)
    {
        return in_array(substr($fileName,strrpos($fileName,'.')+1,strlen($fileName)),$ext) ? true :false;
    }
}