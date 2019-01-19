<?php
use ly\lib\DB as DB;
if (!function_exists('input')) {
    function input($key = '', $default = null, $filter = ''){
        if($key){
            global $Lyparameters;
            return $_GET[$key] ?? $_POST[$key] ?? $Lyparameters[$key] ?? $default;
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
        $fn="\application\index\model\\" .$m;
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