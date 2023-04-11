<?php

// Modelo de dados
class itemopiniao extends base {
	
	var
		$id
		,$item_id
		,$cadastro_id
		,$st_ativo
		,$nome
		,$email
		,$avaliacao
		,$opiniao
		,$st_divulga_email
		,$data_cadastro
		,$data_alteracao;

	// Valida dados
	public function validaDados(&$erro=array()){
		return sizeof($erro)==0;
	}
}

?>