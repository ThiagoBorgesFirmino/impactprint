<?php

class fretefaixalocal extends base{

	var
		$id
		,$st_ativo
		,$uf
		,$cidade
		,$nome
		,$valor_compra_minimo
		,$valor_frete
		,$data_validade
		,$data_cadastro;
		
		
		public function validaFretefaixalocal(&$erros=array()){
		
			if($this->valor_compra_minimo < 0){
				$erros['nome'] = 'Valor de compra invalido';
			}
			if($this->valor_frete < 0){
				$erros['valor_frete'] = 'Valor de frete invalido';
			}
			
			return sizeof($erros)==0;
			
		}

}

?>