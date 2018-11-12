<?php
namespace Muse\Service;
use Muse\Tool\Dir;
use Muse\Service\DbService;
use Muse\Tool\Log;

class ProssSongService
{

    private $dir;
    private $dbService;
    private $muScoreFile;
    //目录是否错误
    private $isDirErr = false;
    //错误个数
    private $isDirErrNum =0;

    public function __construct()
    {
        $this->dir = new Dir();
        $this->dbService    = new DbService();
        $this->outLine('初始化数据');
        $this->muScoreFile  = $this->dir->formatFile();
        $this->outLine('初始化数据 ok');
    }

    /**
     * 开始
     */
    public function start()
    {
        $params = getCmdParams(config('SCRIPT_OPTIONS'));

        $this->outLine("检查目录..");

        Log::delLog(LOG_DIR);
        //检查目录
       // $this->dataFactory(true);

        if( $this->isDirErr )
        {
            $this->outLine('目录信息错误,检查目录错误个数 '.$this->isDirErrNum.',请修改目录后 继续..');
            exit;
        }

        //导出文件
        $this->dataFactory(false,['export' => true,'params'=>$params]);


    }




    private function dataFactory($checkCate = false,$params = [])
    {
        foreach ($this->muScoreFile as $k => $cate)
        {
            //是否要检查目录
            if($checkCate)
            {

                if( !($pData =  $this->dbService->checkCategory(['p'=>$k])) ) {
                    $this->isDirErr = true;$this->isDirErrNum++;
                    Log::writeLog(LOG_DIR,$k.','.ERR_DIR_EXISTE);
                    continue;
                }
            }

            foreach ($cate as $ck => $child) {
                $dir  = $k.DIRECTORY_SEPARATOR.$ck;
                //check
                if ( $checkCate )
                {
                    if(!($childData =  $this->dbService->checkCategory(['pcid'=> $pData['id'],'c'=> $ck])))
                    {
                        $this->isDirErr = true;
                        $this->isDirErrNum++;
                        Log::writeLog(LOG_DIR,$dir.','.ERR_DIR_EXISTE);
                        continue;
                    }
                    //子目录没有文件
                    if(!$child){
                        $this->isDirErr = true;
                        $this->isDirErrNum++;
                        Log::writeLog(LOG_DIR,$dir.','.ERR_DIR_EMPTY);
                        continue;
                    }
                }

                if( $params['export'] )
                {
                    foreach ($child as $fk => $c)
                    {
                        $this->export($dir,$fk,$c,$params['params']);
                    }
                }


            }
        }
    }

    public function export($dir,$sort,$file,$params)
    {
        $p = $params[S_OPTION_SCREEN];

        //根目录
        $rootPath = WORK_PATH.'/';

        $exportCmd  = $p == S_SCREEN_H ? config('CMD.MU_HOR_COMMAND') : config('CMD.MU_VER_COMMAND');
        //
        $files = explode('.',$file);
        $fileDir   = $rootPath.$dir.'/'.$files[0];

        createDir($fileDir);

        $posPath = '"'.$fileDir.'/'.getExportName($dir,$file).'.pos'.'"';
        $filePath = '"'.WORK_PATH.'/'.$dir.'/'.$sort.' '.$file.'"';

        $cmd = $exportCmd .' '.$filePath .' -o '.$posPath;
        $this->outLine('CMD:'.$cmd);
        system($cmd);
    }

    private function savePonitData($p,$c,$files)
    {
        foreach ($files as $f ) {
            Log::writeLog(LOG_POINT,$p.'/'.$c.'/'.$f);
        }

    }

    private function outLine($out)
    {
        echo $out."\n";
       // Log::system($out);
    }
}