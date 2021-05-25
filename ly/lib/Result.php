<?php
namespace ly\lib;
class Result{
  static protected $result=[
    'code'=>0,
    "data"=>null,
    "message"=>"",
    "other"=>""
  ];
  static public function success($data=null,$message="",$other=""){
    $res=array_merge(self::$result,['data'=>$data,'message'=>$message,'other'=>$other]);
    return $res;
  }
  static public function fail($message="",$code=1,$data=null,$other=""){
    $res=array_merge(self::$result,['code'=>$code,'data'=>$data,'message'=>$message,'other'=>$other]);
    return $res;
  }
}