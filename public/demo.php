<?php
    include "../ly/start.php";
    $lycons=new \ly\LY();
    use \ly\lib\DB as DB;
    use application\index\controller as Controller;
    $api=(new Controller\index())->jsonapi();
    var_dump($api);
    $a=$lycons->execute("/index/index/jsonapi");
    var_dump($a);
    function head(){
        ?>
        <a href="/">首页</a>
        <?php
    }
    include "./base.php";
?>

