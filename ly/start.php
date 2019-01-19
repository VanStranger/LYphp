<?php
!defined("APP_PATH") && define("APP_PATH","application");
if(is_file( BASEPATH."/vendor/autoload.php")){
    include BASEPATH."/vendor/autoload.php";
}
include BASEPATH."ly/lib/Loader.php";
include BASEPATH."ly/help.php";
(new ly\lib\loader())->autoload();
$ly=new \ly\ly();
$ly->run();
