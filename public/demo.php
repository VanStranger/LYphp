<?php
    include "../ly/start.php";
    use \ly\lib\DB as DB;
    use application\index\controller as Controller;
    $api=(new Controller\index())->jsonapi();
    var_dump($api);
    function head(){
        ?>
        <a href="/">主页</a>
        <?php
    }
    include "./base.php";
?>

