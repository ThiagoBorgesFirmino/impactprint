<?php

class config extends base {

	var
		$id
		,$grupo
		,$chave
		,$valor
		,$obs
		,$st_podealterar
		,$st_podeexcluir
		,$st_admin
		,$st_tipocampo
		,$st_literal;

    public function validaDados(&$erros=array()){
        return sizeof($erros)==0;
    }

    public function salva(){
        unset($_SESSION['CACHECONFIG'.$this->chave]);
        return parent::salva();
    }

    public function __get($chave){
        return config::get($chave);
    }

    static function get($chave){

        $chave = strtoupper(trim($chave));

        // if(isset($_SESSION['CACHECONFIG'.$chave])){
            // return $_SESSION['CACHECONFIG'.$chave];
        // }

        $ret = '';

        $fetch = fetch(query("SELECT st_literal, valor FROM config WHERE chave = '{$chave}'"));
        if(!@$fetch){
            if($ret = setNovaChave($chave)){ $_SESSION['CACHECONFIG'.$chave] = $ret; return $ret;}
            die("Em config nao achamos: {$chave}");
        }

        if($fetch->st_literal=='N'){
            $ret = nl2br($fetch->valor);
        }
        else {
            $ret = $fetch->valor;
        }

        $_SESSION['CACHECONFIG'.$chave] = $ret;
        return $ret;
    }

}
