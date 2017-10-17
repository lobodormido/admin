<?php
session_start();

$_SESSION['usuario_id'] = (isset($_SESSION['usuario_id']))? $_SESSION['usuario_id'] : null;

$_SESSION['CREATE']     = 1;
$_SESSION['READ']       = 2;
$_SESSION['UPDATE']     = 4;
$_SESSION['DELETE']     = 8;

$protocol               = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
$path                   = realpath(dirname("../../../"));
$path                   = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
$_SESSION['dir_base']   = $protocol.$_SERVER['HTTP_HOST'].$path;

// Slim

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';

$app = new \Slim\App;

require '../src/getPermisos.php';
require '../src/routes/login.php';
require '../src/routes/noticias.php';
require '../src/routes/proyectos.php';
require '../src/routes/usuarios.php';
require '../src/routes/comentarios.php';
require '../src/routes/permisos.php';
require 'upload.php';

$app->run();