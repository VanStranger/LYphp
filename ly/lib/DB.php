<?php
namespace ly\lib;

class DB
{
    public static $conn = "";
    public static $pdo;
    public static $instance;
    public static $datatype;
    public static $dbconfig;
    private $tablename = "";
    private $tableParams = [];
    private $joinSql = "";
    private $joinParams = [];
    private $fieldSql = "";
    private $updateSql = "";
    private $updateParams = [];
    private $whereSql = "";
    private $whereParams = [];
    private $orderSql = "";
    private $groupSql = "";
    private $havingSql = "";
    private $limitSql = "";
    private $limitParams = [];
    private static $sql = "";
    private static $params = [];
    private function __construct()
    {
        $dbconfigs = include LY_BASEPATH . "/config/database.php";
        if (!self::$conn) {
            self::$conn = "db";
        }
        if (!key_exists("type", $dbconfigs[self::$conn])) {
            $dbconfigs[self::$conn]['type'] = 'mysql';
        }
        self::$dbconfig = $dbconfigs[self::$conn];
        self::$dbconfig['prefix'] = isset(self::$dbconfig['prefix']) ? self::$dbconfig['prefix'] : "";
        self::$datatype = self::$dbconfig['type'];
        self::$pdo = PDO::getinstance(self::$dbconfig, self::$conn);
    }
    public static function getDatatype()
    {
        if (self::$datatype) {
            return self::$datatype;
        } else {
            $dbconfigs = include LY_BASEPATH . "/config/database.php";
            if (!self::$conn) {
                self::$conn = "db";
            }
            if (!key_exists("type", $dbconfigs[self::$conn])) {

                $dbconfigs[self::$conn]['type'] = 'mysql';
            }
            self::$dbconfig = $dbconfigs[self::$conn];
            self::$datatype = self::$dbconfig['type'];
        }
        return self::$datatype;
    }
    public static function getDbconfig()
    {
        if (self::$dbconfig) {
            return self::$dbconfig;
        } else {
            $dbconfigs = include LY_BASEPATH . "/config/database.php";
            if (!self::$conn) {
                self::$conn = "db";
            }
            self::$dbconfig = $dbconfigs[self::$conn];
        }
        return self::$dbconfig;
    }
    public static function getPDO()
    {
        return self::$pdo;
    }
    public static function getConn()
    {
        return self::$pdo->getConn();
    }
    public static function lastInsertId()
    {
        return self::$pdo->getConn()->lastInsertId();
    }
    public static function beginTrans()
    {
        if (!self::$pdo) {
            new self();
        }
        self::$pdo->beginTrans();
    }
    public static function commit()
    {
        self::$pdo->commit();
    }
    public static function rollBack()
    {
        self::$pdo->rollBack();
    }
    public function reset()
    {
        $this->tablename = "";
        $this->tableParams = [];
        $this->joinSql = "";
        $this->joinParams = [];
        $this->fieldSql = "";
        $this->updateSql = "";
        $this->updateParams = [];
        $this->whereSql = "";
        $this->whereParams = [];
        $this->orderSql = "";
        $this->limitSql = "";
        $this->limitParams = [];
        $this->groupSql = "";
        $this->havingSql = "";
        // $this::$sql="";
        // $this::$params=[];
    }
    public static function connect($table)
    {
        if (!self::$instance instanceof self) {
            self::$conn = $table;
            self::$instance = new self();
        } elseif (self::$conn !== $table) {
            $dbconfigs = include LY_BASEPATH . "/config/database.php";
            self::$conn = $table;
            self::$dbconfig = $dbconfigs[self::$conn];
            self::$pdo = PDO::getinstance(self::$dbconfig, self::$conn);
        }
        return self::$instance;
    }
    public static function closeConn()
    {
        self::$conn = "";
        DB::$pdo->closeInstance();
        DB::$pdo = null;
        self::$instance = null;
    }
    public static function table($name)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        } elseif (self::$conn !== "db") {
            $dbconfigs = include LY_BASEPATH . "/config/database.php";
            if (!self::$conn) {
                self::$conn = "db";
            }
            self::$dbconfig = $dbconfigs[self::$conn];
            self::$pdo = PDO::getinstance(self::$dbconfig, self::$conn);
        }
        $db = self::$instance;
        if (is_string($name)) {
            $db->tablename = $name;
        } elseif (is_array($name)) {
            if (count($name) == 1) {
                $key = key($name);
                $value = $name[$key];
                $db->tablename = $key . " " . $value;
            } elseif (count($name) == 2 && is_array($name[0])) {
                $db->tablename = "(" . $name[0][0] . ") " . $name[1] . " ";
                $db->tableParams = $name[0][1];
            }
        } else {
            throw new \Exception("table方法的参数应当是一个字符串或者一个数组", 1);
        }
        return $db;
    }
    public function field($param)
    {
        if (is_string($param)) {
            $this->fieldSql = trim($param);
        } elseif (is_array($param)) {
            foreach ($param as $key => $value) {
                if (is_numeric($key)) {
                    $this->fieldSql .= " " . $value . " ,";
                } else {
                    $this->fieldSql .= " " . $key . " as " . $value . " ,";
                }
            }
            $this->fieldSql = substr($this->fieldSql, 0, -1);
        }
        return $this;
    }
    public function join($table, $condition, $option = "inner")
    {
        $option = strtoupper($option);
        if (!in_array($option, ['INNER', 'LEFT', "RIGHT"])) {
            $option = "INNER";
        }
        if (is_string($table)) {
            $jointableSql = $table;
        } elseif (is_array($table)) {
            if (count($table) == 1) {
                $key = key($table);
                $value = $table[$key];
                $jointableSql = $key . " " . $value;
            } elseif (count($table) == 2 && is_array($table[0])) {
                $jointableSql = "(" . $table[0][0] . ") " . $table[1] . " ";
                $this->joinParams = $table[0][1];
            }
        } else {
            throw new \Exception("join方法的第一个参数应当是一个字符串或者一个数组", 1);
        }
        if (is_string($condition)) {
            $conditionSql = $condition;
        } else {
            throw new \Exception("join方法的第二个参数应当是一个字符串,( like: a.userid=b.userid)", 1);
        }
        $this->joinSql .= " " . $option . " join " . $jointableSql . " on " . $condition;
        return $this;
    }
    public function where($where, $param1 = "", $param2 = "")
    {
        if ($this->whereSql) {
            if (substr($this->whereSql, -3) === " ( ") {
                $this->whereSql .= " ( ";
            } else {
                $this->whereSql .= " and ( ";
            }
        } else {
            $this->whereSql = " where ( ";
        }
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $this->whereSql .= sprintf(" %s =? ", $key);
                    $this->whereParams[] = $value;
                } elseif (is_array($value)) {
                    $this->whereSql .= sprintf(" %s ", $key);
                    foreach ($value as $k => $v) {
                        $this->whereSql = $this->whereSql . $v . " ";
                    }
                    $this->whereSql = $this->whereSql;
                }
            }
        } elseif (is_string($where)) {
            if ($param1 === "") {
                $this->whereSql .= sprintf(" %s ", $where);
            } elseif ($param2 === "") {
                if (is_array($param1)) {
                    $this->whereSql .= $where;
                    $this->whereParams = array_merge($this->whereParams, $param1);
                } else {
                    $this->whereSql .= $where . " = ? ";
                    $this->whereParams[] = $param1;
                }
            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }

        if (substr($this->whereSql, -9) === " where ( ") {
            $this->whereSql = substr($this->whereSql,0,-9);
        } elseif (substr($this->whereSql, -7) === " and ( ") {
            $this->whereSql = substr($this->whereSql,0,-7);
        } elseif(substr($this->whereSql, -3) === " ( ") {
            $this->whereSql = substr($this->whereSql,0,-3);
        }else{
            $this->whereSql .= " ) ";
        }
        return $this;
    }
    public function whereOr($where, $param1 = "", $param2 = "")
    {
        if ($this->whereSql) {
            if (substr($this->whereSql, -3) === " ( ") {
                $this->whereSql .= " ( ";
            } else {
                $this->whereSql .= " or ( ";
            }
        } else {
            $this->whereSql = " where ( ";
        }
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                if (is_string($value) || is_numeric($value)) {

                    $this->whereSql .= sprintf(" %s =? ", $key);

                    $this->whereParams[] = $value;
                } elseif (is_array($value)) {

                    $this->whereSql .= sprintf(" %s ", $key);

                    foreach ($value as $k => $v) {
                        $this->whereSql = $this->whereSql . $v . " ";
                    }
                    $this->whereSql = $this->whereSql;
                }
            }
        } elseif (is_string($where)) {
            if ($param1 === "") {
                $this->whereSql .= sprintf(" %s ", $where);
            } elseif ($param2 === "") {
                if (is_array($param1)) {
                    $this->whereSql .= $where;
                    $this->whereParams = array_merge($this->whereParams, $param1);
                } else {
                    $this->whereSql .= $where . "=? ";
                    $this->whereParams[] = $param1;
                }
            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        if (substr($this->whereSql, -9) === " where ( ") {
            $this->whereSql = substr($this->whereSql,0,-9);
        } elseif (substr($this->whereSql, -6) === " or ( ") {
            $this->whereSql = substr($this->whereSql,0,-6);
        } elseif(substr($this->whereSql, -3) === " ( ") {
            $this->whereSql = substr($this->whereSql,0,-3);
        }else{
            $this->whereSql .= " ) ";
        }
        return $this;
    }
    public function group($group)
    {
        if (is_string($group)) {
            $this->groupSql = $this->groupSql ? $this->groupSql . "," . $group : " group by " . $group;
        } elseif (is_callable($group, true)) {
            call_user_func($group, $this);
        }
        return $this;
    }
    public function having($having)
    {
        if (is_string($having)) {
            $this->havingSql = $having;
        } elseif (is_callable($having, true)) {
            call_user_func($having, $this);
        }
        return $this;
    }
    public function order($order)
    {
        if (is_string($order)) {
            $this->orderSql = $this->orderSql ? "," . $order : " order by " . $order;
        } elseif (is_callable($order, true)) {
            call_user_func($order, $this);
        }
        return $this;
    }
    public function limit($start, $size = null)
    {
        if (is_callable($start, true)) {
            call_user_func($start, $this);
        } else {
            if (self::$datatype == "mysql") {
                if ($size === null) {
                    $this->limitSql = " limit ? ";
                    $this->limitParams = [$start];
                } else {
                    $this->limitSql = " limit ?,? ";
                    $this->limitParams = [$start, $size];
                }
            } elseif (self::$datatype == "oci") {
                $this->limitParams = [$size + $start, $start];
            }
        }
        return $this;
    }
    public function insert($param1, $param2 = [])
    {
        $insertSql = " ";
        $insertParams = $tableParams;
        if (is_array($param1)) {
            $iSql1 = "(";
            $iSql2 = "(";
            foreach ($param1 as $key => $value) {
                $iSql1 .= $key . ",";
                if (is_array($value)) {
                    $iSql2 .= $value[0] . ",";
                } else {
                    $iSql2 .= "?,";
                    $insertParams[] = $value;
                }
            }
            $iSql1 = substr($iSql1, 0, -1) . ")";
            $iSql2 = substr($iSql2, 0, -1) . ")";
            $insertSql = $iSql1 . " values " . $iSql2;
        } elseif (is_string($param1)) {
            $insertSql .= $param1;
            if (isarray($param2)) {
                $insertParams = $param2;
            }
        }
        $this::$sql = "INSERT INTO " . self::$dbconfig['prefix'] . $this->tablename . $insertSql;
        $this::$params = array_merge($insertParams);
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function insertEntity($param, $setnull = false)
    {
        $insertSql = "";
        $insertParams = $tableParams;
        $lies = $arr = DB::query("SHOW COLUMNS FROM `" . self::$dbconfig['prefix'] . $this->tablename . "`");
        $param1 = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && (!$setnull || $value !== null)) {
                $param1[$value['Field']] = $param[$value['Field']];
            }
        }
        if (count($param1)) {
            $iSql1 = "(";
            $iSql2 = "(";
            foreach ($param1 as $key => $value) {
                $iSql1 .= $key . ",";
                if (is_array($value)) {
                    $iSql2 .= $value[0] . ",";
                } else {
                    $iSql2 .= "?,";
                    $insertParams[] = $value;
                }
            }
            $iSql1 = substr($iSql1, 0, -1) . ")";
            $iSql2 = substr($iSql2, 0, -1) . ")";
            $insertSql = $iSql1 . " values " . $iSql2;
            $this::$sql = "INSERT INTO " . self::$dbconfig['prefix'] . $this->tablename . $insertSql;
            $this::$params = array_merge($insertParams);
            $res = DB::$pdo->query($this::$sql, $this::$params);
            $this->reset();
            return $res;
        } else {
            throw new \Exception("插入数据不能为空", 1);
            return null;
        }
    }
    public function delete($force = 0)
    {
        if (!$force && !$this->whereSql) {
            throw new \Exception("this will delete with no 'where',we has forbidden it.");
        }
        $this::$sql = "DELETE FROM " . self::$dbconfig['prefix'] . $this->tablename . $this->whereSql . $this->orderSql . $this->limitSql;
        $this::$params = array_merge($this->tableParams, $this->whereParams, $this->limitParams);
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function update($param, $param1 = [])
    {
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if (!is_array($value)) {
                    $this->updateSql .= $key . "=?,";
                    $this->updateParams[] = $value;
                } else {
                    $keys = array_keys($value);
                    if (is_numeric($keys[0])) {
                        if (count($keys) > 1) {
                            $v = $value[$keys[0]];
                            $this->updateSql .= $key . "=" . $v . ",";
                            $this->updateParams[] = $value[$keys[1]];
                        } else {
                            $v = $value[$keys[0]];
                            $this->updateSql .= $key . "=" . $v . ",";
                        }
                    } else {
                        $v = $keys[0];
                        $this->updateSql .= $key . "=" . $v . "?,";
                        $this->updateParams[] = $value[$keys[0]];
                    }
                }
            }
            $this->updateSql = substr($this->updateSql, 0, -1);
        } elseif (is_string($param)) {
            if (!$param1) {
                $this->updateSql .= $param;
            } elseif (is_array($param1)) {
                $this->updateSql .= $param;
                $this->updateParams = array_merge($this->updateParams, $param1);
            } else {
                return 0;
            }
        }
        $this::$sql = "update " . self::$dbconfig['prefix'] . $this->tablename . " set " . $this->updateSql . $this->whereSql . $this->limitSql;
        $this::$params = array_merge($this->tableParams, $this->updateParams, $this->whereParams, $this->limitParams);
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function updateEntity($param1, $setnull = false)
    {
        $lies = $arr = DB::query("SHOW COLUMNS FROM `" . self::$dbconfig['prefix'] . $this->tablename . "`");
        $param = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param1) && (!$setnull || $value !== null)) {
                $param[$value['Field']] = $param1[$value['Field']];
            }
        }
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if (!is_array($value)) {
                    $this->updateSql .= $key . "=?,";
                    $this->updateParams[] = $value;
                } else {
                    $keys = array_keys($value);
                    if (is_numeric($keys[0])) {
                        if (count($keys) > 1) {
                            $v = $value[$keys[0]];
                            $this->updateSql .= $key . "=" . $v . ",";
                            $this->updateParams[] = $value[$keys[1]];
                        } else {
                            $v = $value[$keys[0]];
                            $this->updateSql .= $key . "=" . $v . ",";
                        }
                    } else {
                        $v = $keys[0];
                        $this->updateSql .= $key . "=" . $v . "?,";
                        $this->updateParams[] = $value[$keys[0]];
                    }
                }
            }
            $this->updateSql = substr($this->updateSql, 0, -1);
        }
        $this::$sql = "update " . self::$dbconfig['prefix'] . $this->tablename . " set " . $this->updateSql . $this->whereSql . $this->limitSql;
        $this::$params = array_merge($this->tableParams, $this->updateParams, $this->whereParams, $this->limitParams);
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function buildSql()
    {
        $this::$sql = "SELECT " . ($this->fieldSql ?: "*") . " from " . self::$dbconfig['prefix'] . $this->tablename . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
        $this::$params = array_merge($this->updateParams, $this->whereParams, $this->limitParams);
        $this->reset();
        $return = array_merge([$this::$sql, $this::$params]);
        $this->reset();
        return $return;
    }
    public function select()
    {
        if (self::$datatype == "mysql") {
            $this::$sql = "SELECT " . ($this->fieldSql ?: "*") . " from " . self::$dbconfig['prefix'] . $this->tablename . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
            $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams, $this->limitParams);
        } elseif (self::$datatype == "oci") {
            if ($this->limitParams) {
                if ($this->limitParams[0]) {
                    $this::$sql = "SELECT * from " . "(SELECT A.*,ROWNUM RN  from " . "(SELECT " . ($this->fieldSql ?: "*") . " from " . self::$dbconfig['prefix'] . $this->tablename . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . ") A where ROWNUM<=?) where RN>?";
                    $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams, $this->limitParams);
                } else {
                    $this::$sql = "SELECT A.*  from " . "(SELECT " . ($this->fieldSql ?: "*") . " from " . self::$dbconfig['prefix'] . $this->tablename . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . ") A where ROWNUM <=?";
                    $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams, [$this->limitParams[1]]);
                }
            } else {
                $this::$sql = "SELECT " . ($this->fieldSql ?: "*") . " from " . self::$dbconfig['prefix'] . $this->tablename . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql;
                $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams);
            }
        }
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function find()
    {
        if (self::$datatype == "mysql") {
            $this->limitSql = " limit 1 ";
        } elseif (self::$datatype == "oci") {
            $this->limitParams = [null, 1];
        }
        $arr = $this->select();
        return $arr ? $arr[0] : false;
    }
    public function count()
    {
        $this::$sql = "SELECT 1 from " . self::$dbconfig['prefix'] . $this->tablename . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->limitSql;
        $this::$params = array_merge($this->updateParams, $this->whereParams, $this->limitParams);
        $sql = $this::$sql;
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res ? count($res) : 0;
    }
    public static function query($sql, $params = [])
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        $res = DB::$pdo->query($sql, $params);
        return $res;
    }
    public static function getSql()
    {
        return self::$sql;
    }
    public static function getParams()
    {
        return self::$params;
    }
}
