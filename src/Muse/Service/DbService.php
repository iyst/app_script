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
        $sql = "select $field from cmf_song_category where $map";
      //  var_dump($sql);
        return $this->db->find($sql);
    }


}