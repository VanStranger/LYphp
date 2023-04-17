<?php
namespace ly\lib;

class DB
{
    public static $pdo;
    public static $instance;
    public static $datatype;
    public static $dbconfig;
    public static $conn    = "";
    private static $tables = [];
    private $tablename     = "";
    private $newTablename  = "";
    private $tableParams   = [];
    private $joinSql       = "";
    private $joinParams    = [];
    private $fieldSql      = "";
    private $updateSql     = "";
    private $updateParams  = [];
    private $whereSql      = "";
    private $whereParams   = [];
    private $orderSql      = "";
    private $groupSql      = "";
    private $havingSql     = "";
    private $havingParams  = [];
    private $limitSql      = "";
    private $limitParams   = [];
    private static $sql    = "";
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
        self::$dbconfig           = $dbconfigs[self::$conn];
        self::$dbconfig['prefix'] = isset(self::$dbconfig['prefix']) ? self::$dbconfig['prefix'] : "";
        self::$datatype           = self::$dbconfig['type'];
        self::$pdo                = PDO::getinstance(self::$dbconfig, self::$conn);
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
        self::$tables       = [];
        $this->tablename    = "";
        $this->newTablename = "";
        $this->tableParams  = [];
        $this->joinSql      = "";
        $this->joinParams   = [];
        $this->fieldSql     = "";
        $this->updateSql    = "";
        $this->updateParams = [];
        $this->whereSql     = "";
        $this->whereParams  = [];
        $this->orderSql     = "";
        $this->limitSql     = "";
        $this->limitParams  = [];
        $this->groupSql     = "";
        $this->havingSql    = "";
        $this->havingParams = [];
        // $this::$sql="";
        // $this::$params=[];
    }
    public static function connect($db, $config = [])
    {
        $dbconfigs = include LY_BASEPATH . "/config/database.php";
        if ($config) {
            $json = [
                "type"     => $config['type'] ?: "mysql",
                "host"     => $config['host'] ?: "127.0.0.1",
                "username" => $config['username'] ?: "root",
                "password" => $config['password'] ?: "root",
                "database" => $config['database'] ?: "test",
                "hostport" => $config['hostport'] ?: 3306,
                "charset"  => $config['charset'] ?: "utf8",
                "prefix"   => $config['prefix'] ?: "",
            ];
            $dbconfigs[$db] = $json;
            $file           = fopen("../config/database.php", "w+");
            fwrite($file, "<?php\r\n");
            fwrite($file, "  return [\r\n");
            foreach ($dbconfigs as $k => $v) {
                fwrite($file, "    \"" . $k . "\"=>[\r\n");
                foreach ($v as $key => $value) {
                    fwrite($file, "      \"" . $key . "\"=>\"" . $value . "\",\r\n");
                }
                fwrite($file, "    ],\r\n");
            }
            fwrite($file, "  ];\r\n");
            fwrite($file, "?>\r\n");
            fclose($file);
        }
        if (!self::$instance instanceof self) {
            self::$conn     = $db;
            self::$instance = new self();
        } elseif (self::$conn !== $db) {
            self::$conn     = $db;
            self::$dbconfig = $dbconfigs[self::$conn];
            self::$pdo      = PDO::getinstance(self::$dbconfig, self::$conn);
        }
        return self::$instance;
    }
    public static function closeConn()
    {
        self::$conn = "";
        DB::$pdo->closeInstance();
        DB::$pdo        = null;
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
            self::$pdo      = PDO::getinstance(self::$dbconfig, self::$conn);
        }
        $db = self::$instance;
        if (is_string($name)) {
            $db->tablename       = self::$dbconfig['prefix'] . $name;
            self::$tables[$name] = self::$dbconfig['prefix'] . $name;
        } elseif (is_array($name)) {
            if (count($name) == 1) {
                $key                                            = key($name);
                $value                                          = $name[$key];
                $db->tablename                                  = self::$dbconfig['prefix'] . $key;
                $db->newTablename                               = $value;
                self::$tables[$key]                             = $value;
                self::$tables[self::$dbconfig['prefix'] . $key] = $value;
            } elseif (count($name) == 2 && is_array($name[0])) {
                $db->tablename    = "(" . $name[0][0] . ") ";
                $db->newTablename = $name[1];
                $db->tableParams  = $name[0][1];
            }
        } else {
            throw new \Exception("table方法的参数应当是一个字符串或者一个数组", 1);
        }
        return $db;
    }
    public function join($table, $condition, $option = "inner")
    {
        $option = strtoupper($option);
        if (!in_array($option, ['INNER', 'LEFT', "RIGHT"])) {
            $option = "INNER";
        }
        if (is_string($table)) {
            $jointableSql         = "`" . self::$dbconfig['prefix'] . $table . "`";
            self::$tables[$table] = self::$dbconfig['prefix'] . $table;
        } elseif (is_array($table)) {
            if (count($table) == 1) {
                $key                                            = key($table);
                $value                                          = $table[$key];
                $jointableSql                                   = "`" . self::$dbconfig['prefix'] . $key . "` " . $value;
                self::$tables[$key]                             = $value;
                self::$tables[self::$dbconfig['prefix'] . $key] = $value;
            } elseif (count($table) == 2 && is_array($table[0])) {
                $jointableSql     = "(" . $table[0][0] . ") " . $table[1] . " ";
                $this->joinParams = $table[0][1];
            }
        } else {
            throw new \Exception("join方法的第一个参数应当是一个字符串或者一个数组", 1);
        }
        if (is_string($condition)) {
            foreach (self::$tables as $key => $value) {
                $condition = preg_replace("/^\s*" . $key . "\./", $value . ".", $condition);
                $condition = preg_replace("/\=\s*" . $key . "\./", "=" . $value . ".", $condition);
            }
            $conditionSql = $condition;
        } else {
            throw new \Exception("join方法的第二个参数应当是一个字符串,( like: a.userid=b.userid)", 1);
        }
        $this->joinSql .= " " . $option . " join " . $jointableSql . " on " . $condition;
        return $this;
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
        foreach (self::$tables as $key => $value) {
            $this->fieldSql = preg_replace("/^\s*" . $key . "\./", $value . ".", $this->fieldSql);
            $this->fieldSql = preg_replace("/,\s*" . $key . "\./", "," . $value . ".", $this->fieldSql);
        }
        return $this;
    }
    public function fields($param)
    {
        return $this->field($param);
    }
    public function where($where, $param1 = false, $param2 = false)
    {
        $this->startWhereSqlHead("and");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= " and ";
                }
                $isfirst = false;
                if (is_string($value) || is_numeric($value)) {
                    if (strstr($key, ".") === false) {
                        $this->whereSql .= sprintf(" %s =? ", "`" . $key . "`");
                    } else {
                        $this->whereSql .= sprintf(" %s =? ", $key);
                    }
                    $this->whereParams[] = $value;
                } elseif (is_null($value)) {
                    if (strstr($key, ".") === false) {
                        $this->whereSql .= sprintf(" %s is null ", "`" . $key . "`");
                    } else {
                        $this->whereSql .= sprintf(" %s is null ", $key);
                    }
                } elseif (is_array($value)) {
                    if (strstr($key, ".") === false) {
                        $this->whereSql .= sprintf(" %s", "`" . $key . "`");
                    } else {
                        $this->whereSql .= sprintf(" %s", $key);
                    }
                    foreach ($value as $k => $v) {
                        $this->whereSql = $this->whereSql . $v . " ";
                    }
                    // $this->whereSql = $this->whereSql;
                }
            }
        } elseif (is_string($where)) {
            if ($param1 === false) {
                $this->whereSql .= sprintf(" %s ", $where);
            } elseif ($param2 === false) {
                if (is_array($param1)) {
                    $this->whereSql .= $where;
                    $this->whereParams = array_merge($this->whereParams, $param1);
                } elseif ($param1 === null) {
                    $this->whereSql .= $where . " is null ";
                } else {
                    if (strstr($where, ".") === false) {
                        $this->whereSql .= "`" . $where . "`" . " = ? ";
                    } else {
                        $this->whereSql .= $where . " = ? ";
                    }
                    $this->whereParams[] = $param1;
                }
            } elseif ((is_string($param2) || is_numeric($param2) || is_null($param2)) && (is_string($param1) || is_numeric($param1))) {
                if (is_null($param2)) {
                    $this->whereSql .= $where . " " . $param1 . " is null ";
                } else {
                    $this->whereSql .= $where . " " . $param1 . " ? ";
                    $this->whereParams[] = $param2;
                }
            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }

        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }

    private function startWhereSqlHead($glue = "and")
    {
        if ($this->whereSql) {
            if (substr($this->whereSql, -3) === " ( " || substr($this->whereSql, -4) === " or " || substr($this->whereSql, -5) === " and ") {
                $this->whereSql .= " ( ";
            } else {
                $this->whereSql .= " " . $glue . " ( ";
            }
        } else {
            $this->whereSql = " where ( ";
        }
    }
    private function endWhereSqlHead()
    {
        if (substr($this->whereSql, -9) === " where ( ") {
            $this->whereSql = substr($this->whereSql, 0, -9);
        } elseif (substr($this->whereSql, -7) === " and ( ") {
            $this->whereSql = substr($this->whereSql, 0, -7);
        } elseif (substr($this->whereSql, -6) === " or ( ") {
            $this->whereSql = substr($this->whereSql, 0, -6);
        } elseif (substr($this->whereSql, -5) === " and ") {
            $this->whereSql = substr($this->whereSql, 0, -5);
        } elseif (substr($this->whereSql, -4) === " or ") {
            $this->whereSql = substr($this->whereSql, 0, -4);
        } elseif (substr($this->whereSql, -3) === " ( ") {
            $this->whereSql = substr($this->whereSql, 0, -3);
        } else {
            $this->whereSql .= " ) ";
        }
    }
    public function whereOr($where, $param1 = false, $param2 = false)
    {
        $this->startWhereSqlHead("or");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= " and ";
                }
                $isfirst = false;
                if (is_string($value) || is_numeric($value)) {
                    if (strstr($key, ".") === false) {
                        $this->whereSql .= sprintf(" %s =? ", "`" . $key . "`");
                    } else {
                        $this->whereSql .= sprintf(" %s =? ", $key);
                    }

                    $this->whereParams[] = $value;
                } elseif (is_null($value)) {
                    if (strstr($key, ".") === false) {
                        $this->whereSql .= sprintf(" %s is null ", "`" . $key . "`");
                    } else {
                        $this->whereSql .= sprintf(" %s is null ", $key);
                    }
                } elseif (is_array($value)) {

                    if (strstr($key, ".") === false) {
                        $this->whereSql .= sprintf(" %s ", "`" . $key . "`");
                    } else {
                        $this->whereSql .= sprintf(" %s ", $key);
                    }

                    foreach ($value as $k => $v) {
                        $this->whereSql = $this->whereSql . $v . " ";
                    }
                    // $this->whereSql = $this->whereSql;
                }
            }
        } elseif (is_string($where)) {
            if ($param1 === false) {
                $this->whereSql .= sprintf(" %s ", $where);
            } elseif ($param2 === false) {
                if (is_array($param1)) {
                    $this->whereSql .= $where;
                    $this->whereParams = array_merge($this->whereParams, $param1);
                } elseif ($param1 === null) {
                    $this->whereSql .= $where . " is null ";
                } else {
                    $this->whereSql .= $where . "=? ";
                    $this->whereParams[] = $param1;
                }
            } elseif ((is_string($param2) || is_numeric($param2) || is_null($param2)) && (is_string($param1) || is_numeric($param1))) {
                if (is_null($param2)) {
                    $this->whereSql .= $where . " " . $param1 . " is null ";
                } else {
                    $this->whereSql .= $where . " " . $param1 . " ? ";
                    $this->whereParams[] = $param2;
                }
            }
        } elseif (is_callable($where, true)) {
            // call_user_func([$this,$where], $this);
            $where($this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    public function in($key, $list)
    {
        return $this->whereIn($key, $list);
    }
    public function whereIn($param1, $param2)
    {
        $this->startWhereSqlHead("and");
        if (is_string($param1) && is_array($param2)) {
            if (strstr($param1, ".") === false) {
                $this->whereSql .= sprintf(" %s in ( ", "`" . $param1 . "`");
            } else {
                $this->whereSql .= sprintf(" %s in ( ", $param1);
            }
            foreach ($param2 as $key => $value) {
                $this->whereSql .= "?,";
                $this->whereParams[] = $value;
            }
            $this->whereSql = substr($this->whereSql, 0, -1) . ") ";
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    public function whereLike($param1, $param2)
    {
        $this->startWhereSqlHead("and");
        if (is_string($param1) && is_string($param2)) {
            if (strstr($param1, ".") === false) {
                $this->whereSql .= sprintf(" %s like ? ", "`" . $param1 . "`");
            } else {
                $this->whereSql .= sprintf(" %s like ? ", $param1);
            }
            $this->whereParams[] = "%" . $value . "%";
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    public function whereEntity($param, $type = true)
    {
        $lies  = $arr  = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $where = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && $param[$value['Field']]) {
                $where[$value['Field']] = $param[$value['Field']];
            }
        }
        $this->startWhereSqlHead("or");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= $type ? " and " : " or ";
                }
                $isfirst = false;
                if (!$this->isTypeText($lieTypes[$key])) {
                    if (is_array($value)) {
                        // var_dump($value);
                        $this->whereIn($key, $value);
                    } else {
                        $this->whereSql .= sprintf(" %s = ? ", "`" . $this->tablename . "`.`" . $key . "`");
                        $this->whereParams[] = $value;
                    }
                } else {
                    $this->whereSql .= sprintf(" %s like ? ", "`" . $key . "`");
                    $this->whereParams[] = "%" . $value . "%";
                }

            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    private function isTypeNumber($type)
    {
        $t3 = substr($type, 0, 3);
        if (in_array($t3, ['int', 'bit'])) {
            return true;
        }
        $t5 = substr($type, 0, 5);
        if ($t5 === 'float') {
            return true;
        }
        $t6 = substr($type, 0, 6);
        if (in_array($t6, ['bigint', 'decimal', 'double'])) {
            return true;
        }
        $t7 = substr($type, 0, 7);
        if ($t7 === "tinyint") {
            return true;
        }
        $t8 = substr($type, 0, 8);
        if ($t8 === "smallint") {
            return true;
        }
        $t9 = substr($type, 0, 9);
        if ($t9 === "mediumint") {
            return true;
        }
        return false;
    }
    private function isTypeText($type)
    {
        $t4 = substr($type, 0, 4);
        if (in_array($t4, ['text', 'char', 'json', 'uuid'])) {
            return true;
        }
        $t7 = substr($type, 0, 7);
        if ($t7 === "varchar") {
            return true;
        }
        $t8 = substr($type, 0, 8);
        if (in_array($t8, ['longtext', 'tinytext'])) {
            return true;
        }
        $t10 = substr($type, 0, 10);
        if ($t10 === "mediumtext") {
            return true;
        }
        return false;
    }
    public function whereEqLikeEntity($param, $type = true)
    {
        $lies     = $arr     = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $lieTypes = [];
        $where    = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && $param[$value['Field']]) {
                $where[$value['Field']]    = $param[$value['Field']];
                $lieTypes[$value['Field']] = $value['Type'];
            }
        }
        $this->startWhereSqlHead("and");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= $type ? " and " : " or ";
                }
                $isfirst = false;
                if (!$this->isTypeText($lieTypes[$key])) {
                    if (is_array($value)) {
                        // var_dump($value);
                        $this->whereIn($key, $value);
                    } else {
                        $this->whereSql .= sprintf(" %s = ? ", "`" . $this->tablename . "`.`" . $key . "`");
                        $this->whereParams[] = $value;
                    }
                } else {
                    $this->whereSql .= sprintf(" %s like ? ", "`" . $key . "`");
                    $this->whereParams[] = "%" . $value . "%";
                }

            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    public function whereLikeEntity($param, $type = true)
    {
        $lies  = $arr  = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $where = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && $param[$value['Field']]) {
                $where[$value['Field']] = $param[$value['Field']];
            }
        }
        $this->startWhereSqlHead("and");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= $type ? " and " : " or ";
                }
                $isfirst = false;

                $this->whereSql .= sprintf(" %s like ? ", "`" . $key . "`");

                $this->whereParams[] = "%" . $value . "%";

            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    public function whereLeftLikeEntity($param, $type = true)
    {
        $lies  = $arr  = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $where = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && $param[$value['Field']]) {
                $where[$value['Field']] = $param[$value['Field']];
            }
        }
        $this->startWhereSqlHead("and");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= $type ? " and " : " or ";
                }
                $isfirst = false;

                $this->whereSql .= sprintf(" %s like ? ", "`" . $key . "`");

                $this->whereParams[] = $value . "%";

            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }
    public function whereRightLikeEntity($param, $type = true)
    {
        $lies  = $arr  = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $where = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && $param[$value['Field']]) {
                $where[$value['Field']] = $param[$value['Field']];
            }
        }
        $this->startWhereSqlHead("and");
        if (is_array($where)) {
            $isfirst = true;
            foreach ($where as $key => $value) {
                if (!$isfirst) {
                    $this->whereSql .= $type ? " and " : " or ";
                }
                $isfirst = false;

                $this->whereSql .= sprintf(" %s like ? ", "`" . $key . "`");

                $this->whereParams[] = "%" . $value;

            }
        } elseif (is_callable($where, true)) {
            call_user_func($where, $this);
        }
        $this->endWhereSqlHead();
        foreach (self::$tables as $key => $value) {
            $this->whereSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->whereSql);
        }
        return $this;
    }

    public function group($group)
    {
        if (is_string($group)) {
            $this->groupSql = $this->groupSql ? $this->groupSql . "," . $group : " group by " . $group;
        } elseif (is_array($group)) {
            foreach ($group as $key => $value) {
                $this->groupSql = $this->groupSql ? $this->groupSql . "," . $value : " group by " . $value;
            }
        } elseif (is_callable($group, true)) {
            call_user_func($group, $this);
        }
        foreach (self::$tables as $key => $value) {
            $this->groupSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->groupSql);
            $this->groupSql = preg_replace("/,\s*" . $key . "\./", "," . $value . ".", $this->groupSql);
        }
        return $this;
    }
    public function having($having, $param1 = false, $param2 = false)
    {
        if ($this->havingSql) {
            if (substr($this->havingSql, -3) === " ( ") {
                $this->havingSql .= " ( ";
            } else {
                $this->havingSql .= " and ( ";
            }
        } else {
            $this->havingSql = " having ( ";
        }
        if (is_array($having)) {
            $isfirst = true;
            foreach ($having as $key => $value) {
                if (!$isfirst) {
                    $this->havingSql .= " and ";
                }
                $isfirst = false;
                if (is_string($value) || is_numeric($value)) {
                    if (strstr($key, ".") === false) {
                        $this->havingSql .= sprintf(" %s =? ", "`" . $key . "`");
                    } else {
                        $this->havingSql .= sprintf(" %s =? ", $key);
                    }
                    $this->havingParams[] = $value;
                } elseif (is_null($value)) {
                    if (strstr($key, ".") === false) {
                        $this->havingSql .= sprintf(" %s in null ", "`" . $key . "`");
                    } else {
                        $this->havingSql .= sprintf(" %s in null ", $key);
                    }
                } elseif (is_array($value)) {
                    if (strstr($key, ".") === false) {
                        $this->havingSql .= sprintf(" %s ", "`" . $key . "`");
                    } else {
                        $this->havingSql .= sprintf(" %s ", $key);
                    }
                    foreach ($value as $k => $v) {
                        $this->havingSql = $this->havingSql . $v . " ";
                    }
                    $this->havingSql = $this->havingSql;
                }
            }
        } elseif (is_string($having)) {
            if ($param1 === false) {
                $this->havingSql .= sprintf(" %s ", $having);
            } elseif ($param2 === false) {
                if (is_array($param1)) {
                    $this->havingSql .= $having;
                    $this->havingParams = array_merge($this->havingParams, $param1);
                } else if (is_null($param1)) {
                    $this->havingSql .= $having . " is null ";
                } else {
                    $this->havingSql .= $having . " = ? ";
                    $this->havingParams[] = $param1;
                }
            } elseif ((is_string($param2) || is_numeric($param2) || is_null($param2)) && (is_string($param1) || is_numeric($param1))) {
                if (is_null($param2)) {
                    $this->havingSql .= $having . " " . $param1 . " is null ";
                } else {
                    $this->havingSql .= $having . " " . $param1 . " ? ";
                    $this->havingParams[] = $param2;
                }
            }
        } elseif (is_callable($having, true)) {
            call_user_func($having, $this);
        }

        if (substr($this->havingSql, -10) === " having ( ") {
            $this->havingSql = substr($this->havingSql, 0, -10);
        } elseif (substr($this->havingSql, -7) === " and ( ") {
            $this->havingSql = substr($this->havingSql, 0, -7);
        } elseif (substr($this->havingSql, -3) === " ( ") {
            $this->havingSql = substr($this->havingSql, 0, -3);
        } else {
            $this->havingSql .= " ) ";
        }
        foreach (self::$tables as $key => $value) {
            $this->havingSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->havingSql);

        }
        return $this;
    }
    public function order($order)
    {
        if (is_string($order)) {
            $this->orderSql = $this->orderSql ? "," . $order : " order by " . $order;
        } elseif (is_array($order)) {
            foreach ($order as $key => $value) {
                $this->orderSql = $this->orderSql ? "," . $value : " order by " . $value;
            }
        } elseif (is_callable($order, true)) {
            call_user_func($order, $this);
        }
        foreach (self::$tables as $key => $value) {
            $this->orderSql = preg_replace("/\s+" . $key . "\./", " " . $value . ".", $this->orderSql);
            $this->orderSql = preg_replace("/,\s*" . $key . "\./", "," . $value . ".", $this->orderSql);
        }
        return $this;
    }
    public function limit($start, $size = false)
    {
        if (is_callable($start, true)) {
            call_user_func($start, $this);
        } else {
            if (self::$datatype == "mysql") {
                if ($size === false) {
                    $this->limitSql    = " limit ? ";
                    $this->limitParams = [$start];
                } else {
                    $this->limitSql    = " limit ?,? ";
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
        $insertSql    = " ";
        $insertParams = $tableParams;
        if (is_array($param1)) {
            $iSql1 = "(";
            $iSql2 = "(";
            foreach ($param1 as $key => $value) {
                $iSql1 .= "`" . $key . "`" . ",";
                if (is_array($value)) {
                    $iSql2 .= $value[0] . ",";
                } else {
                    $iSql2 .= "?,";
                    $insertParams[] = $value;
                }
            }
            $iSql1     = substr($iSql1, 0, -1) . ")";
            $iSql2     = substr($iSql2, 0, -1) . ")";
            $insertSql = $iSql1 . " values " . $iSql2;
        } elseif (is_string($param1)) {
            $insertSql .= $param1;
            if (isarray($param2)) {
                $insertParams = $param2;
            }
        }
        $this::$sql    = "INSERT INTO `" . $this->tablename . "`" . $insertSql;
        $this::$params = array_merge($insertParams);
        $res           = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function insertEntity($param, $setnull = false)
    {
        $insertSql    = "";
        $insertParams = $tableParams;
        $lies         = $arr         = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $param1       = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param) && (!$setnull || $value !== false)) {
                $param1[$value['Field']] = $param[$value['Field']];
            }
        }
        if (count($param1)) {
            $iSql1 = "(";
            $iSql2 = "(";
            foreach ($param1 as $key => $value) {
                $iSql1 .= "`" . $key . "`" . ",";
                if (is_array($value)) {
                    $iSql2 .= $value[0] . ",";
                } else {
                    $iSql2 .= "?,";
                    $insertParams[] = $value;
                }
            }
            $iSql1         = substr($iSql1, 0, -1) . ")";
            $iSql2         = substr($iSql2, 0, -1) . ")";
            $insertSql     = $iSql1 . " values " . $iSql2;
            $this::$sql    = "INSERT INTO `" . $this->tablename . "`" . $insertSql;
            $this::$params = array_merge($insertParams);
            $res           = DB::$pdo->query($this::$sql, $this::$params);
            $this->reset();
            return $res;
        } else {
            throw new \Exception("插入数据不能为空", 1);
            return false;
        }
    }
    public function delete($force = 0)
    {
        if (!$force && !$this->whereSql) {
            throw new \Exception("this will delete with no 'where',we has forbidden it.");
        }
        $this::$sql    = "DELETE FROM `" . $this->tablename . "` " . $this->newTablename . " " . $this->whereSql . $this->orderSql . $this->limitSql;
        $this::$params = array_merge($this->tableParams, $this->whereParams, $this->limitParams);
        $res           = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function update($param, $param1 = false)
    {
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if (!is_array($value)) {
                    $this->updateSql .= "`" . $key . "`" . "=?,";
                    $this->updateParams[] = $value;
                } else {
                    $keys = array_keys($value);
                    if (is_numeric($keys[0])) {
                        if (count($keys) > 1) {
                            $v = $value[$keys[0]];
                            $this->updateSql .= "`" . $key . "`" . "=" . $v . ",";
                            $this->updateParams[] = $value[$keys[1]];
                        } else {
                            $v = $value[$keys[0]];
                            $this->updateSql .= "`" . $key . "`" . "=" . $v . ",";
                        }
                    } else {
                        $v = $keys[0];
                        $this->updateSql .= "`" . $key . "`" . "=" . $v . "?,";
                        $this->updateParams[] = $value[$keys[0]];
                    }
                }
            }
            $this->updateSql = substr($this->updateSql, 0, -1);
        } elseif (is_string($param)) {
            if ($param1 === false) {
                $this->updateSql .= $param;
            } elseif (is_array($param1)) {
                $this->updateSql .= $param;
                $this->updateParams = array_merge($this->updateParams, $param1);
            } else {
                $this->updateSql .= "`" . $param . "`" . "=? ";
                $this->updateParams[] = $param1;
            }
        }
        $this::$sql    = "update `" . $this->tablename . "` set " . $this->updateSql . $this->whereSql . $this->limitSql;
        $this::$params = array_merge($this->tableParams, $this->updateParams, $this->whereParams, $this->limitParams);
        $res           = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function updateEntity($param1, $setnull = false)
    {
        $lies  = $arr  = DB::query("SHOW COLUMNS FROM `" . $this->tablename . "`");
        $param = [];
        foreach ($lies as $key => $value) {
            if (array_key_exists($value['Field'], $param1) && (!$setnull || $value !== false)) {
                $param[$value['Field']] = $param1[$value['Field']];
            }
        }
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if (!is_array($value)) {
                    $this->updateSql .= "`" . $key . "`" . "=?,";
                    $this->updateParams[] = $value;
                } else {
                    $keys = array_keys($value);
                    if (is_numeric($keys[0])) {
                        if (count($keys) > 1) {
                            $v = $value[$keys[0]];
                            $this->updateSql .= "`" . $key . "`" . "=" . $v . ",";
                            $this->updateParams[] = $value[$keys[1]];
                        } else {
                            $v = $value[$keys[0]];
                            $this->updateSql .= "`" . $key . "`" . "=" . $v . ",";
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
        $this::$sql    = "update `" . $this->tablename . "` " . $this->newTablename . " " . " set " . $this->updateSql . $this->whereSql . $this->limitSql;
        $this::$params = array_merge($this->tableParams, $this->updateParams, $this->whereParams, $this->limitParams);
        $res           = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function buildSql()
    {
        $this::$sql    = "SELECT " . ($this->fieldSql ?: "*") . " from `" . $this->tablename . "` " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
        $this::$params = array_merge($this->whereParams, $this->havingParams, $this->limitParams);
        $this->reset();
        $return = array_merge([$this::$sql, $this::$params]);
        $this->reset();
        return $return;
    }
    public function select()
    {
        if (self::$datatype == "mysql") {
            $this::$sql    = "SELECT " . ($this->fieldSql ?: "*") . " from `" . $this->tablename . "` " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
            $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams, $this->havingParams, $this->limitParams);
        } elseif (self::$datatype == "oci") {
            if ($this->limitParams) {
                if ($this->limitParams[0]) {
                    $this::$sql    = "SELECT * from " . "(SELECT A.*,ROWNUM RN  from " . "(SELECT " . ($this->fieldSql ?: "*") . " from " . $this->tablename . " " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . ") A where ROWNUM<=?) where RN>?";
                    $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams, $this->limitParams);
                } else {
                    $this::$sql    = "SELECT A.*  from " . "(SELECT " . ($this->fieldSql ?: "*") . " from " . $this->tablename . " " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql . ") A where ROWNUM <=?";
                    $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams, [$this->limitParams[1]]);
                }
            } else {
                $this::$sql    = "SELECT " . ($this->fieldSql ?: "*") . " from " . $this->tablename . " " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql;
                $this::$params = array_merge($this->tableParams, $this->joinParams, $this->whereParams);
            }
        }
        $res = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return $res;
    }
    public function selectPage()
    {
        if (self::$datatype == "mysql") {
            $sql           = " from `" . $this->tablename . "` " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql;
            $this::$sql    = "SELECT " . ($this->fieldSql ?: "*") . $sql . $this->limitSql;
            $params        = array_merge($this->tableParams, $this->joinParams, $this->whereParams, $this->havingParams);
            $this::$params = array_merge($params, $this->limitParams);
        } elseif (self::$datatype == "oci") {
            $sql    = " from " . $this->tablename . " " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->orderSql;
            $params = array_merge($this->tableParams, $this->joinParams, $this->whereParams);
            if ($this->limitParams) {
                if (count($this->limitParams) > 1) {
                    $this::$sql    = "SELECT * from " . "(SELECT A.*,ROWNUM RN  from " . "( SELECT " . ($this->fieldSql ?: "*") . $sql . ") A where ROWNUM<=?) where RN>?";
                    $this::$params = array_merge($params, $this->limitParams);
                } else {
                    $this::$sql    = "SELECT A.*  from " . "( SELECT " . ($this->fieldSql ?: "*") . $sql . ") A where ROWNUM <=?";
                    $this::$params = array_merge($params, [$this->limitParams[1]]);
                }
            } else {
                $this::$sql    = "SELECT " . ($this->fieldSql ?: "*") . $sql;
                $this::$params = $params;
            }
        }
        $totalrows = DB::$pdo->query("select 1" . $sql, $params);
        $total     = count($totalrows);
        $res       = DB::$pdo->query($this::$sql, $this::$params);
        $this->reset();
        return ["total" => $total, "data" => $res];

    }
    public function find()
    {
        if (self::$datatype == "mysql") {
            $this->limitSql = " limit 1 ";
        } elseif (self::$datatype == "oci") {
            $this->limitParams = [false, 1];
        }
        $arr = $this->select();
        return $arr ? $arr[0] : false;
    }
    public function count()
    {
        $this::$sql    = "SELECT 1 from `" . $this->tablename . "` " . $this->newTablename . " " . $this->joinSql . $this->whereSql . $this->groupSql . $this->havingSql . $this->limitSql;
        $this::$params = array_merge($this->updateParams, $this->whereParams, $this->limitParams);
        $sql           = $this::$sql;
        $res           = DB::$pdo->query($this::$sql, $this::$params);
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
    public static function execArray($sql, $params = [])
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        $res = DB::$pdo->execArray($sql, $params);
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
