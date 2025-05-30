<?php
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');

    session_start();

    require_once('conf.php');
    

    if(isset($_GET['mod'])){
        $modulo = $_GET['mod'];
    }else{
        $modulo = MODULO_DEFECTO;
    }

    if(isset($conf[$modulo]['layout'])){
        $path_layout = LAYOUT_PAHT."/".$conf[$modulo]['layout'];
        if(!empty($conf[$modulo]['layout'])){
            include($path_layout);
        }
    }else{
        $modulo = 'login';
        $path_layout = LAYOUT_PAHT."/".$conf[$modulo]['layout'];
        include($path_layout);
    }
?>