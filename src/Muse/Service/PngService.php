<?php
namespace Muse\Service;
use Muse\Service\DbService;
use Muse\Tool\Log;

class PngService
{
      //通用
    const TITLE_HEIGHT  = 389;
    const CUT_HEIGHT    = 1070;

/*
 * 巴赫创意曲集（人民音乐出版社）
       3 D大调
       4 D小调

    巴赫二部创意曲和三部创意曲 （上海音乐出版社）
 * */
 //   const TITLE_HEIGHT  = 589;
 //   const CUT_HEIGHT    = 1270;
/*
 * 04.莫扎特钢琴奏鸣曲nr 282
       4 04 Allegro

    10.莫扎特钢琴奏鸣曲nr 330
        3 03Allegro
    15.莫扎特钢琴奏鸣曲nr 533
        02 anda
    19.莫扎特钢琴奏鸣曲nr 576
        02
        03
    流行-exo-history， 台湾男歌手-40 别怕我伤心
    流行
     欧美流行
        angel
 * */

//    const TITLE_HEIGHT  = 50;
//    const CUT_HEIGHT    = 750;
    /*
     *  影视歌曲
    岁月神偷
     */
//   const TITLE_HEIGHT  = 589;
//   const CUT_HEIGHT    = 1270;
//考级-上海音乐学院考级2016 1-8 ,21 25-28
//练习曲-车尔尼636-01
//练习曲-克拉莫钢琴练习曲60首 20,19
//    const TITLE_HEIGHT  = 670;
//    const CUT_HEIGHT    = 1270;

//名家作品-理查德克莱德曼钢琴曲集精选集 03 08 10 13 14 19 39 40 41 46 49 52-54 56-58
//    const TITLE_HEIGHT  = 620;
//    const CUT_HEIGHT    = 1270;
    private $db;
    public function __construct()
    {
        $this->db = new DbService();
    }

    public function cutPng( $sourcePath,$file )
    {
        $sourceInfo     = getimagesize($file);
        $width          = $sourceInfo[0];
        $sourceImage    = imagecreatefrompng($file);

        //计算要修剪的高度
        $cutPngHeight   = self::CUT_HEIGHT - self::TITLE_HEIGHT;
        $croppedImage   = imagecreatetruecolor($width, $cutPngHeight);

        //2.上色
        $color          = imagecolorallocate($croppedImage,255,255,255);
        //3.设置透明
        imagecolortransparent($croppedImage,$color);
        imagefill($croppedImage,0,0,$color);

        //截取
        imagecopy($croppedImage, $sourceImage, 0, 0, 0, self::TITLE_HEIGHT, $width, $cutPngHeight);


        //保存修改好的图片
        $pngName = createCode().'.png';
        $pngPath = $sourcePath.'/'.$pngName;

        imagepng($croppedImage,$pngPath);
        imagedestroy($croppedImage);

        return ['path'=>$pngPath,'name'=>$pngName];
    }

    public function checkPngEmpty()
    {
        $data = $this->db->getPreviewImgEmpty();
        foreach ($data as $item)
        {
            echo $item['chinese_name']."\n";
            Log::writeLog('pngEmpty',"id:".$item['id'].'-- song_id:'.$item['song_id'].'--'.$item['chinese_name']);
        }
    }
}