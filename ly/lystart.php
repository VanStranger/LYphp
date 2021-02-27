<?php
//应用的根目录就是index.php的父目录
define("LY_BASEPATH", dirname(__DIR__)."/");
chdir(dirname(__DIR__)."/public");
!defined("APP_PATH") && define("APP_PATH","application");
include LY_BASEPATH."ly/lib/Loader.php";
include LY_BASEPATH."ly/help.php";
(new ly\lib\loader())->autoload();
if(is_file( LY_BASEPATH."/vendor/autoload.php")){
    include LY_BASEPATH."/vendor/autoload.php";
}
$ly=new \ly\LY();
$ly->run();
