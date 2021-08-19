<?php
namespace ly\lib;
class Hongmeng {
  public static $scripts=[];
  public static $styles=[];
  public static function addScript($function){
    self::$scripts[]=$function;
  }
  public static function addStyle($function){
    self::$styles[]=$function;
  }
  public static function showScripts(){
    foreach (self::$scripts as $key => $value) {
      $value();
    }
  }
  public static function showStyles(){
    foreach (self::$styles as $key => $value) {
      $value();
    }
  }
  public static function getStr($str){
    list($usec, $sec) = explode(" ", microtime()); 
    $microtimestr=$sec.substr($usec,2);
    return $str.$microtimestr.rand(0,999999);
  }
  public static function scopeStyle($file,$str){
    $cont=file_get_contents($file);
    // $cont=str_replace("{"," [hongmeng-$str]{",$cont);
    preg_match_all("/([^\r\n]+)\{/",$cont,$matches);
    foreach ($matches[1] as $key => $value) {
      if(strstr($value,",")!==false){
        $strarr=explode(",",$value);
        foreach ($strarr as $k => $s) {
          $strarr[$k]=str_replace($s,(rtrim($s)."[hongmeng-$str]"),$s);
        }
        $tostr=implode(",",$strarr);
        $cont=str_replace($value."{",$tostr."{",$cont);
      }else{
        $cont=str_replace($value."{",rtrim($value)."[hongmeng-$str]{",$cont);
      }
    }
    self::addStyle(function()use($cont){
      echo "<style>\r\n$cont</style>";
    });
  }
  public static function render($json,$str,$mode_add=false){
    static $html="";
    if(!$mode_add){
      $html="";
    }
    foreach ($json as $name => $prop) {
      if(is_numeric($name) && is_string($prop)){
        $html.=$prop;
      }elseif(is_numeric($name) && is_array($prop) && $prop['tag']){
        $html.="<".$prop['tag']." ";
        foreach ($prop as $key => $value) {
          $key=strtolower($key);
          if($key!=="tag" && $Key!=="children"){
            $html.=$key.'="'.$value.'" ';
          }
        }
        $html.=" hongmeng-$str >\r\n";
        if($prop['children']){
            self::render($prop['children'],$str,true);
        }
        $html.='</'.$prop['tag'].">\r\n";
      }
    }
    return $html;
  }
}