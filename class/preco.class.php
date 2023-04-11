<?php

class preco extends base {

	var
		$id
		,$item_id
		,$preco
		,$qtd_1
		,$qtd_2;

	public function validaDados(&$erro=array()){
	
		if(intval($this->item_id)==0){
			$erro[] = 'Insira um Item';
		}
		
		if(floatval($this->preco)<0){
			$erro[] = 'O campo preco n&atilde;o pode ser menor que zero';
		}

		return sizeof($erro)==0;
	}
	
}