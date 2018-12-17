<?php
namespace ly\lib;
class Controller{
    public $assign_arr=null;
    public $config=[];
    public $whoops=null;
    public $hook= [
    ];
    protected $beforeActionList=[];
    protected $ly_pre=[];
    public function __construct(){
        $beforeArr=array_merge($this->beforeActionList,$this->hook);
        foreach ($beforeArr as $key => $value) {
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
        $this->assign_arr[$name]=$value;
    }
    public function display($ly_view_file=""){
        $pathtype=$this->config['path_type'];
        $ly_view_file= BASEPATH . APP_PATH ."/".M."/view/".C. ($pathtype==0?"_":"/") .A.".php";
        if(!is_file($ly_view_file) ){
            throw new \Exception($ly_view_file."模板文件不存在。", 1);
        }else{
            if($this->assign_arr){
                // foreach ($this->assign_arr as $key => $value) {
                //     $$key=$value;
                // }
                extract($this->assign_arr);
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
            if($this->assign_arr){
                // foreach ($this->assign_arr as $key => $value) {
                //     $$key=$value;
                // }
                extract($this->assign_arr);
            }
            $cont_temp=file_get_contents($ly_view_file);
            if(preg_match('/'.$this->config['template']['tpl_begin'].'\s*extends\s+([^\s]+?)\s*'.$this->config['template']['tpl_end'].'/',$cont_temp,$matches)){
                $basehtml=trim($matches[1],"\"'()");
                if(in_array(substr($basehtml,0,1),['/','\\'])){
                    $basehtml=BASEPATH."/public/".basehtml;
                }else{
                    $basehtml=BASEPATH . APP_PATH ."/".M."/view/".$basehtml;
                }
                if(!is_file($basehtml) ){
                    throw new \Exception($basehtml."模板文件不存在。", 1);
                }else{
                    $cont=file_get_contents($basehtml);
                    if(preg_match_all('/'.$this->config['template']['tpl_begin'].'\s*block\s+([^\s]+?)\s*'.$this->config['template']['tpl_end'].'([\s\S]+?)'.$this->config['template']['tpl_begin'].'\s*endblock\s*'.$this->config['template']['tpl_end'].'/',$cont,$cont_matches)){
                        preg_match_all('/'.$this->config['template']['tpl_begin'].'\s*block\s+([^\s]+?)\s*'.$this->config['template']['tpl_end'].'([\s\S]+?)'.$this->config['template']['tpl_begin'].'\s*endblock\s*'.$this->config['template']['tpl_end'].'/',$cont_temp,$cont_temp_matches);
                        for($i=0,$len=count($cont_matches[1]);$i<$len;$i++){
                            $replace_key=array_search($cont_matches[1][$i],$cont_temp_matches[1]);
                            if($replace_key!==false){
                                $cont=str_replace($cont_matches[0][$i],$cont_temp_matches[2][$replace_key],$cont);
                            }else{
                                $cont=str_replace($cont_matches[0][$i],$cont_matches[2][$i],$cont);
                            }
                        }


                    }
                }
            }else{
                $cont=$cont_temp;
            }
            preg_match_all('/'.$this->config['template']['tpl_begin'].'\s*literal\s*'.$this->config['template']['tpl_end'].'([\s\S]+?)'.$this->config['template']['tpl_begin'].'\s*endliteral\s*'.$this->config['template']['tpl_end'].'/',$cont,$literals);
            if($literals[0]){
                foreach ($literals[0] as $key => $value) {

                   $cont= str_replace($value,"tpl_space_letters_".$key,$cont);
                }
            }
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*if (.+?)[\s\r\n]*'.$this->config['template']['tpl_end'].'/', '<?php if ($1) { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*else[\s\r\n]*'.$this->config['template']['tpl_end'].'/', '<?php } else { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*elseif (.+?)'.$this->config['template']['tpl_end'].'/', '<?php } elseif ($1) { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*endif[\s\r\n]*'.$this->config['template']['tpl_end'].'/', '<?php } ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*foreach (.+?)'.$this->config['template']['tpl_end'].'/', '<?php foreach ($1) { ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*endforeach[\s\r\n]*'.$this->config['template']['tpl_end'].'/', '<?php } ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*include (.+?)'.$this->config['template']['tpl_end'].'/', '<?php include $1; ?>', $cont);
            $cont = preg_replace('/'.$this->config['template']['tpl_begin'].'[\s\r\n]*(\$.+?)[\s\r\n]*'.$this->config['template']['tpl_end'].'/', '<?php echo $1; ?>', $cont);
             if($literals[1]){
               foreach ($literals[1] as $key => $value) {

                    $cont=str_replace("tpl_space_letters_".$key,$value,$cont);
                }
             }
            $file=BASEPATH ."runtime/cache/".M."_".C."_".A.".php";
            if(!is_dir(BASEPATH ."runtime/cache")){
                mkdir(BASEPATH ."runtime/cache/",0755,true);
            }
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