<?php
    include "../ly/start.php";
    use \ly\lib\DB as DB;
    $a=$ly->execute("/index/index/jsonapi");


    function content(){
        ?>
        <p>自定义内容</p>
        <?php
        include "./components/comp.php";
        comp();
    }
    include "./base.php";
?>

