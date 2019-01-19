<?php
namespace ly\lib;
class loader{
    public static $classMap=array();
    static public function load($class){
        if(isset($classMap[$class])){
            return true;
        }else{
            $class=str_replace("\\","/",$class);
            $file1=LY_BASEPATH."/".$class.".php";
            $file2=LY_BASEPATH."/extend/".$class.".php";
            if(is_file($file1)){
                include $file1;
                self::$classMap[$class]=$class;
            }elseif(is_file($file2)){
                include $file2;
                self::$classMap[$class]=$class;
            }else{
                throw new \Exception("找不到".$class ."，引入失败。", 1);
            }
        }
    }
    public function autoload(){
        spl_autoload_register([$this,'load'],true,true);
    }
}