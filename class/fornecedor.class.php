<?php

/**********************

Criado em:
Ultima alteraчуo: 2/04/2012



**********************/

class fornecedor extends base {

	var
		$id
		,$nome
		,$email
		,$telefone		
	;

		// Retorna uma lista simples
	static function opcoes(){

		$return = array();
		$query = query($sql="SELECT * FROM cadastro WHERE tipocadastro_id = 4 ORDER BY nome");
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		return $return;

	}
	
	
}
?>