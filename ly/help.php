<?php
use ly\lib\DB as DB;
if (!function_exists('input')) {
    function input($key = '', $default = null, $filter = ''){
        $key=str_replace("[]","",$key);
        if($key){
            global $Lyparameters;
            $rws_post = file_get_contents('php://input');
            $mypost = json_decode($rws_post,true);
            $value=($_GET[$key] ?? $_POST[$key] ?? $Lyparameters[$key] ?? $mypost[$key] ?? $default);
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
            return array_merge($_GET,$_POST);
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
if (!function_exists('blockhtml')) {
    function blockhtml($fun,$newfun) {
        if(function_exists($fun)){
            $fun();
        }else{
            $newfun();
        }
    }
}