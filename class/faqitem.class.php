<?php

class faqitem extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$faqgrupo_id
		,$pergunta
		,$pergunta_es
		,$pergunta_in
		,$resposta
		,$resposta_es
		,$resposta_in
		,$ordem
		,$data_cadastro;
		
	public function validaDados(&$erro=array()){

		$faqgrupo_id = intval($this->faqgrupo_id);
		
		if($faqgrupo_id==0){
			$erro[] = 'Grupo precisa ser definido';
		}
	
		if($this->pergunta==''){
			$erro[] = 'Pergunta precisa ser definida';
		}

		return sizeof($erro)==0;

	}
	
}