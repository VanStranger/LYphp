<?php
namespace application\index\controller;
error_reporting(0);
use ly\lib\Controller;
use \application\index\model as Model;
use ly\lib\DB as DB;
use ly\lib\PDO as PDO;
class index extends Controller{
    public function index(){
        $this->assign("IWantToSay","，我好想你。");
        $Love=new Model\Love();
        $hername=$Love->gethername();
        $this->assign("hername",$hername);
        $this->displayhtml();
    }

}