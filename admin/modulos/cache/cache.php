<?php

// Author: Felipe Gregorio
// Modulo simples de cache para substituir new nomedomodelo(1) por modulo_cache::model('nomedomodelo',1)
// Objetivo de diminuir consultas na base e ganhar velocidade

class modulo_cache {

    public $arquivo = 'cache';

    static function model($name,$id,$action='get'){

        $debug = false;

        $hash = md5($name.$id);
        $hash_session = $hash;

        $file = __DIR__."/files/{$name}/{$hash}";

        if($debug){
            printr("debug");
            printr($hash);
            printr($file);
        }

        if($action=='get'){

            if(file_exists($file)){

                if(isset($_SESSION[$hash_session])){
                    if($debug) {
                        printr("return session: {$hash_session}");
                    }
                    return $_SESSION[$hash_session];
                }

                if($debug){
                    printr("return unserialize: {$file}");
                }
                return $_SESSION[$hash_session] = unserialize(file_get_contents($file));
            }

            if(!file_exists($folder=__DIR__."/files/{$name}")){
                mkdir($folder);
            }

            if($debug) {
                printr("save serialize: {$file}");
            }
            file_put_contents($file,serialize($obj=new $name($id)));
            return $obj;
        }
        elseif($action=='clear'){
            if(file_exists($file)){
                unlink($file);
                unset($_SESSION[$hash_session]);
            }
        }
    }
}