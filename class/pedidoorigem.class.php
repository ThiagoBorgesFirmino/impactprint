<?php

// Classe de controle para pedidoorigem
class pedidoorigem extends base {

	var
		$id
		,$descricao
		,$ordem;

	static function getId($descricao){
		$descricao = strtoupper($descricao)	;
		return query_col("SELECT id FROM pedidoorigem WHERE upper(descricao) = '{$descricao}'");
	}

	static function opcoes(){
	
		$opcoes = array();

		$query = query($sql="SELECT id, descricao FROM pedidoorigem ORDER BY ordem");

		while($fetch=fetch($query)){
			$opcoes[$fetch->id] = $fetch->descricao;
		}

		return $opcoes;
	
	}
	
	static function opcoes_admin(){
	
		$opcoes = array();

		$query = query($sql="SELECT id, descricao FROM pedidoorigem WHERE id <> ".pedidoorigem::getId('Site')." ORDER BY ordem");

		while($fetch=fetch($query)){
			$opcoes[$fetch->id] = $fetch->descricao;
		}

		return $opcoes;
	
	}
	
}

?>