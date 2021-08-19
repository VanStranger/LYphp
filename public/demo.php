<?php
    require_once "../ly/start.php";
    // use \ly\lib\DB as DB;
    // $a=$ly->execute("/index/index/jsonapi");
    function content(){
        ?>
        <p>自定义内容</p>
        <?php
        $comp=include "./components/comp.php";
        $comp();
        $new=include "./components/new.php";
        $new();
    }
    include "./base.php";
?>
