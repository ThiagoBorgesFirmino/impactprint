<?php

set_time_limit(0);
ini_set('memory_limit', '-1');
ob_implicit_flush(true);

$start = time();

require '../global.php';
require '../class/config.class.php';
require '../class/SimpleImage.class.php';

$limit = 300;
$jobs = results($sql="SELECT * FROM cronjob WHERE tipo = 'cortarimagem' AND processado = 0 ORDER BY dt_cadastro LIMIT {$limit}");

foreach($jobs as $job){

    $log = '';

    ob_start();

    $path = '../img/produtos/original/';
    $dir = dir($path);

    $opacidade = config::get($key_opacidade = 'MARCA_DAGUA_OPACIDADE');
    $file = $job->param1;

    if(is_file($source=$path.$file)){

        try {

            $dest = "../img/produtos/{$job->param2}/{$file}";

            $tamanho = $job->param3;
            $marca = $job->param4;

            $si = new SimpleImage($source);

            if($marca == 'S'){
                $si->overlay('../img/marcadagua.png', 'center', $opacidade);
                // $si->save($path_marca_dagua, 100);
            }

            $si->resize($tamanho, $tamanho);
            $si->save($dest,100);

        }
        catch(Exception $ex){
            print "Erro: ".$ex->getMessage();
        }

    }

    $log = mysql_real_escape_string(ob_get_contents());
    ob_end_clean();

    query("UPDATE cronjob SET processado = 1, dt_processado = NOW(), logprocesso = '{$log}' WHERE id = {$job->id}");

}

$end = time();
print 'Tempo de execução '.($end-$start).' segundos';
