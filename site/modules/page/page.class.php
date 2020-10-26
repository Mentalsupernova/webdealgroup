<?php include_once('site/modules/aModule.class.php');
class page extends aModule{
    function execute($arr)
    {           
        //echo "<pre>"; print_r($arr); echo "</pre>"; //die();

        $page = row("SELECT * FROM _pages WHERE alias LIKE '".mysql_escape_string(stripslashes($arr['action'][0]))."' ");
        //echo "<pre>"; print_r($page); echo "</pre>"; die();
        if($page['module'] == 'page')
        {
	        $content = $_SESSION['smarty']->fetch('page:'.$page['id']);
	        $_SESSION['smarty']->assign('page_content', $content);
	        $_SESSION['smarty']->assign('page', $page);
	        $_SESSION['smarty']->display($arr['mod_name'].'/'.$arr['mod_name'].'.tpl');
        }
        else
        {
        	print ($_SESSION['smarty']->fetch('str:'.$page['module']));
        }


    }
}
?>