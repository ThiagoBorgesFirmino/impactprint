<?php

// Modelo de dados
class formaentregaufgratis extends base {

	var
		$id

		,$st_ativo

		,$formaentrega_id
		,$uf_id

		,$frete_gratis_acima
	;

	public function getValorFreteGratisAcimaFormatado(){
		return money($this->frete_gratis_acima);
	}

}

?>