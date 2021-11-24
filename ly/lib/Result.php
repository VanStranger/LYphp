<?php
namespace ly\lib;
class Result{
  static protected $result=[
    'code'=>0,
    'state'=>1,
    "data"=>null,
    "message"=>"",
    "other"=>""
  ];
  static public function success($data=null,$message="",$other=""){
    $res=array_merge(self::$result,['data'=>$data,'message'=>$message,'other'=>$other]);
    return $res;
  }
  static public function fail($message="",$code=1,$data=null,$other=""){
    $res=array_merge(self::$result,['code'=>$code,'state'=>0,'data'=>$data,'message'=>$message,'other'=>$other]);
    return $res;
  }
  static public function code($code=0,$res=[]){
    $res=array_merge(self::$result,['code'=>$code],$res);
    return $res;
  }
}