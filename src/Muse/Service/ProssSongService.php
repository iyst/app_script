<?php
namespace Muse\Service;
use Muse\Tool\Dir;
use Muse\Service\DbService;
use Muse\Tool\Log;
use Muse\Tool\Xml;
use Muse\Tool\Upload;
use Exception;
use OSS\Core\OssException;
use Muse\Service\MiddlewareService;
use Muse\Service\PngService;


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
    private $params;
    private $ossObject;
    public function __construct()
    {
        $this->ossObject = new Upload();
        $this->params = MiddlewareService::checkCmd();
        $this->dir = new Dir();
        $this->dbService    = new DbService();
        $this->outLine('初始化数据',true);
        //$this->muScoreFile  = $this->dir->formatFileCopy();
        $this->outLine('初始化数据 ok',true);
    }

    /**
     * 开始
     */
    public function start()
    {
        $this->outLine("检查目录..",true);
        Log::delLog(LOG_DIR);
        //检查目录
        $this->checkCategory();
        if( $this->isDirErr )
        {
            $this->outLine('目录信息错误,检查目录错误个数 '.$this->isDirErrNum.',请修改目录后 继续..',true);
            exit;
        }
        foreach ($this->muScoreFile as $k => $cate) {
            foreach ($cate as $ck => $child) {
                $dir  = $k.DIRECTORY_SEPARATOR.$ck;
                //初始化该分类下的歌曲
                if($this->params[S_OPTION_METHOD]== S_METHOD_UPDATE) {
                    if(!($this->dbSongData = $this->dbService->getCategorySongData($k,$ck))) {
                        Log::system($dir.' 目录下没有歌曲');
                        throw new Exception($dir.' 目录下没有歌曲');
                    }
                }
                foreach ($child as  $fName) {
                    $this->exportPng($dir,trim($fName));
                }
            }
        }
    }

    public function checkJumpNotes()
    {
        $rootPath   = WORK_PATH.'/';
        $exportPath = '/Users/iyst/Downloads/jump/';
        $exportPath1 = '/Users/iyst/Downloads/jump1/';
        $msczPath  = '/Users/iyst/Downloads/mscz/';

        $handle = opendir( $msczPath );
        while ( ($dir = readdir($handle)) != false ){
            if($dir == '..' || $dir =='.' || !$dir) continue;
            $index      = strpos($dir,'.');
            if($index == 0 && gettype($index) == 'integer') continue;
            if(is_dir($msczPath.$dir))
            {

                $child =  opendir($msczPath.$dir);
                while (($childDir = readdir($child))!= false)
                {
                    if($childDir == '..' || $childDir =='.' || !$childDir) continue;
                    $index      = strpos($childDir,'.');
                    if($index == 0 && gettype($index) == 'integer') continue;

                    $cld =  opendir($msczPath.$dir.'/'.$childDir);
                    while (($cldDir = readdir($cld))!= false)
                    {
                       $name = $msczPath.$dir.'/'.$childDir.'/'.$cldDir;
                       if(strrpos($name,'.pos'))
                       {
                          $data = file_get_contents($name);
                          if(strstr($data,'isStaccato'))
                          {
                              $filePath = $rootPath.str_replace('.pos','.mscz',$dir.'/'.$childDir.'/'.$cldDir);
                                if(!is_dir($exportPath1.$dir)) mkdir($exportPath1.$dir);
                                if(!is_dir($exportPath1.$dir.'/'.$childDir)) mkdir($exportPath1.$dir.'/'.$childDir);
                                $cmd = 'cp "' .$filePath.'" "'. $exportPath1.$dir.'/'.$childDir.'"';
                                echo $cmd."\n";
                                system($cmd);
                          }
                       }
                    }
                }
            }

        }

//        foreach ($this->muScoreFile as $k => $cate) {
//            foreach ($cate as $ck => $child) {
//                $dir  = $k.DIRECTORY_SEPARATOR.$ck;
//                foreach ($child as  $sort => $fName) {
//
//                    $fileData = getExportName($dir,$sort,$fName);
//                    //var_dump($fileData);exit;
////                    if($fileData['ext'] == '.mscx')
////                    {
////                        $childPath = explode('/',$fileData['fileDir']);
////                        $path = $rootPath.$dir.'/'.$fName;
////                        $content = file_get_contents($path);
////                        if(strstr($content,'staccato'))
////                        {
////                            if(!is_dir($exportPath.$childPath[0])) mkdir($exportPath.$childPath[0]);
////                            if(!is_dir($exportPath.$childPath[0].'/'.$childPath[1])) mkdir($exportPath.$childPath[0].'/'.$childPath[1]);
////                            $cmd = 'cp "' .$fileData['dir'].$fileData['fileName'].$fileData['ext'].'" "'.
////                                $exportPath.$childPath[0].'/'.$childPath[1].'/'.$fileData['fileName'].$fileData['ext'].'"';
////                            echo $cmd."\n";
////                            system($cmd);
////                        }
////                    }
//
//                    $searchKey = 'isStaccato';
//
////                    if($fileData['ext'] == '.mscz')
////                    {
////                        $path = '"'.$rootPath.$dir.'/'.$fName.'"';
////                        $childPath = explode('/',$fileData['fileDir']);
////                        if(!is_dir($msczPath.$childPath[0])) mkdir($msczPath.$childPath[0]);
////                        if(!is_dir($msczPath.$childPath[0].'/'.$childPath[1])) mkdir($msczPath.$childPath[0].'/'.$childPath[1]);
////                        $outPath = '"'.$msczPath.$fileData['fileDir'].'/'.$fileData['fileName'].'.pos'.'"';
////
////                        $cmd = config('CMD.MU_VER_COMMAND').' '.$path.' -o '.$outPath;
////                        echo $cmd."\n";
////                        system($cmd);
////                    }
//
//
//                }
//            }
//        }
    }


    public function checkColor()
    {
        $rootPath   = WORK_PATH.'/';
        $f = fopen($rootPath.'color.txt','a+');
        foreach ($this->muScoreFile as $k => $cate) {
            foreach ($cate as $ck => $child) {
                $dir  = $k.DIRECTORY_SEPARATOR.$ck;
                foreach ($child as  $sort => $fName) {

                    $fileData = getExportName($dir,$sort,$fName);

                    $path = '"'.$rootPath.$dir.'/'.$fName.'"';
                    $outPath = '"'.$rootPath.$fileData['fileName'].'.svg'.'"';

                    $cmd = config('CMD.MU_VER_COMMAND').' '.$path.' -o '.$outPath;

                    system($cmd);

                    $svgPath = $rootPath.$fileData['fileName'].'.svg';
                    if($svgData = file_get_contents($svgPath))
                    {
                        if(xml::checkSvgColor($svgData)){
                            fwrite($f,$dir.'/'.$fName."\n");
                        }
                    }
                    unlink($svgPath);
                }
            }
        }
        fclose($f);
    }


    public function test()
    {
        foreach ($this->muScoreFile as $k => $cate)
        {
            foreach ($cate as $ck => $child)
            {
                $dir  = $k.DIRECTORY_SEPARATOR.$ck;


                //导出文件

                $sortData = [];
                //排序数据
                foreach ($child as $s => $f) {
                    $sortData[clearExt($f)] = $s;
                }
                $this->sortData = $sortData;

                //初始化该分类下的歌曲
                if($this->params[S_OPTION_METHOD]== S_METHOD_UPDATE)
                {
                    if(!($this->dbSongData = $this->dbService->getCategorySongData($k,$ck)))
                    {
                        Log::system($dir.' 目录下没有歌曲');
                        throw new Exception($dir.' 目录下没有歌曲');
                    }
                }

                foreach ($child as $sort => $fName)
                {
                    // var_dump($fName);
                    $this->exportPng(getExportName($dir,$sort,$fName));
                }


            }
        }
    }

    /**
     * 导出png图片
     * @param $dir 目录
     * @param $fName 文件名称
     */
    public function exportPng($dir,$fName)
    {

        $sourceName = $fName;
        $rootPath   = WORK_PATH.'/';
        $rootPath   = $rootPath.$dir.DIRECTORY_SEPARATOR;

        $fName      = str_replace('.mscx','',$fName);
        $fileName   = str_replace('.mscz','',$fName);

        $fileDir    = $rootPath.$fileName;
        /////////////
        $explodeData    = explode(' ',$fileName);
        if(count($explodeData) >1 )
        {
            if(is_numeric($explodeData[0]))
            {
                $firstIndex = strpos($fileName , ' ');
                $dbName     = $explodeData[0].'-'.substr($fileName,$firstIndex+1,strlen($fileName));
            }else{
                $dbName   = $fileName;
            }
        }
        else
        {
            $dbName =  $explodeData[0].'-'.$explodeData[0];
        }


        //////////////////
        $songId         = $this->dbSongData[$dbName];
        if(!$songId)
        {
            $songId         = $this->dbSongData[str_replace(' ','',$dbName)];
        }
        if(!$songId)
        {
            Log::writeLog('dbSongID',$fileDir);
            return;
        }

        //生成目录
        createDir($fileDir);
        $posPath    = '"'.$fileDir.'/'.$fileName.'.png"';
        $filePath   = '"'.$rootPath.$sourceName.'"';

        $cmd        = config('CMD.MU_VER_COMMAND') .' '.$filePath .' -o '.$posPath;
        $this->outLine('CMD:'.$songId.'---'.$cmd);
        system($cmd);

        $pngService = new PngService();
        $pngFileUrl = $fileDir.'/'.$fileName.'-1.png';

        if(!file_exists($pngFileUrl)){
            $pngFileUrl = $fileDir.'/'.$fileName.'-01.png';
        }
        if(!file_exists($pngFileUrl)) {
            Log::writeLog('png',$fileDir.'/'.$fileName);
        }
        if( $pngData = $pngService->cutPng($fileDir,$pngFileUrl))
        {
            $this->outLine($pngData['path'].' 剪切成功');
            //return;
            $dir   =  explode('/',$dir);

            $object     =  config('OSS_PNG_DIR').'/'.$dir[0].'/'.$dir[1].'/'.$pngData['name'];

            if($ossResult = $this->ossObject->uploadOss($pngData['path'],$object))
            {
                $this->outLine($pngData['path'].' 上传成功');
                if($ossResult)
                {

                    $this->dbService->updatePreViewUrl($songId,$ossResult);
                    $this->outLine($songId.'---'.$pngData['path'].'  更新成功');
                    removeDir($fileDir);
                }
            }
        }

    }



    private function checkCategory()
    {
        foreach ($this->muScoreFile as $k => $cate) {
            if( !($pData =  $this->dbService->checkCategory(['p'=>$k])) ) {
                $this->isDirErr = true;$this->isDirErrNum++;
                Log::writeLog(LOG_DIR,$k.','.ERR_DIR_EXISTE);
                continue;
            }
            foreach ($cate as $ck => $child) {
                $dir  = $k.DIRECTORY_SEPARATOR.$ck;
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

        //记录错误歌曲
        if(!$this->dbSongData[$pathData['fileName']]) {
            Log::writeLog(LOG_SONG,$fileDir.'/'.ERR_SONG_NAME);
            return;
        }
        //生成目录
        createDir($fileDir);
        $posPath    = '"'.$fileDir.'/'.$pathData['fullName'].config('EXPORT_EXT').'"';
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
        if( $params[S_OPTION_C] == S_C_ON )
        {
            $this->createPlist($fileDir,$pathData['fullName'],$data);
        }

        if ($params[S_OPTION_ZIP] == S_ZIP_ON  || !isset($params[S_OPTION_ZIP])) {
            $this->outLine($fileDir.', 开始压包..');
            //压包
            $zip = compressZip($pathData['dir'],$fileDir);
            $this->outLine($fileDir.'/'.$pathData['fullName'].', 压包成功..');
        }

        //压包成功删除目录
        if($zip['name']) removeDir($fileDir);

        //上传到oss
        if( ((isset($params[S_OPTION_UPLOAD]) && $params[S_OPTION_UPLOAD] == S_UPLOAD_ON && $zip['name']) || !isset($params[S_OPTION_UPLOAD]) && $zip['name'] ) )
        {
            $dir   =  explode('/',$pathData['fileDir']);
            if(!$dir[0] || !$dir[1]) {
                Log::system($pathData['fileDir'].' 目录错误');
                throw new Exception(' 目录错误');
            }

            $object     =  config('OSS_DIR').'/'.$dir[0].'/'.$dir[1].'/'.$zip['code'].'.zip';
            try {
                $url    =  Upload::uploadOss($zip['name'],$object);
            }catch (OssException $e)
            {
                Log::system($e->getMessage());
                throw new Exception($e->getMessage());
            }

            if($url)
            {
                $this->outLine($pathData['dir'].$pathData['fileName'].' 上传成功!');
                //更新到数据库
                if( $this->updateDb($pathData,$params,$url,$dir,''))
                {
                    //记录已上传的曲目
                    Log::writeLog(LOG_POINT,$pathData['dir'].$pathData['fileName'].$pathData['ext']);
                    //删除压缩文件和目录
                    unlink($zip['name']);
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
        //添加
        if( $params[S_OPTION_METHOD] == S_METHOD_ADD  && $url)
        {
            $parentCate = $this->dbService->findCategory("chinese_name='".$childName[0]."'",'id');
            $category   =  $this->dbService->findCategory("chinese_name='".$childName[1]."' and pcid=".$parentCate['id'],'id');

            //
            if($category)
            {
                $this->dbService->deleteSongs($category['id']);
            }
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

            if($this->dbService->insertSongRc($screen,[
                'song_id' => $insertId,
                'horscreen_url' => $url,
                'h_android_url' => $androidUrl,
            ])) $this->outLine($path['dir'].$path['fileName'].' 添加成功');
            return true;
        }

        if( ($params[S_OPTION_METHOD] == S_METHOD_UPDATE
                || !isset($params[S_OPTION_METHOD]))  && $url)
        {
            if(!($songId = $this->dbSongData[$path['fileName']])) return false;
            if($this->dbService->updateSongRc($screen,[
                'song_id' => $songId,
                'url' => $url,
                'h_android_url' => $androidUrl,
            ])) $this->outLine($path['dir'].$path['fileName'].' 更新成功');
            return true;
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