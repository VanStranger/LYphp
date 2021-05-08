<?php
namespace application\index\controller;
use ly\lib\Controller;
use \application\index\model as Model;
use \ly\lib\DB as DB;
class Index extends Controller{
    public function __construct(){
        // echo "construct";
    }
    public $hook=[
        "pre"
    ];
    public function pre(){
        // return "pre";
        // var_dump(config("path_type"));
    }
    public function index(){
        $Love=new Model\Love();
        $hername=$Love->gethername();
        $data=DB::table("users")->where("id",10000)->select();
        $this->assign("hername",$hername);
        $this->assign("showhtml",'代码是:<p>Hello，{{ $hername}}。</p>');
        $this->displayHtml();
    }
    public function jsonapi($a){
        var_dump($a);
        $a=1;
        $data=DB::table("users")->where("id",10000)->buildSql();
        // var_dump($data);
        $d=DB::table(["answer"=>"a"])->join([$data,"m"],"a.authorid=m.id")->field("m.id,username,title")->select();
        return $d;
    }
}