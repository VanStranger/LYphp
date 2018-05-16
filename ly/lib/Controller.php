<?php
namespace ly\lib;
class Controller{
    public $assign=null;
    public function assign($name,$value){
        $this->assign[$name]=$value;
    }
    public function display($file=""){
        $file= BASEPATH . APP_PATH ."/".M."/view/".C."_".A.".php";
        if(!is_file($file) ){
            throw new \Exception($file."模板文件不存在。", 1);
        }else{
            if($this->assign){
                foreach ($this->assign as $key => $value) {
                    $$key=$value;
                }
            }
            $res=include $file;
            return $res;
        }
    }
}