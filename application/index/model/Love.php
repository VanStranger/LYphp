<?php
namespace application\index\model;
use ly\lib\Model;
class Love extends Model
{
    public function gethername(){
        if($mySuccess){
            return "李阳";
        }else{
            return "ly";
        }
    }
}