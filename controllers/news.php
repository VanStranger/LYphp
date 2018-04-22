<?php  
/** 
 * 这个文件处理文章的查询，并提供文章 
 */  
class News_Controller  
{  
    /** 
     * $template变量会保存与此控制器相关的"view(视图)"的文件名，不包括.php后缀  
     */  
    public $template = 'news';  
  
    /** 
     * 此方法为route.php默认调用 
     *  
     * @param array $getVars 传入到index.php的GET变量数组 
     */  
    public function main(array $getVars)  
    {  
        $newsModel = new News_Model;  
      
         //获取一片文章   
         $article = $newsModel->get_article($getVars['article']);   
         //创建一个视图，并传入该控制器的template变量   
         $view = new View_Model($this->template);   
         //把文章数据赋给视图模板   
         $view->assign('title' , $article['title']);   
         $view->assign('content' , $article['content']);
    }  
}  