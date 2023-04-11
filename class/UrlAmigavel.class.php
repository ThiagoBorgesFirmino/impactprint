<?php

class UrlAmigavel {

    function __construct($controle){

        // Resgata o PATH_INFO

        // Nome do metodo que sera chamado na classe de controle
        $target = 'index';

        // Parametros que serao passados
        $arg1 = $arg2 = $arg3 = $arg4 = $arg5 = '';

        $params = array();
        if(array_key_exists('PATH_INFO', $_SERVER)){
            $path_info = explode('/', ($url_request=$_SERVER['PATH_INFO']));
            foreach($path_info as $path){
                if($path != '' && $path != 'index.php'){
                    $params[] = $path;
                }
            }
            if(array_key_exists(0, $params)){
                $target = $params[0];
            }
            for($i=1;$i<=5;$i++){
                if(array_key_exists($i, $params)){
                    $var = "arg{$i}";
                    $$var = $params[$i];
                    // $arg"{$i}" = $params[$i];
                }
            }
        }
        elseif(array_key_exists('ORIG_PATH_INFO', $_SERVER)){
            $path_info = explode('/', ($url_request=$_SERVER['ORIG_PATH_INFO']));
            foreach($path_info as $path){
                if($path != '' && $path != 'index.php'){
                    $params[] = $path;
                }
            }
            if(array_key_exists(0, $params)){
                $target = $params[0];
            }
            for($i=1;$i<=5;$i++){
                if(array_key_exists($i, $params)){
                    $var = "arg{$i}";
                    $$var = $params[$i];
                    // $arg"{$i}" = $params[$i];
                }
            }
        }

        // printr($_SERVER);
        // printr($params);
        // printr('Target: '.$target);
        // printr($arg1);
        // printr($arg2);
        // printr($arg3);
        // printr($arg4);
        // printr($arg5);

        $seo_url = config::get("URL").$target;
        $seopro = new seopro(array("url"=>$seo_url));

        // printr($seo_url);
        // printr($seopro);

        $tag_n = 1;
        if(method_exists($controle, $seopro->metodo)){
            $target = $seopro->metodo;
            if($seopro->args!=""){
                $var = "arg{$tag_n}";
                $$var = $seopro->args;
                $tag_n++;
            }
            if($seopro->tag_nome!=""){
                $var = "arg{$tag_n}";
                $$var = $seopro->tag_nome;
                $tag_n++;
            }

            if(isset($controle->seopro))$controle->seopro = $seopro;
        }
        /* ### */

        if(method_exists($controle, $target)){
            $controle->$target($arg1, $arg2, $arg3, $arg4, $arg5);
        }
        else {
            $controle->index();
        }
    }
}
