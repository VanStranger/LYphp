<?php
//应用的根目录就是index.php的父目录
define("LY_BASEPATH", dirname(__DIR__)."/");
!defined("APP_PATH") && define("APP_PATH","application");
if(is_file( LY_BASEPATH."/vendor/autoload.php")){
    include LY_BASEPATH."/vendor/autoload.php";
}
include LY_BASEPATH."ly/lib/Loader.php";
include LY_BASEPATH."ly/help.php";
(new ly\lib\loader())->autoload();
$ly=new \ly\ly();
$ly->run();
