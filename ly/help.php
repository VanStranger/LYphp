<?php
use ly\lib\DB as DB;
if (!function_exists('input')) {
    function ly_get_all_headers(){
        // 忽略获取的header数据 
       $ignore = array('host','accept','content-length','content-type'); 
       $headers = array(); 
       foreach($_SERVER as $key=>$value){ 
        if(substr($key, 0, 5)==='HTTP_'){ 
         $key = substr($key, 5); 
         $key = str_replace('_', ' ', $key); 
         $key = str_replace(' ', '-', $key); 
         $key = strtolower($key); 
        //  if(!in_array($key, $ignore)){ 
           $headers[$key] = $value; 
        //  } 
       } 
       } 
       return $headers; 
    }
    function input($key = '', $default = null, $filter = ''){
        global $Lyparameters;
        $headers=ly_get_all_headers();
        $rws_post = file_get_contents('php://input');
        $mypost = json_decode($rws_post,true);
        $key=str_replace("[]","",$key);
        if($key){
            $value=($headers[$key] ?? $_GET[$key] ?? $_POST[$key] ?? $Lyparameters[$key] ?? $mypost[$key] ?? $default);
            if(is_numeric($value)){
                if(strstr($value,".")){
                    $value=floatval($value);
                }else{
                    $value=intval($value);
                }
            }
            if(is_string($value)){
                $value=htmlspecialchars($value);
            }
            return $value;
        }else{
            return array_merge([],(array)$Lyparameters,(array)$headers,(array)$_GET,(array)$_POST,(array)$mypost);
        }
    }
}
if (!function_exists('json')) {
    function json($array)
    {
        return json_encode($array,JSON_UNESCAPED_UNICODE);
    }
}
if (!function_exists('db')) {
    function db($table)
    {
        return DB::table($table);
    }
}
if (!function_exists('model')) {
    function model($m)
    {
        $trace =debug_backtrace();
        $path=explode("\\",$trace[1]['class']);
        $fn="\\".$path[0]."\\".$path[1]."\\model\\" .$m;
        return (new $fn());
    }
}
if (!function_exists('config')) {
    function config($m)
    {
        if($m){

            return LY_CONFIG[$m];
        }else{
            return LY_CONFIG;
        }
    }
}
if (!function_exists('gethref')) {
    function gethref($url="",$params=[]){
        // gethref(array("./notes.php",array("page"=>now+1)));
        $url=$url?:$_SERVER['REQUEST_URI'];
        $query=parse_url($url)['query'];
        $baseUrl=parse_url($url)['path'];
        parse_str($query?:"",$ar);
        foreach ($params as $key => $value) {
            $ar[$key]=$value;
        }
        return $baseUrl."?".http_build_query($ar);
    }
}
if (!function_exists('blockhtml')) {
    function blockhtml($fun,$newfun) {
        if(function_exists($fun)){
            $fun();
        }else{
            $newfun();
        }
    }
}