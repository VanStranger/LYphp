<?php
namespace ly\lib;
class Controller{
    public $assign=null;
    public $config=[];
    public $whoops=null;
    public $hook= [
        'class'    => 'MyClass',
        'function' => 'Myfunction',
        'filename' => 'Myclass.php',
        'filepath' => '',
        'params'   => array('beer', 'wine', 'snacks')
    ];
    protected $beforeActionList=[];
    protected $ly_pre=[];
    public function __construct(){
        foreach ($this->beforeActionList as $key => $value) {
            if(!is_numeric($key)){
                if (array_key_exists("only",$value) && !in_array(A,$value['only'])){
                    continue;
                }
                if (array_key_exists("except",$value) && in_array(A,$value['except'])){
                    continue;
                }
                if(method_exists($this,$key)){
                    $this->ly_pre[]=$this->$key();
                }
            }else{
                if(method_exists($this,$value)){
                    $this->ly_pre[]=$this->$value();
                }
            }
        }
        $this->assign("Request",["m"=>M,"c"=>C,"a"=>A]);
    }
    public function setConfig($config){
        $this->config=$config;
        $this->assign("config",$this->config);
    }
    public function redirect($url){
        header("LOCATION:".$url);
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
    public function displayHtml($ly_view_file=""){
        $pathtype=$this->config['path_type'];
        $ly_view_file= BASEPATH . APP_PATH ."/".M."/view/".C. ($pathtype==0?"_":"/") .A.".html";
        if(!is_file($ly_view_file) ){
            throw new \Exception($ly_view_file."模板文件不存在。", 1);
        }else{
            if($this->assign){
                foreach ($this->assign as $key => $value) {
                    $$key=$value;
                }
            }
            $cont=file_get_contents($ly_view_file);
            preg_match_all('/'.$this->config['template']['tpl_begin'].'\s*literal\s*'.$this->config['template']['tpl_end'].'([\s\S]+?)'.$this->config['template']['tpl_begin'].'\s*endliteral\s*'.$this->config['template']['tpl_end'].'/',$cont,$literals);
            if($literals[0]){
                foreach ($literals[0] as $key => $value) {

                   $cont= str_replace($value,"tpl_space_letters_".$key,$cont);
                }
            }
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'if (.+?)'.$this->config['template']['tpl_end'].'/', '<?php if ($1) { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'else'.$this->config['template']['tpl_end'].'/', '<?php } else { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'elseif (.+?)'.$this->config['template']['tpl_end'].'/', '<?php } elseif ($1) { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'endif'.$this->config['template']['tpl_end'].'/', '<?php } ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'foreach (.+?)'.$this->config['template']['tpl_end'].'/', '<?php foreach ($1) { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'endforeach'.$this->config['template']['tpl_end'].'/', '<?php } ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'include (.+?)'.$this->config['template']['tpl_end'].'/', '<?php include $1; ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'\s*(\$.+?)\s*'.$this->config['template']['tpl_end'].'/', '<?php echo $1; ?>', $cont);
             if($literals[1]){
               foreach ($literals[1] as $key => $value) {

                    $cont=str_replace("tpl_space_letters_".$key,$value,$cont);
                }
             }
            $file=BASEPATH ."runtime/cache/".M."_".C."_".A.".php";
            file_put_contents($file,$cont);
            include $file;
            // echo eval($cont);
        }
    }
    public function displayTwig($ly_view_file=""){
        $pathtype=$this->config['path_type'];
        $ly_view_file= BASEPATH . APP_PATH ."/".M."/view/".C.($pathtype==0?"_":"/").A.".html";
        if(is_file($ly_view_file)){                                 //判断有无该文件
            $loader = new \Twig_Loader_Filesystem(BASEPATH . APP_PATH ."/".M."/view/");
            $twig = new \Twig_Environment($loader, array(
                'cache' => BASEPATH.'/runtime/cache',           //缓存文件路径
                'debug'=>DEBUG
            ));
            $template = $twig->loadTemplate(($pathtype==0?C."_":C."/").A.".html");
            echo $template ->display($this->assign?$this->assign:[]);
        }else{
            throw new \Exception($ly_view_file."模板文件不存在。", 1);
         }
    }
}