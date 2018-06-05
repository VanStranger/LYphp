<?php
if (!function_exists('input')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
     * @return mixed
     */
    function input($key = '', $default = null, $filter = '')
    {

        if($key){
            global $Lyparameters;
            var_dump($Lyparameters);
            return htmlspecialchars(addslashes($_GET[$key] ?? $_POST[$key] ?? $Lyparameters[$key] ?? $default));
        }else{
            return array_merge($_GET,$_POST);
        }
    }
}
if (!function_exists('json')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
     * @return mixed
     */
    function json($array)
    {

        return json_encode($array,JSON_UNESCAPED_UNICODE);
    }
}