<?php
$now=time();
function getName($name){
  return __FUNCTION__.$name.microtime(true);
}
function comp(){
  ?>
  <style>
  .<?php echo getName('comp');?> .<?php echo getName('info');?>{
    color:red;
  }
  </style>
  <div class="<?php echo getName('comp');?>">
    <a href="###" class="<?php echo getName('info');?>">组件内容,点击有响应事件</a>
  </div>
  <?php
  addCallable(function(){
    ?>
    <script>
      document.getElementsByClassName("<?php echo getName('info');?>")[0].onclick=function(){
        alert("组件自定义的点击事件");
      }
    </script>
    <?php
  });
}