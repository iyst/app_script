<?php
namespace Muse\Service;
use Muse\Tool\DbHelper;

class DbService
{
    private $db;
    private $tb_category = 'cmf_song_category';
    private $tb_song    = 'cmf_song';
    private $tb_song_rc = 'cmf_song_rc';


    public function __construct()
    {
        $this->db = DbHelper::getInstance();
    }

    public function checkCategory($category)
    {
        if( $category['p'])
        {
            if($parentCate = $this->findCategory("chinese_name='".$category['p']."'",'id,chinese_name')) return $parentCate;
        }
        if( $category['c'] )
        {
            if($childCate  = $this->findCategory("chinese_name='".$category['c']."' and pcid = " .$category['pcid'],'id,chinese_name,pcid')) return $childCate;
        }
        return false;
    }

    public function findCategory($map , $field)
    {
        $sql = "select $field from ".$this->tb_category." where $map";
        return $this->db->find($sql);
    }

    public function insertSong($data)
    {
        $sql = "insert into $this->tb_song (cid,chinese_name,cover_pic,sort,created) values(".$data['cid'].",'".$data['chinese_name']."','',".$data['sort'].",time())";
        $this->db->query($sql);
        return $this->db->getInserID();
    }

    /**
     * @param $screen
     * @param $data
     * @return bool|\mysqli_result
     */
    public function insertSongRc($screen,$data)
    {
        $sql = $screen == S_SCREEN_V ?
            "insert into $this->sb_song_rc (song_id,url,created) VALUES (".$data['song_id'].",'".$data['url']."',time())" :
            "insert into cmf_song_rc (song_id,horscreen_url,h_android_url,created,url) VALUES (".$data['song_id'].",'".$data['horscreen_url']."','".$data['h_android_url']."',time(),'')";
        return $this->db->query($sql);
    }

    /**
     * @param $screen
     * @param $data
     * @return bool|\mysqli_result
     */
    public function updateSongRc($screen,$data)
    {
        $sql = $screen == S_SCREEN_V ?
            "update $this->tb_song_rc set url = '".$data['url']."' where song_id=".$data['song_id'] :
            "update $this->tb_song_rc set horscreen_url = '".$data['url']."',h_android_url='".$data['h_android_url']."' where song_id=".$data['song_id'];
        return $this->db->query($sql);
    }

    /**
     * @param $parentName
     * @param $childName
     * @return array|null
     */
    public function getCategorySongData($parentName,$childName)
    {
        $sql            = "select id from $this->tb_category where chinese_name='".$parentName."'";
        if ( !($parent  = $this->db->find($sql)) ) return null;

        $childSql       = "select id,pcid from $this->tb_category where chinese_name='".$childName."'";
        if ( !($child   = $this->db->find($childSql)) ) return null;

        if( $child['pcid'] != $parent['id'] ) return null;

        $musicSql       = 'select id,chinese_name,sort from cmf_song where cid='.$child['id'];
        $music          = $this->db->select($musicSql);
        $song           = [];

        foreach ($music as $k)
        {
            if($k['sort'] ) {

                if($k['sort'] < 10)
                {
                    $sort = '0'.$k['sort'];
                }else{
                    $sort = $k['sort'];
                }
                $chineseName = $sort.'-'.$k['chinese_name'] ;
            }else{
                $chineseName = $k['chinese_name'];
            }
            $song[$chineseName] = $k['id'];
        }
        return $song;
    }

    public function deleteSongs($id)
    {
        $sel = "delete from cmf_song where cid=".$id;
        return $this->db->query($sel);
    }

    public function updatePreViewUrl($songId,$url)
    {
        $sql = "update $this->tb_song_rc  set preview_img='".$url."' where song_id=".$songId;
        return $this->db->query($sql);
    }

    public function getPreviewImgEmpty()
    {
        $songData =  $this->db->select('select t.id,t.song_id,s.cid,s.chinese_name from '.$this->tb_song_rc.' as t left join '.$this->tb_song.' as s on t.song_id = s.id where t.preview_img="" and s.cid > 0');

        foreach ($songData as $k =>$item)
        {
            echo $item['chinese_name']."\n";
            if(!$item['cid']) continue;
            $c1 = "select pcid,chinese_name from cmf_song_category where id=".$item['cid'];

            if($c1Data = $this->db->find($c1))
            {
                $c2 = 'select chinese_name from cmf_song_category where id='.$c1Data['pcid'];
                $c2Data = $this->db->find($c2);
            }
            $songData[$k]['chinese_name'] = $c2Data['chinese_name'].'/'.$c1Data['chinese_name'].'/'.$item['chinese_name'];
        }
        return $songData;
    }

}