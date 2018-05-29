<?php
namespace ly\lib;
class Controller{
    public $assign=null;
    public $config=[];
    public $hook= [
        'class'    => 'MyClass',
        'function' => 'Myfunction',
        'filename' => 'Myclass.php',
        'filepath' => '',
        'params'   => array('beer', 'wine', 'snacks')
    ];
    public function setConfig($config){
        $this->config=$config;
    }
    public function assign($name,$value){
        $this->assign[$name]=$value;
    }
    public function display($ly_view_file=""){
        $pathtype=$this->config['path_type'];
        $ly_view_file= BASEPATH . APP_PATH ."/".M."/view/".C. ($pathtype==0?"_":"/") .A.".php";
        if(!is_file($ly_view_file) ){
            throw new \Exception($ly_view_file."模板文件不存在。", 1);
        }else{
            if($this->assign){
                foreach ($this->assign as $key => $value) {
                    $$key=$value;
                }
            }
            include $ly_view_file;
        }
    }
    public function view($ly_view_file=""){
        $pathtype=$this->config['path_type'];
        $ly_view_file= BASEPATH . APP_PATH ."/".M."/view/".C.($pathtype==0?"_":"/").A.".html";
         if(is_file($ly_view_file)){                                 //判断有无该文件
             $loader = new \Twig_Loader_Filesystem(BASEPATH . APP_PATH ."/".M."/view/".($pathtype==0?"":C));
             $twig = new \Twig_Environment($loader, array(
                 'cache' => BASEPATH.'/runtime/cache',           //缓存文件路径
                 'debug'=>DEBUG
             ));
             $template = $twig->loadTemplate(($pathtype==0?C."_":"").A.".html");
             echo $template ->display($this->assign?$this->assign:[]);
         }
    }
}