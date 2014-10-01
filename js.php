<?php

    namespace Runtime;

    //! Dynamic generated javascript styles

    header("Content-Type: text/javascript; charset=utf-8;");

    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors',   true);
    ini_set('html_errors',      true);

    session_start();
    
    require_once "config/app_config.php";
    require_once "class/request.php";

    $file = request::get_var('id');
    if(file_exists($file))
    {
        ob_start();
        include $file;
        $js = ob_get_contents();
        ob_end_clean();
    }
    else
        $js = "/* <not found: ".$file."> */".LN;

    echo $js;

    unset($js);

