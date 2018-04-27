<?php  
/** 
 * 此文件会把所有的传入参数分派到相应的控制器中 
 */  
//当类初始化时，自动引入相关文件  
function __autoload($className)  
{  
    //解析文件名，得到文件的存放路径，如News_Model表示存放在models文件夹里的news.php（这里是作者的命名约定）  
    list($filename , $suffix) = explode('_' , $className);  
  
    //构成文件路径  
    $file = SERVER_ROOT . '/models/' . strtolower($filename) . '.php';  
  
    //获取文件  
    if (file_exists($file))  
    {  
        //引入文件  
        include_once($file);          
    }  
    else  
    {  
        //文件不存在  
        die("File '$filename' containing class '$className' not found.");      
    }  
}   
//获取请求参数  
$request = $_SERVER['QUERY_STRING'];  
  
//解析请求页面和其它GET变量  
$parsed = explode('&' , $request);  
  
//页面是第一个元素  
$page = array_shift($parsed);  
  
//剩余的为GET变量，也把它们解析出来  
$getVars = array();  
foreach ($parsed as $argument)  
{  
    //split GET vars along '=' symbol to separate variable, values  
    list($variable , $value) = explode('=' , $argument);  
    $getVars[$variable] = $value;  
}  
  
//构成控制器文件路径  
$target = SERVER_ROOT . '/controllers/' . $page . '.php';  
  
//引入目标文件  
if (file_exists($target))  
{  
    include($target);  
  
    //修改page变量，以符合命名规范（如$page="news"，我们的约定是首字母大写，控制器的话就在后面加上“<strong>_Controller”</strong>,即News_Controller）  
    $class = ucfirst($page) . '_Controller';  
  
    //初始化对应的类  
    if (class_exists($class))  
    {  
        $controller = new $class;  
    }  
    else  
    {  
        //类的命名正确吗？  
        die('class does not exist!');  
    }  
}  
else  
{  
    //不能在controllers找到此文件  
    die('page does not exist!');  
}  
  
//一但初始化了控制器，就调用它的默认函数main();  
//把get变量传给它  
$controller->main($getVars);?>  