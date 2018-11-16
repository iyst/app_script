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

        $musicSql       = 'select id,chinese_name from cmf_song where cid='.$child['id'];
        $music          = $this->db->select($musicSql);
        $song           = [];

        foreach ($music as $k)
        {
            $song[$k['chinese_name']] = $k['id'];
        }
        return $song;
    }




}