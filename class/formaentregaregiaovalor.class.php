<?php

// Modelo de dados
class formaentregaregiaovalor extends base {

	var
		$id

		,$st_ativo

		,$formaentrega_id
		,$regiaoentrega_id

		,$valor
		,$prazo
	;

	public function getValorFormatado(){
		return money($this->valor);
	}

}

?>