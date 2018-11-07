<?php
namespace Muse\Service;
use Muse\Tool\DbHelper;

class DbService
{
    private $db;

    public function __construct()
    {
        $this->db = DbHelper::getInstance();
    }

    public function checkCategory($category)
    {
        if( !$category ) return false;
        $parentCate = $this->findCategory("chinese_name='".$category['p']."'",'id,chinese_name');
        if(!$parentCate) return false;
        $childCate  = $this->findCategory("chinese_name='".$category['c']."' and pcid = " .$parentCate['id'],'id,chinese_name,pcid');
        if(!$childCate) return false;
        return ['id'=>$childCate['id'],'name' => $childCate['chinese_name']];
    }

    public function findCategory($map , $field)
    {
        return $this->db->find("select $field from cmf_song_category where $map");
    }


}