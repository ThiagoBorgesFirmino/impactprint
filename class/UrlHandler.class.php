<?php

class UrlHandler {

    function __construct($controle){

        $fileName = basename($_SERVER['SCRIPT_FILENAME']);
        if(strpos(request_uri(),$fileName)>-1){
            $uriParts = explode("{$fileName}/", request_uri());
        }

        //printr($_SERVER['REQUEST_URI']);
        //printr($fileName);
        //printr($uriParts);

        // se tem alguma coisa depois do nome do arquivo
        if(@$uriParts[1]){
            //echo 'aqui';
            @list($metodo, $arg1, $arg2, $arg3, $arg4, $arg5) = explode('/', $uriParts[1]);

            // vardump($uriParts[1]);
            // var_dump($metodo);
            // var_dump($arg1);

            if(method_exists($controle, $metodo)){
                $controle->$metodo($arg1, $arg2, $arg3, $arg4, $arg5);
            }
            elseif(method_exists($controle, '__call')){
                $controle->$metodo($arg1, $arg2, $arg3, $arg4, $arg5);
            }
            else {
                $controle->index();
            }
        }
        else {
            $controle->index();
        }

        unset($controle);
        // unset($this);

    }
}

if(!function_exists('request_uri')){

    function request_uri() {

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }
        else {
            if(isset($_SERVER['ORIG_PATH_INFO'])) {
                $uri = $_SERVER['ORIG_PATH_INFO'];
            }
            elseif (isset($_SERVER['argv'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . @$_SERVER['argv'][0];
            }
            elseif(isset($_SERVER['ORIG_PATH_INFO'])) {
                $uri = $_SERVER['ORIG_PATH_INFO'];
            }
            elseif (isset($_SERVER['QUERY_STRING'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
            }
            else {
                $uri = $_SERVER['SCRIPT_NAME'];
            }
        }

        // Prevent multiple slashes to avoid cross site requests via the Form API.
        $uri = '/' . ltrim($uri, '/');

        return $uri;
    }

}