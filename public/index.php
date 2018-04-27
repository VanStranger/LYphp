<?php

//应用的根目录就是index.php的父目录
define("SERVER_ROOT", dirname(__DIR__)."/");
define('SITE_ROOT' , 'http://mvc.com');
define("APP_PATH","application");
require_once(SERVER_ROOT . '/ly/' . 'start.php');
