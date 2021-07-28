<?php
namespace ly\lib;

class PageHelper
{
  public static $pageNames=[
    "pageNum"=>"pageNum",
    "pageSize"=>"pageSize",
    "total"=>"total",
    "pages"=>"pages",
    "data"=>"data",
  ];
  public static function setColumn($column){
    if(is_array($column)){
      self::$pageNames=array_merge(self::$pageNames,$column);
    }
  }
  public static function init($data,$total,$size=0){
    $returndata=[];
    $returndata[self::$pageNames['data']]=$data;
    $returndata[self::$pageNames['total']]=$total;
    if($size){
      $returndata[self::$pageNames['pages']]=ceil($total/$size);
    }
    return $returndata;
  }
}