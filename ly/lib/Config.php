<?php
namespace ly\lib;
class Config{
    protected $config = [];
    public function getConfig(){
        $system_config=include LY_BASEPATH."/ly/config.php";
        $user_config=is_file(LY_BASEPATH."/config/config.php")?include LY_BASEPATH."/config/config.php":array();
        defined("APP_PATH") or difine("APP_PATH",$system_config['app_path']);
        $app_path=defined("APP_PATH")?APP_PATH:$system_config['app_path'];
        $this->config = array_merge($system_config,$user_config);
        return $this->config;
    }
}