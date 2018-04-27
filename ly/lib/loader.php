<?php
namespace ly\lib;
class loader{
    public static $classMap=array();
    static public function load($class){
        if(isset($classMap[$class])){
            return true;
        }else{
            $class=str_replace("\\","/",$class);
            $file1=SERVER_ROOT.$class.".php";
            $file2=SERVER_ROOT."/extend/".$class.".php";
            if(is_file($file1)){
                include $file1;
                self::$classMap[$class]=$class;
            }elseif(is_file($file2)){
                include $file2;
                self::$classMap[$class]=$class;
            }else{
                echo "无法加载".$file;
                return false;
            }
        }
    }
    public function autoload(){
        spl_autoload_register([$this,'load'],true,true);
    }
}