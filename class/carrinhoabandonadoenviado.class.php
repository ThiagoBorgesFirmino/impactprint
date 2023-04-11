<?php
	class carrinhoabandonadoenviado extends base{
		var
		$id
		,$data_envio
		,$porcentagem
		,$carrinhoabandonado_id;
		
		public function getDataEnvioFormatada(){
			return formata_datahora_br($this->data_envio);
		}
		
	}

?>