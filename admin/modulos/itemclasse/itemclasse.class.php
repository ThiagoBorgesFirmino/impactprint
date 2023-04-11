<?php

class itemclasse extends base {

    var $id;
    var $nome;
    var $ordem;

    static function opcoes($opts=array()){
        $ret = array();

        if(isset($opts['blank'])){
            $ret[] = '--';
        }

        foreach(results($sql="SELECT id, nome FROM itemclasse ORDER BY ordem, nome") as $item){
            $ret[$item->id] = $item->nome;
        }
        return $ret;
    }

}
