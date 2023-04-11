<?php

function set_SEO(&$t, $params=array()){

    if(!isset($params['title'])){
        $params['title'] = config::get('EMPRESA');
    }
    else {
        $params['title'] = $params['title'].' - '.config::get('EMPRESA').', '.config::get('EMPRESA');
    }

    if(!isset($params['url'])){
        $params['url'] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    if(!isset($params['description'])){
        $params['description'] = config::get('EMPRESA').', '.config::get('EMPRESA');
    }
    else {
        $params['description'] = $params['description'].', '.config::get('EMPRESA');
    }

    if(!isset($params['keywords'])){
        $params['keywords'] = config::get('EMPRESA').', '.config::get('EMPRESA');
    }
    else {
        $newKeywords = str_replace("-",",",$params['keywords']);
        $params['keywords'] = $newKeywords.', '.config::get('EMPRESA');
    }

    $seo = array();
    $og = array();

    foreach($params as $key => $value){

        if($key=='title'){
            $seo[] = "<title>{$value}</title>";
            $og[] = "<meta property='og:title' content='{$value}' />";
        }
        elseif($key=='description'){
            $seo[] = "<meta name='{$key}' content='{$value}' />";
            $og[] = "<meta property='og:{$key}' content='{$value}' />";
        }
        elseif($key=='keywords'){
            $seo[] = "<meta name='{$key}' content='{$value}' />";
        }
        elseif($key=='url'){
            $og[] = "<meta property='og:{$key}' content='{$value}' />";
        }
        elseif($key=='image'){
            $og[] = "<meta property='og:{$key}' content='{$value}' />";
        }
    }

    $og[] = "<meta property='og:type' content='website' />";

    $t->seo = join(PHP_EOL, $seo).PHP_EOL.PHP_EOL.join(PHP_EOL, $og);

}