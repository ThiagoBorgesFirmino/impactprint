<?php

// Modelo de dados
class site extends base {

	var $id;
	var $nome;
	var $imagem;
	var $data_cadastro;

	public function validaDados(&$erro=array()){
		if($this->nome==''){
			$erro[] = 'Nome não pode estar vazio';
		}
		return sizeof($erro)==0;
	}

	static function opcoes(){

		$ret = array();

		$query = query($sql = "SELECT * FROM site ORDER BY nome");

		while($fetch = fetch($query)){
			$ret[$fetch->id] = $fetch->nome;
		}

		return $ret;
	}

}
