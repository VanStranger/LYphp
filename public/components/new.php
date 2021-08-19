<?php
use \ly\lib\Hongmeng;

return function ()
{
    $name = Hongmeng::getStr("comp");
    ?>
  <style>
  .<?php echo $name; ?> .info{
    color:red;
  }
  </style>
  <div class="<?php echo $name; ?>">
    <a href="###" class="info">组件内容,点击有响应事件</a>
  </div>
  <?php
  $body = [
            [
              "tag"=>"div",
              "class" => "comp",
              "children"=>[
                    [
                      "tag"=>"a",
                      "href" => '###',
                      "onclick"=>"console.log('1');",
                      "children"=>[
                        'LYphp demo'
                      ],
                    ],
              ]
            ],
    ];
    $a=["aaa"];
    echo Hongmeng::render($body,$name);
    echo Hongmeng::scopeStyle(__DIR__."/new.css",$name);
    Hongmeng::addScript(function () use ($name) {
        ?>
    <script>
      document.querySelector(".<?php echo $name; ?> .info").onclick=function(){
        alert("组件自定义的点击事件");
      }
    </script>
    <?php
});
};