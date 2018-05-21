<?php
//应用的根目录就是index.php的父目录
define("BASEPATH", dirname(__DIR__)."/");
define("DEBUG",true);
define('SITE_ROOT' , 'http://mvc.com');
define("APP_PATH","application");
include BASEPATH."/vendor/autoload.php";
if(DEBUG){
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}else{
    ini_set('display_error','off');
}
require (BASEPATH . '/ly/' . 'start.php');
