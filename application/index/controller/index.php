<?php
namespace application\index\controller;
use ly\lib\Controller;
use ly\lib\database\DB\DB as DB;
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
}