<?php
namespace ly\lib;
class router{
    public $request;
    public function __construct(){
        $this->request = $_SERVER['QUERY_STRING'];
    }
    public function getRoute(){
        if(!$this->request){
            return false;
        }else{
            $routerArr=explode("&", $this->request);
            $routerStr=trim($routerArr[0],"/");
            $routers=explode("/", $routerStr);
            if(count($routers)<3){
                return false;
            }
            return $routers;
        }
    }
}