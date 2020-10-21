<?php
error_reporting(E_ALL ^ E_DEPRECATED);




session_start();
//ob_start('ob_gzhandler');
require_once('site/libs/mysql.php'); // порядок подключения обязателен




    $GLOBALS['db'] = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    $GLOBALS['db']->set_charset('utf8');


    $GLOBALS['mobile'] = 0;

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }



    include_once('site/modules/functions.php');


    require_once('site/libs/smarty/Smarty.class.php');

if(isset($_GET['mod_name']) && strlen($_GET['mod_name']) > 0) // если идет непосредственное обращение к модулю
{

    print ($_SESSION['smarty']->fetch('str:'.$_GET['mod_name'])); // передаем управление модулю
    exit(0);

}

    $GLOBALS['index'] = 0;
    $_SESSION['smarty']->assign('index', $GLOBALS['index']);

    
    
    include_once('site/modules/aModule.class.php');
    include_once('site/modules/router.class.php');


    print($_SESSION['smarty']->fetch('index.tpl'));

       
?>