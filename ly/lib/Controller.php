<?php
namespace ly\lib;

class Controller
{
    public $assign_arr = null;
    public $config     = [];
    public $whoops     = null;
    public $hook       = [
    ];
    public $beforeActionList = [];
    public $ly_pre           = null;
    public function setConfig($config)
    {
        $this->config = $config;
        $this->assign("config", $this->config);
    }
    public function redirect($url)
    {
        header("LOCATION:" . $url);
        exit();
    }
    public function assign($name, $value)
    {
        $this->assign_arr[$name] = $value;
    }
    public function display($a = "")
    {
        $pathtype     = $this->config['path_type'];
        $ly_view_file = LY_BASEPATH . APP_PATH . "/" . M . "/view/" . C . ($pathtype == 0 ? "_" : "/") . ($a ? $a : A) . ".php";
        if (!is_file($ly_view_file)) {
            throw new \Exception ($ly_view_file . "模板文件不存在。", 1);
        } else {
            if ($this->assign_arr) {
                // foreach ($this->assign_arr as $key => $value) {
                //     $$key=$value;
                // }
                extract($this->assign_arr);
            }
            include $ly_view_file;
        }
    }
    public function displayHtml($a = "")
    {
        $file = LY_BASEPATH . "runtime/cache/" . M . "_" . C . "_" . ($a ? $a : A) . ".php";
        if (!is_dir(LY_BASEPATH . "runtime/cache")) {
            mkdir(LY_BASEPATH . "runtime/cache/", 0755, true);
        }
        if ($this->assign_arr) {
            // foreach ($this->assign_arr as $key => $value) {
            //     $$key=$value;
            // }
            extract($this->assign_arr);
        }
        if (!is_file($file) || !$this->config['PRODUCTION_MODE']) {
            $pathtype     = $this->config['path_type'];
            $ly_view_file = LY_BASEPATH . APP_PATH . "/" . M . "/view/" . C . ($pathtype == 0 ? "_" : "/") . ($a ? $a : A) . ".html";
            if (!is_file($ly_view_file)) {
                throw new \Exception ($ly_view_file . "模板文件不存在。", 1);
            } else {
                $cont_temp = file_get_contents($ly_view_file);
                if (preg_match('/' . $this->config['template']['tpl_begin'] . '\s*extends\s+([^\s]+?)\s*' . $this->config['template']['tpl_end'] . '/', $cont_temp, $matches)) {
                    $basehtml = trim($matches[1], "\"'()");
                    if (in_array(substr($basehtml, 0, 1), ['/', '\\'])) {
                        $basehtml = LY_BASEPATH . "/public/" . $basehtml;
                    } else {
                        $basehtml = LY_BASEPATH . APP_PATH . "/" . M . "/view/" . $basehtml;
                    }
                    if (!is_file($basehtml)) {
                        throw new \Exception ($basehtml . "模板文件不存在。", 1);
                    } else {
                        $cont = file_get_contents($basehtml);
                        if (preg_match_all('/' . $this->config['template']['tpl_begin'] . '\s*block\s+([^\s]+?)\s*' . $this->config['template']['tpl_end'] . '([\s\S]+?)' . $this->config['template']['tpl_begin'] . '\s*endblock\s*' . $this->config['template']['tpl_end'] . '/', $cont, $cont_matches)) {
                            preg_match_all('/' . $this->config['template']['tpl_begin'] . '\s*block\s+([^\s]+?)\s*' . $this->config['template']['tpl_end'] . '([\s\S]+?)' . $this->config['template']['tpl_begin'] . '\s*endblock\s*' . $this->config['template']['tpl_end'] . '/', $cont_temp, $cont_temp_matches);
                            for ($i = 0, $len = count($cont_matches[1]); $i < $len; $i++) {
                                $replace_key = array_search($cont_matches[1][$i], $cont_temp_matches[1]);
                                if ($replace_key !== false) {
                                    $cont = str_replace($cont_matches[0][$i], $cont_temp_matches[2][$replace_key], $cont);
                                } else {
                                    $cont = str_replace($cont_matches[0][$i], $cont_matches[2][$i], $cont);
                                }
                            }

                        }
                    }
                } else {
                    $cont = $cont_temp;
                }
                //include
                preg_match_all('/' . $this->config['template']['tpl_begin'] . '\s*include\s*\'?\"?([^\'\"]+?)\'?\"?\s*' . $this->config['template']['tpl_end'] . '/', $cont, $includes);
                foreach ($includes[1] as $key => $value) {
                    $include_file = LY_BASEPATH . APP_PATH . "/" . M . "/view/" . $value;
                    if (!is_file($include_file)) {
                        throw new Exception("模板不存在，引入位置为" . $include_file, 1);
                    } else {
                        $includehtml = file_get_contents($include_file);
                        $cont        = str_replace($includes[0][$key], $includehtml, $cont);
                    }
                }

                //literal
                preg_match_all('/' . $this->config['template']['tpl_begin'] . '\s*literal\s*' . $this->config['template']['tpl_end'] . '([\s\S]+?)' . $this->config['template']['tpl_begin'] . '\s*endliteral\s*' . $this->config['template']['tpl_end'] . '/', $cont, $literals);
                if ($literals[0]) {
                    foreach ($literals[0] as $key => $value) {

                        $cont = str_replace($value, "tpl_space_letters_" . $key, $cont);
                    }
                }

                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*if (.+?)[\s\r\n]*' . $this->config['template']['tpl_end'] . '/', '<?php if ($1) { ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*else[\s\r\n]*' . $this->config['template']['tpl_end'] . '/', '<?php } else { ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*elseif (.+?)' . $this->config['template']['tpl_end'] . '/', '<?php } elseif ($1) { ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*endif[\s\r\n]*' . $this->config['template']['tpl_end'] . '/', '<?php } ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*foreach (.+?)' . $this->config['template']['tpl_end'] . '/', '<?php foreach ($1) { ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*endforeach[\s\r\n]*' . $this->config['template']['tpl_end'] . '/', '<?php } ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*include (.+?)' . $this->config['template']['tpl_end'] . '/', '<?php include $1; ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . '[\s\r\n]*(\$.+?)[\s\r\n]*' . $this->config['template']['tpl_end'] . '/', '<?php echo $1; ?>', $cont);
                $cont = preg_replace('/' . $this->config['template']['tpl_begin'] . 'php[\s\r\n]*(.+?)[\s\r\n]*php' . $this->config['template']['tpl_end'] . '/', '<?php $1 ?>', $cont);

                if ($literals[1]) {
                    foreach ($literals[1] as $key => $value) {

                        $cont = str_replace("tpl_space_letters_" . $key, $value, $cont);
                    }
                }
                file_put_contents($file, $cont);
            }
        }
        include $file;
    }
    public function displayTwig($ly_view_file = "")
    {
        $pathtype     = $this->config['path_type'];
        $ly_view_file = LY_BASEPATH . APP_PATH . "/" . M . "/view/" . C . ($pathtype == 0 ? "_" : "/") . A . ".html";
        if (is_file($ly_view_file)) { //判断有无该文件
            $loader = new \Twig_Loader_Filesystem (LY_BASEPATH . APP_PATH . "/" . M . "/view/");
            $twig   = new \Twig_Environment ($loader, array(
                'cache' => LY_BASEPATH . '/runtime/cache', //缓存文件路径
                'debug' => DEBUG,
            ));
            $template = $twig->loadTemplate(($pathtype == 0 ? C . "_" : C . "/") . A . ".html");
            echo $template->display($this->assign_arr ? $this->assign_arr : []);
        } else {
            throw new \Exception ($ly_view_file . "模板文件不存在。", 1);
        }
    }
}
