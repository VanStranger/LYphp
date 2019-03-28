<?php
namespace ly\lib;
class DB{
    static public $conn="";
    static public $pdo;
    static public $instance;
    static public $datatype;
    private $tablename="";
    private $joinSql="";
    private $fieldSql="";
    private $updateSql="";
    private $updateParams=[];
    private $whereSql="";
    private $whereParams=[];
    private $orderSql="";
    private $groupSql="";
    private $havingSql="";
    private $limitSql="";
    private $limitParams=[];
    static private $sql="";
    static private $params=[];
    private function __construct() {
        $dbconfigs=include LY_BASEPATH."/config/database.php";
        if(!self::$conn){
            self::$conn="db";
        }
        $dbconfig=$dbconfigs[self::$conn];
        self::$datatype=$dbconfig['type'];
        self::$pdo=PDO::getinstance($dbconfig,self::$conn);
    }

    static public function getPDO(){
        return self::$pdo;
    }
    static public function getConn(){
        return self::$pdo->getConn();
    }
    public function lastInsertId(){
        return self::$pdo->getConn()->lastInsertId();
    }
    static public function beginTrans(){
        if(!self::$pdo){
            new self();
        }
		self::$pdo->beginTrans();
	}
	static public function commit(){
		self::$pdo->commit();
	}
	static public function rollBack(){
		self::$pdo->rollBack();
	}
    public function reset(){
        $this->tablename="";
        $this->joinSql="";
        $this->fieldSql="";
        $this->updateSql="";
        $this->updateParams=[];
        $this->whereSql="";
        $this->whereParams=[];
        $this->orderSql="";
        $this->limitSql="";
        $this->limitParams=[];
        // $this::$sql="";
        // $this::$params=[];
    }
    static function connect($table){
        if (!self::$instance instanceof self) {
            self::$conn=$table;
            self::$instance = new self();
        }elseif(self::$conn!==$table){
            $dbconfigs=include LY_BASEPATH."/config/database.php";
            self::$conn=$table;
            $dbconfig=$dbconfigs[self::$conn];
            self::$pdo=PDO::getinstance($dbconfig,self::$conn);
        }
        return self::$instance;
    }
    static function closeConn(){
        self::$conn="";
        DB::$pdo->closeInstance();
        DB::$pdo=null;
        self::$instance=null;
    }
    static function table($name){
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }elseif(self::$conn!=="db"){
            $dbconfigs=include LY_BASEPATH."/config/database.php";
            if(!self::$conn){
                self::$conn="db";
            }
            $dbconfig=$dbconfigs[self::$conn];
            self::$pdo=PDO::getinstance($dbconfig,self::$conn);
        }
        $db=self::$instance;
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
    public function field($param){
        if(is_string($param)){
            $this->fieldSql=trim($param);
        }elseif(is_array($param)){
            foreach ($param as $key => $value) {
                if(is_numeric($key)){
                    $this->fieldSql.=" ".$value." ,";
                }else{
                    $this->fieldSql.=" ".$key." as ".$value." ,";
                }
            }
            $this->fieldSql=substr($this->fieldSql,0,-1);
        }
        return $this;
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
                }else{
                    $this->whereSql=sprintf(" where %s =? ",$key);
                }
                $this->whereParams[]=$value;
            }
        }elseif(is_string($where)){
            if($param1===""){
                if($this->whereSql){
                    $this->whereSql.=sprintf(" and %s",$where);
                }else{
                    $this->whereSql=sprintf(" where %s",$where);
                }
            }elseif($param2===""){
                if($this->whereSql){
                    $this->whereSql.=" and ";
                }else{
                    $this->whereSql=" where ";
                }
                if(is_array($param1)){
                    $this->whereSql.=$where;
                    $this->whereParams=array_merge($this->whereParams,$param1);
                }else{
                    $this->whereSql.=$where."=?";
                    $this->whereParams[]=$param1;
                }
            }
        }elseif(is_callable($where,true)){
            call_user_func($where,$this);
        }
        return $this;
    }
    public function group($group){
        if(is_string($group)){
            $this->groupSql=$this->groupSql?",".$group:" group by ".$group;
        }
        return $this;
    }
    public function having($having){
        if(is_string($having)){
            $this->havingSql=$having;
        }
        return $this;
    }
    public function order($order){
        if(is_string($order)){
            $this->orderSql=$this->orderSql?",".$order:" order by ".$order;
        }
        return $this;
    }
    public function limit($start,$end=null){
        if(self::$datatype=="mysql"){
            if($end===null){
                $this->limitSql=" limit ? ";
                $this->limitParams=[$start];
            }else{
                $this->limitSql=" limit ?,? ";
                $this->limitParams=[$start,$end];
            }
        }elseif(self::$datatype=="oci"){
            $this->limitParams=[$end,$start];
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
        $this::$sql="INSERT INTO ".$this->tablename.$insertSql;
        $this::$params=array_merge($insertParams);
        $res=DB::$pdo->query($this::$sql,$this::$params);
        $this->reset();
        return $res;
    }
    public function delete($force=0){
        if(!$force && !$this->whereSql){
            throw new \Exception("this will delete with no 'where',we has forbidden it.");
        }
        $this::$sql="DELETE FROM ".$this->tablename.$this->whereSql.$this->orderSql.$this->limitSql;
        $this::$params=array_merge($this->whereParams,$this->limitParams);
        $res=DB::$pdo->query($this::$sql,$this::$params);
        $this->reset();
        return $res;
    }
    public function update($param,$param1=[]){
        if(is_array($param)){
            foreach ($param as $key => $value) {
                if(!is_array($value)){
                    $this->updateSql.=$key."=?,";
                    $this->updateParams[]=$value;
                }else{
                    $keys=array_keys($value);
                    if(is_numeric($keys[0])){
                        if(count($keys)>1){
                            $v=$value[$keys[0]];
                            $this->updateSql.=$key."=".$v."?,";
                            $this->updateParams[]=$value[$keys[1]];
                        }else{
                            $v=$value[$keys[0]];
                            $this->updateSql.=$key."=".$v .",";
                        }
                    }else{
                        $v=$keys[0];
                        $this->updateSql.=$key."=".$v."?,";
                        $this->updateParams[]=$value[$keys[0]];
                    }
                }
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
        $this::$sql="update ".$this->tablename." set ".$this->updateSql.$this->whereSql.$this->limitSql;
        $this::$params=array_merge($this->updateParams,$this->whereParams,$this->limitParams);
        $res=DB::$pdo->query($this::$sql,$this::$params);
        $this->reset();
        return $res;
    }
    public function buildSql(){
        $this::$sql="SELECT ". ($this->fieldSql?:"*") ." from ".$this->tablename.$this->joinSql.$this->whereSql.$this->groupSql.$this->havingSql.$this->orderSql.$this->limitSql;
        $this::$params=array_merge($this->updateParams,$this->whereParams,$this->limitParams);
        $this->reset();
        return [$this::$sql,$this::$params];
    }
    public function select(){
        if(self::$datatype=="mysql"){
            $this::$sql="SELECT ". ($this->fieldSql?:"*") ." from ".$this->tablename.$this->joinSql.$this->whereSql.$this->groupSql.$this->havingSql.$this->orderSql.$this->limitSql;
            $this::$params=array_merge($this->whereParams,$this->limitParams);
        }elseif(self::$datatype=="oci"){
            if($this->limitParams){
                if($this->limitParams[0]){
                    $this::$sql="SELECT * from "."(SELECT A.*,ROWNUM RN  from "."(SELECT ". ($this->fieldSql?:"*") ." from ".$this->tablename.$this->joinSql.$this->whereSql.$this->groupSql.$this->havingSql.$this->orderSql.") A where ROWNUM<=?) where RN>?";
                    $this::$params=array_merge($this->whereParams,$this->limitParams);
                }else{
                    $this::$sql="SELECT A.*  from "."(SELECT ". ($this->fieldSql?:"*") ." from ".$this->tablename.$this->joinSql.$this->whereSql.$this->groupSql.$this->havingSql.$this->orderSql.") A where ROWNUM <=?";
                    $this::$params=array_merge($this->whereParams,[$this->limitParams[1]]);
                }
            }else{
                $this::$sql="SELECT ". ($this->fieldSql?:"*") ." from ".$this->tablename.$this->joinSql.$this->whereSql.$this->groupSql.$this->havingSql.$this->orderSql;
                $this::$params=array_merge($this->whereParams);
            }
        }
        $res=DB::$pdo->query($this::$sql,$this::$params);
        $this->reset();
        return $res;
    }
    public function find(){
        if(self::$datatype=="mysql"){
            $this->limitSql=" limit 1 ";
        }elseif(self::$datatype=="oci"){
            $this->limitParams=[null,1];
        }
        $arr=$this->select();
        return $arr?$arr[0]:false;
    }
    public function count(){
        $this::$sql="SELECT 1 from ".$this->tablename.$this->joinSql.$this->whereSql.$this->limitSql;
        $this::$params=array_merge($this->updateParams,$this->whereParams,$this->limitParams);
        $sql=$this::$sql;
        $res=DB::$pdo->query($this::$sql,$this::$params);
        $this->reset();
        return $res?count($res):0;
    }
    static public function query($sql,$params=[]){
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        $res=DB::$pdo->query($sql,$params);
        return $res;
    }
    static public function getSql(){
        return self::$sql;
    }
    static public function getParams(){
        return self::$params;
    }
}