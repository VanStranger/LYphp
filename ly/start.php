<?php
include BASEPATH."/ly/lib/Loader.php";
include BASEPATH."/ly/help.php";
(new ly\lib\loader())->autoload();
$ly=new \ly\ly();
$ly->run();
