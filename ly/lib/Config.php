<?php
namespace ly\lib;
class Config{
    protected $config = [];
    public function getConfig(){
        $system_config=include LY_BASEPATH."/ly/config.php";
        $user_config=is_file(LY_BASEPATH."/config/config.php")?include LY_BASEPATH."/config/config.php":array();
        $this->config = array_merge($system_config,$user_config);
        return $this->config;
    }
}