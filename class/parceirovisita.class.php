<?php

// Parceiro
class parceirovisita extends base {
	var
		$id
		,$session_id
		,$parceiro_id
		,$item_id
		,$categoria_id
		,$data_cadastro
	;

	public function salva(){
		$parceiro = new parceiro(intval($this->parceiro_id));
		if(!$parceiro->id){
			return false;
		}
		
		$this->parceiro_id = $parceiro->id;
		
		if($this->session_id){
			
			$pesquisa = new parceirovisita(array('session_id'=>$this->session_id, "date_format(data_cadastro,'%d%m%y')"=>date('dmy')));
			
			if(!$pesquisa->id){
				return parent::salva();
			}
		}
	}

}

?>