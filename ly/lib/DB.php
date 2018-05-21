<?php
namespace ly\lib;
class DB{
    public $config;
    public $pdo;
    private $tablename="";
    private $joinSql="";
    private $fieldSql="";
    private $updateSql="";
    private $updateParams=[];
    private $whereSql="";
    private $whereParams=[];
    private $orderSql="";
    private $limitSql="";
    private $limitParams=[];
    private $sql="";
    private $params=[];
    public function __construct() {
        $this->config=(new Config())->getConfig();
        $this->pdo=PDO::getinstance($this->config['db']);
    }
    static function table($name){
        $db=new self();
        if(is_string($name)){
            $db->tablename=$name;
        }elseif(is_array($name) && count($name)==1){
            $key=key($name);
            $value=$name[$key];
            $db->tablename=$key." as ".$value;
        }else{
            throw new \Exception("table方法的参数应当是一个字符串或者一个数组", 1);
        }
        return $db;
    }
    public function join($table,$condition,$option="inner"){
        $option=strtoupper($option);
        if(!in_array($option,['INNER','LEFT',"RIGHT"])){
            $option="INNER";
        }
        if(is_string($table)){
            $jointableSql=$table;
        }elseif(is_array($table) && count($table)==1){
            $key=key($table);
            $value=$table[$key];
            $jointableSql=$key." as ".$value;
        }else{
            throw new \Exception("join方法的第一个参数应当是一个字符串或者一个数组", 1);
        }
        if(is_string($condition)){
            $conditionSql=$condition;
        }else{
            throw new \Exception("join方法的第二个参数应当是一个字符串,( like: a.userid=b.userid)", 1);
        }
        $this->joinSql.=" ".$option." join ".$jointableSql." on ".$condition;
        return $this;
    }
    public function where($where,$param1="",$param2=""){
        if(is_array($where)){
            foreach ($where as $key => $value) {
                if($this->whereSql){
                    $this->whereSql.=sprintf(" and %s =? ",$key);
                }
                $this->whereSql=sprintf(" where %s =? ",$key);
                $this->whereParams[]=$value;
            }
        }elseif(is_string($where)){
            if(!$param1){
                if($this->whereSql){
                    $this->whereSql.=sprintf(" and %s",$where);
                }
                $this->whereSql=sprintf(" where %s",$where);
            }elseif(!$param2){
                if($this->whereSql){
                    $this->whereSql.=" and ";
                }else{
                    $this->whereSql=" where ";
                }
                $this->whereSql.=$where." =? ";
                if(is_array($param1)){
                    $this->whereParams=array_merge($this->whereParams,$param1);
                }else{
                    $this->whereParams[]=$param1;
                }
            }
        }elseif(is_callable($where,true)){
            call_user_func($where,$this);
        }
        return $this;
    }
    public function order($order){
        if(is_string($order)){
            $this->orderSql=$order;
        }
        return $this;
    }
    public function limit($start,$end=null){
        if(!$end){
            $this->limitSql=" limit ? ";
            $this->limitParams=[$start];
        }else{
            $this->limitSql=" limit ?,? ";
            $this->limitParams=[$start,$end];
        }
        return $this;
    }
    public function insert($param1,$param2=[]){
        $insertSql=" ";
        $insertParams=[];
        if(is_array($param1)){
            $iSql1="(";$iSql2="(";
            foreach ($param1 as $key => $value) {
                $iSql1.=$key.",";
                $iSql2.="?,";
                $insertParams[]=$value;
            }
            $iSql1=substr($iSql1,0,-1).")";
            $iSql2=substr($iSql2,0,-1).")";
            $insertSql= $iSql1." values ".$iSql2;
        }elseif(is_string($param1)){
            $insertSql.=$param1;
            if(isarray($param2)){
                $insertParams=$param2;
            }
        }
        $this->sql="INSERT INTO ".$this->tablename.$insertSql;
        $this->params=array_merge($insertParams);
        $res=$this->pdo->query($this->sql,$this->params);
        return $res;
    }
    public function delete($force=0){
        if(!$force && !$this->whereSql){
            throw new \Exception("this will delete with no 'where',we has forbidden it.");
        }
        $this->sql="DELETE FROM ".$this->tablename.$this->whereSql.$this->orderSql.$this->limitSql;
        $this->params=array_merge($this->whereParams,$this->limitParams);
        $res=$this->pdo->query($this->sql,$this->params);
        return $res;
    }
    public function update($param,$param1=[]){
        if(is_array($param)){
            foreach ($param as $key => $value) {
                $this->updateSql.=$key."=?,";
                $this->updateParams[]=$value;
            }
            $this->updateSql=substr($this->updateSql,0,-1);
        }elseif(is_string($param)){
            if(!$param1){
                $this->updateSql.=$param;
            }elseif(is_array($param1)){
                $this->updateSql.=$param;
                $this->updateParams=array_merge($this->updateParams,$param1);
            }else{
                return 0;
            }
        }
        $this->sql="update ".$this->tablename." set ".$this->updateSql.$this->whereSql.$this->limitSql;
        $this->params=array_merge($this->updateParams,$this->whereParams,$this->limitParams);
        $res=$this->pdo->query($this->sql,$this->params);
        return $res;
    }
    public function select(){
        $this->sql="SELECT ". ($this->fieldSql?:"*") ." from ".$this->tablename.$this->joinSql.$this->whereSql.$this->limitSql;
        $this->params=array_merge($this->updateParams,$this->whereParams,$this->limitParams);
        try{
            $sql=$this->sql;
            $res=$this->pdo->query($this->sql,$this->params);
        }catch(\Exception $e){
            throw new \Exception("sql执行错误，sql为".$this->sql."，参数为".json_encode($this->params), 1);
        }
        return $res;
    }
}