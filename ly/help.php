<?php
use ly\lib\DB as DB;
if (!function_exists('input')) {
    function input($key = '', $default = null, $filter = ''){
        if($key){
            global $Lyparameters;
            return htmlspecialchars(addslashes($_GET[$key] ?? $_POST[$key] ?? $Lyparameters[$key] ?? $default));
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