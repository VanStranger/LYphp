<?php
namespace ly\lib;
class router{
    public function __construct(){
    }
    public function getRoute(){
        if(!$_GET){
            return [false,false,false];
        }else{
            foreach ($_GET as $key => $value) {
                if($value==""){
                    $routers=include BASEPATH."/config/Routes.php";

                    $request=$key;

                    $routerArr=explode("&", $request);
                    $routerStr=trim($routerArr[0],"/");
                    $routers=explode("/", $routerStr);
                    $num=min(count($routers),3);
                    $routerArr=['model','controller','action'];
                    for($i=0;$i<$num;$i++){
                       if($pos=strpos($routers[$i],"_")){
                           $routers[$i]=substr($routers[$i],0,$pos);
                       }
                    }
                    while($num<3){
                       $routers[$num]=isset($_GET[$routerArr[$num]])?$_GET[$routerArr[$num]]:false;
                       $num++;
                    }
                    while(isset($routers[$num])){
                       if($num%2==0){
                           global $Lyparameters;
                           $Lyparameters[$routers[$num-1]]=$routers[$num];
                       }else{
                       }
                       $num++;
                    }
                }else{
                    $num=0;
                    $routers=[];
                    $routerArr=['model','controller','action'];
                    while($num<3){
                       $routers[$num]=isset($_GET[$routerArr[$num]])?$_GET[$routerArr[$num]]:false;
                       $num++;
                    }
                }
                break;
            }
            return $routers;
        }
    }
}