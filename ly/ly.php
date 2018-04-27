<?php
namespace ly;
class ly
{
    // 配置内容
    protected $config = [];

    public function __construct()
    {
        $system_config=include SERVER_ROOT."/ly/config.php";
        $user_config=is_file(SERVER_ROOT."/config/config.php")?include SERVER_ROOT."/config/config.php":array();
        defined("APP_PATH") or difine("APP_PATH",$system_config['app_path']);
        $app_path=defined("APP_PATH")?APP_PATH:$system_config['app_path'];
        // $global_config_file=SERVER_ROOT.$app_path."/config.php";
        // if(is_file($global_config_file)){
        //     $global_config=include $global_config_file;
        // }else{
        //     $global_config=array();
        // }
        $this->config = array_merge($system_config,$user_config);
    }

    // 运行程序
    public function run()
    {
        // (new \ceshi())->run();

        $router=(new \ly\lib\router())->getRoute();
        if(!$router){
            $file= SERVER_ROOT . APP_PATH ."/".$this->config['default_module']."/controller/".$this->config['default_controller'].".php";
            if(is_file($file)){
                $controllerSpace= "\\".APP_PATH."\\".$this->config['default_module']."\\controller\\".$this->config['default_controller'];
                $action=$this->config['default_action'];
               (new $controllerSpace())->$action();
           }else{
                throw new \Exception('找不到控制器'."index\index");
           }
        }else{
             $file= SERVER_ROOT . APP_PATH ."/".$router[0]."/controller/".$router[1].".php";
             if(is_file($file)){
                 $controllerSpace= "\\".APP_PATH."\\".$router[0]."\\controller\\".$router[1];
                 $action=$router[2];
                (new $controllerSpace())->$action();
            }else{
                throw new \Exception('找不到控制器'.$router[0]."\\".$router[1]);
            }
        }
    }
}