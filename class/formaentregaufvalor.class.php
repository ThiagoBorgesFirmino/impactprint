<?php

// Modelo de dados
class formaentregaufvalor extends base {

	var
		$id

		,$st_ativo

		,$formaentrega_id
		,$uf_id

		,$valor_capital
		,$valor_interior
		
		,$prazo_capital
		,$prazo_interior
	;

	public function getValorCapitalFormatado(){
		return money($this->valor_capital);
	}

	public function getValorInteriorFormatado(){
		return money($this->valor_interior);
	}

}

?>