<?php
namespace Muse\Service;
use Muse\Tool\Dir;
use Muse\Service\DbService;
use Muse\Tool\Log;
use Muse\Tool\Xml;
use Muse\Tool\Upload;
use Exception;
use OSS\Core\OssException;

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
    private $dbSongData;

    public function __construct()
    {
        $this->dir = new Dir();
        $this->dbService    = new DbService();
        $this->outLine('初始化数据',true);
        $this->muScoreFile  = $this->dir->formatFile();
        $this->outLine('初始化数据 ok',true);
    }

    /**
     * 开始
     */
    public function start()
    {
        $params = getCmdParams(config('SCRIPT_OPTIONS'));
        $this->outLine("检查目录..",true);

        Log::delLog(LOG_DIR);
        //检查目录
        $this->dataFactory(true);
        if( $this->isDirErr )
        {
            $this->outLine('目录信息错误,检查目录错误个数 '.$this->isDirErrNum.',请修改目录后 继续..',true);
            exit;
        }
        //导出文件
        $this->dataFactory(false,['export' => true,'params'=>$params]);
    }


    /**
     * @param bool $checkCate
     * @param array $params
     * @throws Exception
     */
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
                        $this->isDirErr = true;$this->isDirErrNum++;
                        Log::writeLog(LOG_DIR,$dir.','.ERR_DIR_EXISTE);
                        continue;
                    }
                    //子目录没有文件
                    if(!$child){
                        $this->isDirErr = true;$this->isDirErrNum++;
                        Log::writeLog(LOG_DIR,$dir.','.ERR_DIR_EMPTY);
                        continue;
                    }
                }

                //导出文件
                if( $params['export'] ) {
                    $sortData = [];
                    //排序数据
                    foreach ($child as $s => $f) {
                        $sortData[clearExt($f)] = $s;
                    }
                    $this->sortData = $sortData;

                    //初始化该分类下的歌曲
                    if(!($this->dbSongData = $this->dbService->getCategorySongData($k,$ck)))
                    {
                        Log::system($dir.' 目录下没有歌曲');
                        throw new Exception($dir.' 目录下没有歌曲');
                    }
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
        $screen         = $params[S_OPTION_SCREEN];
        $exportCmd      = $screen == S_SCREEN_H ? config('CMD.MU_HOR_COMMAND')
            : config('CMD.MU_VER_COMMAND');

        $fileDir        = $pathData['dir'].$pathData['fileName'];

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
//        if( $screen == S_SCREEN_H)
//        {
//            if(config('CREATE_ANDROID_DATA'))
//            {
//                $this->createAndroidData($pathData['dir'],$data);
//            }
//        }

        if ($params[S_OPTION_ZIP] == S_ZIP_ON  || !isset( $params[S_OPTION_ZIP])) {
            $this->outLine($fileDir.', 开始压包..');
            //压包
            $zip = compressZip($pathData['dir'],$fileDir);
            $this->outLine($fileDir.'/'.$pathData['fullName'].', 压包成功..');
        }
        //上传到oss

        if( (!$params[S_OPTION_UPLOAD] || $params[S_OPTION_UPLOAD] == S_UPLOAD_ON) && $zip['name'] )
        {
            $dir        = explode('/',$pathData['fileDir']);
            if(!$dir[0] || !$dir[1]) {
                Log::system($pathData['fileDir'].' 目录错误');
                throw new Exception(' 目录错误');
            }

            $object     =  config('OSS_DIR').'/'.$dir[0].'/'.$dir[1].'/'.$zip['code'].'.zip';
            try
            {
                $url    =  Upload::uploadOss($zip['name'],$object);
            }catch (OssException $e)
            {
                Log::system($e->getMessage());
                throw new Exception($e->getMessage());
            }

            if($url)
            {
                //更新到数据库
                if( $this->updateDb($pathData,$params,$url,$dir,''))
                {
                    //记录已上传的曲目
                    Log::writeLog(LOG_POINT,$pathData['dir'].$pathData['fileName'].$pathData['ext']);
                    //删除压缩文件和目录
                    unlink($zip['name']);
                    removeDir($fileDir);
                }
            }
        }


    }

    /**
     * @param $path
     * @param $params
     * @param $url
     * @param $childName
     * @param string $androidUrl
     * @return bool|\mysqli_result
     * @throws Exception
     */
    private function updateDb($path,$params,$url,$childName,$androidUrl = '')
    {

        //横屏还是竖屏
        $screen = !$params[S_OPTION_SCREEN] ? S_SCREEN_V : $params[S_OPTION_SCREEN];
        if( !in_array($screen,[S_SCREEN_V,S_SCREEN_H]) ) throw new Exception('屏幕参数错误');

        //添加
        if( $params[S_OPTION_METHOD] == S_METHOD_ADD  && $url)
        {
            $parentCate = $this->dbService->findCategory("chinese_name='".$childName[0]."'",'id');
            $category   =  $this->dbService->findCategory("chinese_name='".$childName[1]."' and pcid=".$parentCate['id'],'id');
            if(!$category)
            {
                Log::system($path['dir'].' 目录数据为空');
                throw new Exception($path['dir'].' 目录数据为空');
            }

            $insertId   =  $this->dbService->insertSong([
                    'cid'   =>  $category['id'],
                    'chinese_name' => $path['fileName'],
                    'sort' => $this->sortData[$path['fileName']],
                ]);

            $result =  $this->dbService->insertSongRc($screen,[
                'song_id' => $insertId,
                'horscreen_url' => $url,
                'h_android_url' => $androidUrl,
            ]);
            $this->outLine($path['dir'].$path['fileName'].' 添加成功');
            return $result;
        }

        if( ($params[S_OPTION_METHOD] == S_METHOD_UPDATE || !isset($params[S_OPTION_METHOD]))  && $url)
        {
            //记录错误歌曲
            if(!($songId = $this->dbSongData[$path['fileName']])) {
                Log::writeLog(LOG_SONG,$path['dir'].$path['fileName']);
                return false;
            }

            $result = $this->dbService->updateSongRc($screen,[
                'song_id' => $songId,
                'url' => $url,
                'h_android_url' => $androidUrl,
            ]);
            if($result) $this->outLine($path['dir'].$path['fileName'].' 更新成功');
            return $result;
        }
    }


    /**
     * @param $path
     * @param $fullName
     * @param $data
     * @throws Exception
     */
    private function createPlist($path,$fullName,$data)
    {
        if(!file_exists($data['svg']))
        {
            Log::system($data['svg'].' 不存在');
            throw new Exception($data['svg'].' 不存在');
        }
        if(!file_exists($data['pos']))
        {
            Log::system($data['pos'].' 不存在');
            throw new Exception($data['pos'].' 不存在');
        }
        $outPath = $path.DIRECTORY_SEPARATOR.$fullName;

        if( $content = file_get_contents($data['svg']) ) {
                //去除颜色
                if(config('IS_CHANGE_SVG_COLOR'))
                {
                    if( Xml::checkSvgColor($content) )
                    {
                        write($data['svg'],Xml::replaceSvg($data));
                    }
                }
                if(!($svgData = Xml::svg($content)))
                {
                    Log::system($data['svg'].' 内容解析失败');
                    throw new Exception($data['svg'].' 内容解析失败');
                }
                write($outPath.'.plist', $svgData);
                removeFile($data['svg']);
                $this->outLine($data['svg'].', 处理完成');

         }
         if( !($posData = Xml::pos(file_get_contents($data['pos']))))
         {
             Log::system($data['pos'].' 内容解析失败');
             throw new Exception($data['pos'].' 内容解析失败');
         }
         write($outPath.'_pos.plist', $posData);removeFile($data['pos']);
         $this->outLine($data['pos'].', 处理完成');

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