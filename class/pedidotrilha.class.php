<?php

class pedidotrilha extends base {

	var
		$id
		,$pedido_id
		,$cadastro_id
		,$msg
		,$data_cadastro
		;

	public function getDataCadastroFormatado(){
		return formata_datahora_br($this->data_cadastro);
	}
	
	public function salva(){
		$this->cadastro_id = decode(@$_SESSION['CADASTRO']->id);
		if($this->cadastro_id==''){
			$this->cadastro_id = null;
		}
		parent::salva();
	}
		
}

?>