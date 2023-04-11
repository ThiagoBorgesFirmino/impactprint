<?php

class itemduvida extends base{

	var
		$id
		,$nome
		,$email
		,$duvida
		,$st_divulga_email
		,$data_cadastro
		,$item_id
		,$cadastro_id
		,$resposta
		,$st_ativo
		,$categoria_id;
		
		public function validaitemduvida($erros=null){
			return $erros;
		}
}

?>