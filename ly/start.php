<?php
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set('PRC');
define("LY_BASEPATH", dirname(__DIR__)."/");
chdir(dirname(__DIR__)."/public");
!defined("APP_PATH") && define("APP_PATH","application");
if(is_file( LY_BASEPATH."/vendor/autoload.php")){
    include LY_BASEPATH."/vendor/autoload.php";
}
include LY_BASEPATH."ly/lib/Loader.php";
include LY_BASEPATH."ly/help.php";
(new ly\lib\loader())->autoload();



$system_config=include LY_BASEPATH."/ly/config.php";
$user_config=is_file(LY_BASEPATH."/config/config.php")?include LY_BASEPATH."/config/config.php":array();
$config = array_merge($system_config,$user_config);
define("LY_CONFIG",$config);

if($config['PRODUCTION_MODE']){
    ini_set("display_errors", "Off");
    error_reporting(0);
    define("DEBUG",false);
}else{
    ini_set("display_errors", "On");
    error_reporting(E_ALL | E_STRICT);
    define("DEBUG",true);
}
$go=new \ly\lib\Exception();