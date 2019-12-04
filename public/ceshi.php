<?php
header("Content-type:text/html;charset=gbk");
$tns = "  
(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1522))
    )
    (CONNECT_DATA =
      (SERVICE_NAME = orcl)
    )
  )
       ";
$db_username = "root";
$db_password = "br13jHhrh6";
try{
    $conn = new \PDO("oci:dbname=".$tns,$db_username,$db_password);
   var_dump($conn);
    $sth = $conn->prepare('SELECT * from "T_STU" ');
    $sth->execute();

    $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
    var_dump($result);
}catch(PDOException $e){
    echo ($e->getMessage());
}

    try{
     $conn = new PDO("oci:dbname=127.0.0.1:1522/orcl",'root','br13jHhrh6');// PDO方式
    //    $conn = oci_connect('root','br13jhhrh6',"(DEscriptION=(ADDRESS=(PROTOCOL =TCP)(HOST=127.0.0.1)(PORT = 1521))(CONNECT_DATA =(SID=orcl)))");
       var_dump($conn);
       echo "连接成功";
    }catch(PDOException $e){
       echo ("Error:".$e->getMessage());
    }