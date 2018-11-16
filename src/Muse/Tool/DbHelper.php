<?php
namespace Muse\Tool;
use Exception;
class DbHelper
{
    private static $instance;
    private $dbConnect;
    private function __construct(){
        $this->connect();
    }
    public function connect(){
        if(!$this->dbConnect) {
            $this->dbConnect = mysqli_connect(
               config('db.host'),
               config('db.user'),
               config('db.passwd')
            );
            if(!$this->dbConnect) throw new Exception(mysqli_error());
            $this->selectDb();
            $this->query('set names utf8');
        }
    }
    public static function getInstance(){
        if(!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function select($sql){
        $result = $this->query($sql);
        $list =array();
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $list[] = $row;
        }
        return $list;
    }
    public function find($sql){
        return mysqli_fetch_assoc($this->query($sql));
    }
    public function update($sql){
        $this->query($sql);
        return mysqli_affected_rows($this->dbConnect);
    }
    private function selectDb(){
        mysqli_select_db($this->dbConnect,config('db.dbname'));
    }
    public function query($sql){
        return mysqli_query($this->dbConnect,$sql);
    }
    public function getInserID(){
        return mysqli_insert_id($this->dbConnect);
    }
    public function __destruct()
    {
        $this->close();
        // TODO: Implement __destruct() method.
    }
    private function close()
    {
        mysqli_close($this->dbConnect);
    }
}