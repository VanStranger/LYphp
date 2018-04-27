<?php
namespace application\index\controller;
use ly\lib\Controller;
class index extends Controller{
    public function index(){
        $this->assign("liu","li");
        var_dump($this->assign);
        echo "1";
    }
    public function liu(){
        echo "liu";
    }
}