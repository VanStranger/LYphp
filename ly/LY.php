<?php
namespace ly;
$Lyparameters=[];
class LY
{
    // 配置内容
    protected $config = [];

    public function getparams($c, $m){
        $method = new \ReflectionMethod($c, $m);
        $params = $method->getParameters();
        return $params;
    }
    public function getMethodAnnotation($class,$fn,$key){
        $ref = new \ReflectionMethod($class,$fn);
        $doc = $ref->getDocComment();
        $doc=ltrim($doc,"/**");
        $doc=rtrim($doc,"*/");
        $doc=preg_replace("/(\r\n|\n)\s+?\*\s/","$1",$doc);
        $doc=preg_replace("/\s*(\r\n|\n|$)/","$1",$doc);
        preg_match("/@".$key."\s(.+?)($|\r|\n)/",$doc,$matches);
        if($matches){
            return $matches[1];
        }else{
            return false;
        }
    }
    public function execClassMethod($controller,$action){
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
        return $res;
    }
    // 运行程序
    public function run()
    {
        $go=new \ly\lib\Exception();

        $this->config=(new lib\Config())->getConfig();
        define("LY_CONFIG",$this->config);
        defined("APP_PATH") or define("APP_PATH",$this->config['app_path']);
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
            if(method_exists($controllerSpace,"setConfig")){
                $controller->setConfig($this->config);
            }
            if(defined("M") && defined("C") && defined("A")){
                if(method_exists($controllerSpace,"assign")){
                    $controller->assign("Request",["m"=>M,"c"=>C,"a"=>A]);
                }
            }
            $res=null;
            $annotation=$this->getMethodAnnotation($controller,$action,"Before");
                if($annotation){
                    $res=$this->execClassMethod($controller,$annotation);    
                }
                if(is_null($res)){
                    $beforeArr=array_merge($controller->beforeActionList,$controller->hook);
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
                                // $res=$controller->$key();
                                $res=$this->execClassMethod($controller,$key);
                            }
                        }else{
                            if(A===$value){
                                continue;
                            }elseif(method_exists($controller,$value)  && is_null($res)){
                                // $res=$controller->$value();
                                $res=$this->execClassMethod($controller,$value);
                            }
                        }
                        if($res){
                            break;
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
                            $paramarr[$value->name]=null!==input($value->name)?input($value->name):$value->getDefaultValue();
                        }else{
                            if(!is_null(input($value->name)) || $this->config['params_strict']===0){
                                $paramarr[$value->name]=input($value->name);
                            }else{
                                throw new \Exception("函数".$action."缺少参数".$value->name, 1);
                            }
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
                // 
                if(DEBUG){
                    throw new \Exception('找不到控制器'.M."\\".ucfirst(C));
                }else{

                }
            }
    }
    public function execute($fn="",$p=[])
    {
        if(!$fn){
            return false;
        }else{
            $router=explode("/",trim($fn,"/"));
            if(count($router)<3){
                return false;
            }
        }
        $go=new \ly\lib\Exception();
        $this->config=(new lib\Config())->getConfig();
        $m=$router[0]?:$this->config['default_module'];
        $c=$router[1]?:$this->config['default_controller'];
        $action=$router[2]?:$this->config['default_action'];
        $common_file= LY_BASEPATH . APP_PATH ."/".$m."/common/common.php";
        include_once $common_file;
        $file= LY_BASEPATH . APP_PATH ."/".$m."/controller/".ucfirst($c).".php";
        if(is_file($file)){
            $controllerSpace= "\\".APP_PATH."\\".$m."\\controller\\".ucfirst($c);
            $controller=new $controllerSpace();
            if(method_exists($controllerSpace,"setConfig")){
                $controller->setConfig($this->config);
            }
            if(method_exists($controllerSpace,"assign")){
                $controller->assign("Request",["m"=>$m,"c"=>$c,"a"=>$action]);
            }


                $beforeArr=array_merge($controller->beforeActionList,$controller->hook);
                $res=null;
                foreach ($beforeArr as $key => $value) {
                    if(!is_numeric($key)){
                        if (array_key_exists("only",$value) && !in_array($a,$value['only'])){
                            continue;
                        }
                        if (array_key_exists("except",$value) && in_array($a,$value['except'])){
                            continue;
                        }
                        if($a===$key){
                            continue;
                        }elseif(method_exists($controller,$key) && is_null($res)){
                            $res=$controller->$key();
                        }
                    }else{
                        if($a===$value){
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
                            $paramarr[$value->name]=isset($p[$value->name])?$p[$value->name]:(input($value->name)?input($value->name):$value->getDefaultValue());
                        }else{
                            if(isset($p[$value->name]) || !is_null(input($value->name)) || $this->config['params_strict']===0){
                                $paramarr[$value->name]=isset($p[$value->name])?$p[$value->name]:input($value->name);
                            }else{
                                throw new \Exception("函数".$action."缺少参数".$value->name, 1);
                            }
                        }
                    }
                    $res=call_user_func_array(array($controller,$action),$paramarr);
                }
                return $res;
            }else{
                // 
                if(DEBUG){
                    throw new \Exception('找不到控制器'.$m."\\".ucfirst($c));
                }else{

                }
            }
    }
}
