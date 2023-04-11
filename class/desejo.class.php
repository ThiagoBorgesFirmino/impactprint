<?php
class desejo extends base {

	var
		$id
		,$cadastro_id
		,$item_id
		,$data_cadastro;

	public function getDataHoraFormatada(){
		return formata_datahora_br($this->data_cadastro);
	}
	
	public function getDataCadastroFormatada(){
		return formata_data_br($this->data_cadastro);
	}

}
?>