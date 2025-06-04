<?php

define('MODULO_DEFECTO', 'home');
define('LAYOUT_LOGIN', 'login.php');
define('LAYOUT_DESKTOP', 'desktop.php');
define('MODULO_PATH',  realpath('app/views'));
define('LAYOUT_PAHT', realpath('app/templates'));
define('BASE_URL_PROJECT', '/WESStore');

$id_rol = 2;

$conf['error'] = array(
    'archivo' => 'index.html',
    'layout' => LAYOUT_DESKTOP
);

$conf['home'] = array(
    'archivo' => 'home.html',
    'layout' => LAYOUT_DESKTOP
);
 
$conf['login'] = array(
    'archivo' => 'login.html',
    'layout' => LAYOUT_LOGIN
);

$conf['inicio'] = array(
    'archivo' => 'inicio.html',
    'layout' => LAYOUT_DESKTOP
);


$conf['productos'] = array(
    'archivo' => 'productos.html',
    'layout' => LAYOUT_DESKTOP
);



$conf['carrito'] = array(
    'archivo' => 'carrito.html',
    'layout' => LAYOUT_DESKTOP
);


$conf['perfil'] = array(
    'archivo' => 'perfil.html',
    'layout' => LAYOUT_DESKTOP
);

$conf['admin'] = array(
    'archivo' => 'admin.html',
    'layout' => LAYOUT_DESKTOP
);