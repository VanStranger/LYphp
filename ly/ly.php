<?php
namespace ly;

class ly
{
    // 配置内容
    protected $config = [];
    public function __construct()
    {

        $system_config=include BASEPATH."/ly/config.php";
        $user_config=is_file(BASEPATH."/config/config.php")?include BASEPATH."/config/config.php":array();
        defined("APP_PATH") or difine("APP_PATH",$system_config['app_path']);
        $app_path=defined("APP_PATH")?APP_PATH:$system_config['app_path'];
        $this->config = array_merge($system_config,$user_config);
    }

    // 运行程序
    public function run()
    {
        if(DEBUG){
            $GLOBALS['whoops'] = new \Whoops\Run;
            $GLOBALS['whoops']->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $GLOBALS['whoops']->register();
        }else{
            ini_set('display_error','off');
        }
        $this->config=(new lib\Config())->getConfig();
        $router=(new \ly\lib\router())->getRoute();
        define("M",$router[0]?:$this->config['default_module']);
        define("C",$router[1]?:$this->config['default_controller']);
        define("A",$router[2]?:$this->config['default_action']);

            $file= BASEPATH . APP_PATH ."/".M."/controller/".C.".php";
            if(is_file($file)){
                $controllerSpace= "\\".APP_PATH."\\".M."\\controller\\".C;
                $action=A;
                $controller=new $controllerSpace();
                $controller->setConfig($this->config);
                $res=$controller->$action();
                if(is_array($res)){
                    echo json_encode($res);
                }elseif(is_string($res)){
                    echo $res;
                }else{
                    if($res){
                        throw new \Exception("返回类型应该为字符串或数组", 1);
                    }

                }
            }else{
                // Configure the PrettyPageHandler:
                $errorPage = new \Whoops\Handler\PrettyPageHandler();

                $errorPage->setPageTitle("It's broken!"); // Set the page's title
                $errorPage->addDataTable("Extra Info", array(
                    "stuff"     => 123,
                    "foo"       => "bar",
                    "useful-id" => "baloney"
                ));

                $GLOBALS['whoops']->pushHandler($errorPage);
                throw new \Exception('找不到控制器'.M."\\".C);
            }
    }
}