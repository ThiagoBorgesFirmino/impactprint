<?php

class faqgrupo extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$nome
		,$nome_es
		,$nome_in
		,$descricao
		,$descricao_es
		,$descricao_in
		,$ordem
		,$data_cadastro;
		
	public function validaDados(&$erro=array()){

		if($this->nome==''){
			$erro[] = 'Nome não pode estar vazio';
		}

		return sizeof($erro)==0;

	}
	
	static function opcoes(){

		$return = array();

		$query = query($sql = "SELECT * FROM faqgrupo ORDER BY ordem ");

		while($fetch = fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}
		
}