<?php
/*
 * PHP-PDO-MySQL-Class
 * https://github.com/lincanbin/PHP-PDO-MySQL-Class
 *
 * Copyright 2015 Canbin Lin (lincanbin@hotmail.com)
 * http://www.94cb.com/
 *
 * Licensed under the MIT License

 *
 * A PHP MySQL PDO class
 */
namespace ly\lib;
class Log
{
	private $path = '/runtime/log/';
	public function __construct($setPath="")
	{
		$this->path = $setPath?$setPath:LY_BASEPATH. $this->path;
	}
	public function write($message, $fileSalt)
	{
		$date = new \DateTime();
		$log  = $this->path . $date->format('Y-m-d') . "-" . md5($date->format('Y-m-d') . $fileSalt) . ".txt";
		if (is_dir($this->path)) {
			if (!file_exists($log)) {
				$fh = fopen($log, 'a+') or die("Fatal Error !");
				$logcontent = "Time : " . $date->format('H:i:s') . "\r\n" . $message . "\r\n";
				fwrite($fh, $logcontent);
				fclose($fh);
			} else {
				$this->edit($log, $date, $message);
			}
		} else {
			if (mkdir($this->path, 0777,true) === true) {
				$this->write($message, $fileSalt);
			}
		}
	}
	private function edit($log, $date, $message)
	{
		$logcontent = "Time : " . $date->format('H:i:s') . "\r\n" . $message . "\r\n\r\n";
		$logcontent = $logcontent . file_get_contents($log);
		file_put_contents($log, $logcontent);
	}
}
class PDO
{
	static private $instance;
	private $DBType;
	private $DBHost;
	private $DBName;
	private $DBUser;
	private $DBPassword;
	private $DBPort;
	private $DBCharset;
	private $pdo;
	private $sQuery;
	private $bConnected = false;
	private $log;
	private $parameters;
	public $rowCount   = 0;
	public $columnCount   = 0;
	public $querycount = 0;


	private function __construct($config)
	{
		$this->log        = new Log();
		$this->DBType       = isset($config['type'])?$config['type']:"mysql";
		$this->DBHost       = isset($config['hostname'])?$config['hostname']:"127.0.0.1";
		$this->DBUser     = isset($config['username'])?$config['username']:"root";
		$this->DBPassword = isset($config['password'])?$config['password']:"root";
		$this->DBPort	  = isset($config['hostport'])?$config['hostport']:3306;
		$this->DBName     = isset($config['database'])?$config['database']:"";
		$this->DBCharset     = isset($config['charset'])?$config['charset']:"utf8";
		$this->Connect();
		$this->parameters = array();
	}

	private function __clone(){
	}
	static public function getInstance($config){
	                //判断$instance是否是Uni的对象
	                //没有则创建
	        if (!self::$instance instanceof self) {
	            self::$instance = new self($config);
	        }
	        return self::$instance;

	}
	private function Connect()
	{
		try {
			$this->pdo = new \PDO($this->DBType . ':dbname=' . $this->DBName . ';host=' . $this->DBHost . ';port=' . $this->DBPort . ';charset=' . $this->DBCharset,
				$this->DBUser,
				$this->DBPassword,
				array(
					//For PHP 5.3.6 or lower
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
					\PDO::ATTR_EMULATE_PREPARES => false,

					//长连接
					//\PDO::ATTR_PERSISTENT => true,

					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
				)
			);
			/*
			//For PHP 5.3.6 or lower
			$this->pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//$this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);//长连接
			$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			*/
			$this->bConnected = true;

		}
		catch (\PDOException $e) {
			throw $this->ExceptionLog($e);
			die();
		}
	}


	public function CloseConnection()
	{
		$this->pdo = null;
	}


	private function Init($query, $parameters = "")
	{
		if (!$this->bConnected) {
			$this->Connect();
		}
		try {
			$this->parameters = $parameters;
			$this->sQuery     = $this->pdo->prepare($this->BuildParams($query, $this->parameters));

			if (!empty($this->parameters)) {
				if (array_key_exists(0, $parameters)) {
					$parametersType = true;
					array_unshift($this->parameters, "");
					unset($this->parameters[0]);
				} else {
					$parametersType = false;
				}
				foreach ($this->parameters as $column => $value) {
					$this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]); //It would be query after loop end(before 'sQuery->execute()').It is wrong to use $value.
				}
			}

			$this->succes = $this->sQuery->execute();
			$this->querycount++;
		}
		catch (\PDOException $e) {
			$this->ExceptionLog($e, $this->BuildParams($query));
			if(DEBUG){

				if(isset($GLOBALS['whoops']) && $GLOBALS['whoops']){
					$errorPage = new \Whoops\Handler\PrettyPageHandler();
					$errorPage->setPageTitle("It's broken!"); // Set the page's title
					$errorPage->addDataTable("Extra Info", array(
						"query"=>$query,
						"parameters"=>$parameters,
					));

					$GLOBALS['whoops']->pushHandler($errorPage);
					throw $e;
				}else{
					Header("HTTP/1.1 500 Internal Server Error");
					throw $e;
				}
			}
			die();
		}

		$this->parameters = array();
	}

	private function BuildParams($query, $params = null)
	{
		if (!empty($params)) {
			$rawStatement = explode(" ", $query);
			foreach ($rawStatement as $value) {
				if (strtolower($value) == 'in') {
					return str_replace("(?)", "(" . implode(",", array_fill(0, count($params), "?")) . ")", $query);
				}
			}
		}
		return $query;
	}


	public function query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
	{
		$query        = trim($query);
		$rawStatement = explode(" ", $query);
		$this->Init($query, $params);
		$statement = strtolower($rawStatement[0]);
		if ($statement === 'select' || $statement === 'show') {
			return $this->sQuery->fetchAll($fetchmode);
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			return $this->sQuery->rowCount();
		} else {
			return NULL;
		}
	}


	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}


	public function column($query, $params = null)
	{
		$this->Init($query, $params);
		$resultColumn = $this->sQuery->fetchAll(\PDO::FETCH_COLUMN);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultColumn;
	}


	public function row($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
	{
		$this->Init($query, $params);
		$resultRow = $this->sQuery->fetch($fetchmode);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultRow;
	}


	public function single($query, $params = null)
	{
		$this->Init($query, $params);
		return $this->sQuery->fetchColumn();
	}


	private function ExceptionLog($e, $sql = "")
	{
		$message=$e->getMessage();
		$exception = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";

		if (!empty($sql)) {
			$message .= "\r\nRaw SQL : " . $sql;
		}
		$this->log->write($message, $this->DBName . md5($this->DBPassword));
		//Prevent search engines to crawl
		return $e;
	}
}