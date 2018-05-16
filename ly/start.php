<?php
include BASEPATH."/ly/lib/loader.php";
(new ly\lib\loader())->autoload();
$ly=new \ly\ly();
$ly->run();
