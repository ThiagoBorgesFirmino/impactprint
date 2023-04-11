<?php

class tipocadastro extends base {

	var
		$id
		,$descricao;

	static function getId($descricao){
		$descricao = strtoupper($descricao)	;
		return query_col("SELECT id FROM tipocadastro WHERE descricao = '$descricao'");
	}
	
	static function opcoesCliente(){
		return array(2=>"Normal",7=>"Especial");
	}

}


?>