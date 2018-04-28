<?php
namespace application\index\controller;
use ly\lib\Controller;
use ly\lib\database\DB\DB as DB;
class query{
    public $sql="";
    static public function table($t){
        $obj=new self();
        $obj->sql.=$t;
        return $obj;
    }
    public function select(){
        $this->sql="SELECT ".$this->field?:"*" ." from ".$this->tablename.$this->where.$this->order.$this->limit;
        return $this->sql;
    }
    public function update($arr){
        $this->sql="update ".$this->tablename."";
    }
}
class index extends Controller{
    public function index(){
        $this->assign("liu","li");
        var_dump($this->assign);
        echo "1";
    }
    public function liu(){
        $db=DB::getInstance(["database"=>"zhihu"]);
        var_dump($db);
    }
    public function ceshi(){
        echo query::table("liu")->select();
    }
}