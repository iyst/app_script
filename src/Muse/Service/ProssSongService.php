<?php
namespace Muse\Service;
use Muse\Tool\Dir;
use Muse\Service\DbService;
use Muse\Tool\Log;
use Muse\Tool\Xml;
use Muse\Tool\Upload;
use Exception;

class ProssSongService
{

    private $dir;
    private $dbService;
    private $muScoreFile;
    //目录是否错误
    private $isDirErr = false;
    //错误个数
    private $isDirErrNum =0;
    private $sortData;

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
            if($checkCate) {
                if( !($pData =  $this->dbService->checkCategory(['p'=>$k])) ) {
                    $this->isDirErr = true;$this->isDirErrNum++;
                    Log::writeLog(LOG_DIR,$k.','.ERR_DIR_EXISTE);
                    continue;
                }
            }
            foreach ($cate as $ck => $child) {
                $dir  = $k.DIRECTORY_SEPARATOR.$ck;
                //check
                if ( $checkCate ) {
                    if(!($childData =  $this->dbService->checkCategory(['pcid'=> $pData['id'],'c'=> $ck]))) {
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
                //导出文件
                if( $params['export'] ) {
                    foreach ($child as $sort => $fName)
                    {
                        $this->export(getExportName($dir,$sort,$fName),$params['params']);
                    }
                }
            }
        }
    }

    /**
     * @param $pathData
     * @param $params
     * @throws \Exception
     */
    public function export($pathData,$params)
    {
        $p          = $params[S_OPTION_SCREEN];
        $exportCmd  = $p == S_SCREEN_H ? config('CMD.MU_HOR_COMMAND')
            : config('CMD.MU_VER_COMMAND');

        $fileDir = $pathData['dir'].$pathData['fileName'];

        //生成目录
        createDir($fileDir);
        $posPath    = '"'.$fileDir.'/'.$pathData['fullName'].'.pos'.'"';
        $filePath   = '"'.$pathData['dir'].$pathData['sort'].' '.$pathData['fileName'].$pathData['ext'].'"';
        $cmd        = $exportCmd .' '.$filePath .' -o '.$posPath;

        $this->outLine('CMD:'.$cmd);
        system($cmd);

        //拷贝缺失的mid
        copyLRMid($fileDir,$pathData['fullName']);
        $data = [
            'svg' => $fileDir.DIRECTORY_SEPARATOR.$pathData['fullName'].'.svg',
            'pos' => $fileDir.DIRECTORY_SEPARATOR.$pathData['fullName'].'.pos',
        ];
        //解析svg 和pos文件
        $this->createPlist($fileDir,$pathData['fullName'],$data);

        //生成安卓数据
        if( $p == S_SCREEN_H)
        {
            if(config('CREATE_ANDROID_DATA'))
            {
                $this->createAndroidData($pathData['dir'],$data);
            }
        }

        if ($params[S_OPTION_ZIP] == S_ZIP_ON  && !isset( $params[S_OPTION_ZIP]))
        {
            $this->outLine($pathData['fileName'].'开始压包..',true);

            //压包
            $zip = compressZip($pathData['dir'],$pathData['dir'].'/'.$pathData['fileName']);

            if($zip['name'])
            {
                $this->outLine($pathData['fullName'].'压包成功..',true);
            }
        }

        //上传到oss

        if ($params[S_OPTION_UPLOAD] == S_UPLOAD_ON && $zip['name'])
        {
            $dir = explode('/',$pathData['fileDir']);
            if(!$dir[0] || $dir[1]) throw new Exception('目录错误');

            $object =  config('OSS_DIR').'/'.$dir[0].'/'.$dir[1].'/'.$zip['code'].'.zip';
            $url    =  Upload::uploadOss($zip['name'],$object);

            if($url)
            {
                //更新到数据库
                $this->updateDb($pathData,$params,$url,$dir[1],'');
            }
        }

    }

    /**
     * @param $path
     * @param $params
     * @param $url
     * @param $childName
     * @param string $androidUrl
     */
    private function updateDb($path,$params,$url,$childName,$androidUrl = '')
    {
        //添加
        if( $params[S_OPTION_METHOD] == S_METHOD_ADD  && $url)
        {
            $category   =  $this->dbService->findCategory("chinese_name='".$childName."'",'id');
            $insertId   =  $this->dbService->insertSong([
                    'cid'   =>  $category['id'],
                    'chinese_name' => $path['fileName'],
                    'sort' => 1
                ]);
            $this->dbService->insertSongRc($params[S_OPTION_SCREEN],[
                'song_id' => $insertId,
                'horscreen_url' => $url,
                'h_android_url' => $androidUrl,
            ]);

            $this->outLine('添加成功');
        }

        if( $params[S_OPTION_METHOD] == S_METHOD_UPDATE  && $url)
        {
            $songId = 0;
            $this->dbService->updateSongRc($params[S_OPTION_SCREEN],[
                'song_id' => $songId,
                'url' => $url,
                'h_android_url' => $androidUrl,
            ]);
            $this->outLine('更新成功');
        }
    }


    /**
     * @param $path
     * @param $fullName
     * @param $data
     */
    private function createPlist($path,$fullName,$data)
    {
        if(file_exists($data['svg']))
        {
            if( $content = file_get_contents($data['svg']) )
            {
                //去除颜色
                if(config('IS_CHANGE_SVG_COLOR'))
                {
                    if( Xml::checkSvgColor($content) )
                    {
                        write($data['svg'],Xml::replaceSvg($data));
                    }
                }
                write($path.DIRECTORY_SEPARATOR.$fullName.'.plist', Xml::svg($content));
            }
           removeFile($data['svg']);
        }

        if(file_exists($data['pos']))
        {
            write($path.DIRECTORY_SEPARATOR.$fullName.'_pos.plist', Xml::pos(file_get_contents($data['pos'])));
           removeFile($data['pos']);
        }
        $this->outLine($data['pos'].'处理完成',true);

    }

//    public function createAndroidData($path,$data)
//    {
//
//    }

    private function savePonitData($p,$c,$files)
    {
        foreach ($files as $f ) {
            Log::writeLog(LOG_POINT,$p.'/'.$c.'/'.$f);
        }

    }

    /**
     * print
     * @param $out
     * @param bool $isSystem
     */
    private function outLine($out,$isSystem = false)
    {
        echo $out."\n";
        if($isSystem) Log::system($out);
    }
}