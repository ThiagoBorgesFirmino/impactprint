<?php

class workflow extends base {

	const LANCADO = 10;
	const EM_ANALISE = 6;
	const RESPONDIDO = 2;
	const APROVADO = 3;
	const REPROVADO = 4;
	const VENDIDO = 5;
	
	var
		$id,
		$ordem,
		$descricao;

	static function opcoes(){
		
		$return = array();

		$sql = 
		"
		SELECT
			id
			,descricao
			,ordem
		FROM
			workflow
		ORDER BY
			ordem
			,descricao
		";

		$query = query($sql);
		while($fetch=fetch($query)){
			$return[$fetch->id]=$fetch->descricao;
		}

		return $return;

	}

}

?>