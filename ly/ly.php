<?php
namespace ly;
$Lyparameters=[];
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
    public function getparams($c, $m){
        $method = new \ReflectionMethod($c, $m);
        $params = $method->getParameters();
        return $params;
    }

    // 运行程序
    public function run()
    {
        $go=new \ly\lib\Exception();

        $this->config=(new lib\Config())->getConfig();
        define("LY_CONFIG",$this->config);
        $router=(new \ly\lib\Router())->getRoute();
        define("M",$router[0]?:$this->config['default_module']);
        define("C",$router[1]?:$this->config['default_controller']);
        define("A",$router[2]?:$this->config['default_action']);
            $common_file= BASEPATH . APP_PATH ."/".M."/common/common.php";
            include $common_file;
            $file= BASEPATH . APP_PATH ."/".M."/controller/".ucfirst(C).".php";
            if(is_file($file)){
                $controllerSpace= "\\".APP_PATH."\\".M."\\controller\\".ucfirst(C);
                $action=A;
                $controller=new $controllerSpace();
                $controller->setConfig($this->config);
                if(!method_exists($controller,$action)){
                   if(DEBUG){
                        throw new \Exception("方法不存在", 1);
                    }else{
                        return "";
                    }
                }
                $paramarr=[];
                $params=$this->getparams($controller,$action);
                foreach ($params as $key => $value) {
                    if($value->isDefaultValueAvailable()){
                        $paramarr[$value->name]=input($value->name)?:$value->getDefaultValue();
                    }else{
                        $paramarr[$value->name]=input($value->name);
                    }
                }
                $res=call_user_func_array(array($controller,$action),$paramarr);
                // $res=$controller->$action();
                if(is_array($res)){
                    echo json_encode($res,JSON_UNESCAPED_UNICODE);
                }elseif(is_string($res) or is_numeric($res)){
                    echo $res;
                }else{
                    if($res){
                        if(DEBUG){
                            throw new \Exception("返回类型应该为字符串或数组", 1);
                        }else{
                            return "";
                        }
                    }

                }
            }else{
                // Configure the PrettyPageHandler:
                if(DEBUG){

                    throw new \Exception('找不到控制器'.M."\\".ucfirst(C));
                }else{

                }
            }
    }
}