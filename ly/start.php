<?php
include SERVER_ROOT."/ly/lib/loader.php";
(new ly\lib\loader())->autoload();
$ly=new \ly\ly();
$ly->run();
