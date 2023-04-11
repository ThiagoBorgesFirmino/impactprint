<?php

class vendastatus extends base {

	var
		$id
		,$descricao
		,$ordem;

	static function opcoes(){
		return vendastatus::options();
	}
		
	static function options(){

		$opcoes = array();

		$query = query($sql="SELECT id, descricao FROM vendastatus ORDER BY ordem, descricao");

		while($fetch=fetch($query)){
			$opcoes[$fetch->id] = $fetch->descricao;
		}

		return $opcoes;

	}

}

?>