<?php

//应用的根目录就是index.php的父目录
define("SERVER_ROOT", dirname(__FILE__));

//你的域名.comm 是你的服务器域名
define('SITE_ROOT' , 'http://mvc.com');

/**
 * 引入router.php
 */
 require_once(SERVER_ROOT . '/LY/' . 'index.php');