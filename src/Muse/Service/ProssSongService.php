<?php
namespace Muse\Service;
use Muse\Tool\Dir;
use Muse\Service\DbService;

class ProssSongService
{

    private $dir;
    private $dbService;
    public function __construct()
    {
        $this->dir = new Dir();
        $this->dbService = new DbService();
    }

    /**
     * 处理歌曲
     */
    public function prossSong()
    {
        $museFiles = $this->dir->formatFile();
        foreach ($museFiles as $k => $cate)
        {
            $category['p'] = $k;
            foreach ($cate as $ck => $child)
            {
                $category['c'] = $ck;
                $result = $this->dbService->checkCategory($category);
            }

        }

       // var_dump($museFiles);

       // $params =  getCmdParams(['m','s']);
    }
}