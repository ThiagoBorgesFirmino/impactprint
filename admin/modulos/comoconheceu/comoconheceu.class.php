<?php

class comoconheceu extends base {

    var $id;
    var $st_ativo;
    var $nome;
    var $nome_es;
    var $nome_in;
    var $data_cadastro;

    public function get_table_name(){
        return 'comoconheceu';
    }

    public function salva(){
        $ret = parent::salva();
        return $ret;
    }

    static function opcoes(){

        $return = array();

        $query = query($sql = "SELECT * FROM comoconheceu ORDER BY nome ");

        while($fetch = fetch($query)){
            $return[$fetch->id] = $fetch->nome;
        }

        return $return;

    }

}