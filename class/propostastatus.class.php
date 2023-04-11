<?php

class propostastatus extends base {

	var
		$id
		,$descricao
		,$ordem;

	static function options(){

		$opcoes = array();

		$query = query($sql="SELECT id, descricao FROM propostastatus ORDER BY ordem, descricao");

		while($fetch=fetch($query)){
			$opcoes[$fetch->id] = $fetch->descricao;
		}

		return $opcoes;

	}
	
	static function opcoes(){
		return propostastatus::options();
	}

}

?>