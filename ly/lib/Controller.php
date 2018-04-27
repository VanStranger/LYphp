<?php
namespace ly\lib;
class Controller{
    public $assign;
    public function assign($name,$value){
        $this->assign[$name]=$value;
    }
    public function display($file){

    }
}