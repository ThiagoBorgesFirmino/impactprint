<?php

if(isset($_GET["site"])){
	setcookie("site",1,time()+60*60*24*365, "/");
}
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set('America/Sao_Paulo');

define('TAM_NOME', '20');

if($_SERVER['SERVER_NAME']=='impactprint.local'){

    error_reporting(E_ALL);

    header('Content-Type: text/html; charset=UTF-8');

    define('BD_HOST'	, 'localhost' ) ;
    define('BD_USER'	, 'root' ) ;
    define('BD_PASS'	, '' ) ;
    define('BD_DATABASE', 'projeto_st' ) ;
    define("PATH_IMG","https://ajg.im". DIRECTORY_SEPARATOR ."img". DIRECTORY_SEPARATOR );
    
    define('PATH_SITE' , '/') ;
    define('INDEX' , '/index.php/') ;
    define('ADMIN_PATH' , '/admin.php/') ;
    define( 'AJAX' , '/ajax.php/') ;
    

    define('DEBUG'         , '0');
    define('MANUTENCAO'    , '0');
    define('BOOTSTRAP_LESS', '0');
    define('JS_DEV'        , '0');
    

} elseif($_SERVER['SERVER_NAME']==''){

    error_reporting(0);

    header('Content-Type: text/html; charset=UTF-8');

    define( 'BD_HOST'	 , 'localhost');
	define( 'BD_USER'	 , 'admin');
	define( 'BD_PASS'	 , 'TIAjung11');
    define( 'BD_DATABASE', '');
	
    define("PATH_IMG","https://ajg.im". DIRECTORY_SEPARATOR ."img". DIRECTORY_SEPARATOR );
    define('PATH_SITE'  , '/');
    define('INDEX'      , '/index.php/');
    define('ADMIN_PATH' , '/admin.php/');
    define( 'AJAX'      , '/ajax.php/');
    define('DEBUG'         , '0');
    define('MANUTENCAO'    , '0');
    define('BOOTSTRAP_LESS', '0');
    define('JS_DEV'        , '0');
}

define('PATH_PEQ', 'img/produtos/1/' ) ; // Pequeno
define('PATH_INT', 'img/produtos/2/' ) ; // Intermediario
define('PATH_MED', 'img/produtos/3/' ) ; // Medio
define('PATH_GRD', 'img/produtos/4/' ) ; // Grande
define('PATH_GIG', 'img/produtos/5/' ) ; // Gigante

define('PATH_IMG_COR', 'img/cor/' ) ; //
define('PATH_IMG_SPLASH', 'img/splash/' ) ; //
define('PATH_TEXTO', 'img_texto/' ) ; //
define('PATH_NOTICIA', 'img_noticia/' ) ; //

$path = dirname(__FILE__);

define('PATH_ABS',$path);

require("{$path}/util/conexao.php");
require("{$path}/util/funcoes.php");
require("{$path}/util/funcoes-rede-sociais.php");
require("{$path}/util/funcoes-gpc.php");
require("{$path}/util/funcoes-debug.php");
require("{$path}/class/base.class.php");
require("{$path}/class/carrinho.class.php");
require("{$path}/vendor/Mobile_Detect.php");
if(!defined("PATH_IMG"))define("PATH_IMG","{$path}". DIRECTORY_SEPARATOR ."img". DIRECTORY_SEPARATOR );


$detect = new Mobile_Detect;
define('IS_MOBILE', $detect->isMobile() ? 1 : 0);

//hack pra zerar a sessao
if(@$_REQUEST['z']=='1'){
    @session_destroy();
}
else {
    @session_start();
}

query('SET NAMES utf8');

if( MANUTENCAO=='1' && (!isset($_COOKIE["site"])) ){
    die(require 'manutencao.html');
}