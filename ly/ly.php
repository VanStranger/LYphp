<?php
namespace ly;
$Lyparameters=[];
class LY
{
    // 配置内容
    protected $config = [];
    public function __construct()
    {

        $system_config=include LY_BASEPATH."/ly/config.php";
        $user_config=is_file(LY_BASEPATH."/config/config.php")?include LY_BASEPATH."/config/config.php":array();
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
        $common_file= LY_BASEPATH . APP_PATH ."/".M."/common/common.php";
        include $common_file;
        $file= LY_BASEPATH . APP_PATH ."/".M."/controller/".ucfirst(C).".php";
        if(is_file($file)){
            $controllerSpace= "\\".APP_PATH."\\".M."\\controller\\".ucfirst(C);
            $action=A;
            $controller=new $controllerSpace();
            $controller->setConfig($this->config);
            if(defined("M") && defined("C") && defined("A")){
                $controller->assign("Request",["m"=>M,"c"=>C,"a"=>A]);
            }

                $beforeArr=array_merge($controller->beforeActionList,$controller->hook);
                $res=null;
                foreach ($beforeArr as $key => $value) {
                    if(!is_numeric($key)){
                        if (array_key_exists("only",$value) && !in_array(A,$value['only'])){
                            continue;
                        }
                        if (array_key_exists("except",$value) && in_array(A,$value['except'])){
                            continue;
                        }
                        if(A===$key){
                            continue;
                        }elseif(method_exists($controller,$key) && is_null($res)){
                            $res=$controller->$key();
                        }
                    }else{
                        if(A===$value){
                            continue;
                        }elseif(method_exists($controller,$value)  && is_null($res)){
                            $res=$controller->$value();
                        }
                    }
                }

                if(is_null($res)){
                    if(!method_exists($controller,$action)){
                       if(DEBUG){
                            throw new \Exception($file."中 '".$action."' 方法不存在", 1);
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
                }
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