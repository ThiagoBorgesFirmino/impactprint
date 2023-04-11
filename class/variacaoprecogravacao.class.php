<?php

class variacaoprecogravacao extends base {

	var
		$id
		,$qtd_1
		,$qtd_2;

	public function validaDados(&$erro=array()){

		if(intval($this->qtd_1)<=0){
			$erro[] = 'O campo quantidade 1 deve ser maior que zero';
		}
		
		if(intval($this->qtd_2)<=0){
			$erro[] = 'O campo quantidade 2 deve ser maior que zero';
		}
		
		if(intval($this->qtd_1)>intval($this->qtd_2)){
			$erro[] = 'O campo quantidade 2 deve ser maior que o campo quantidade 1';
		}

		if(sizeof($erro)==0){
			// Periodos conflitantes
			if(rows(query("select * from variacaoprecogravacao where {$this->qtd_1} BETWEEN qtd_1 AND qtd_2 ".($this->id>0?" AND id <> {$this->id} ":"")))>0){
				$erro[] = "O campo quantidade 1 {$this->qtd_1} entra em conflito com outros, ajuste a quantidade individualmente para fazer esse tipo de altera&ccedil;&atilde;o";
			}
			
			if(rows(query("select * from variacaoprecogravacao where {$this->qtd_2} BETWEEN qtd_1 AND qtd_2 ".($this->id>0?" AND id <> {$this->id} ":"")))>0){
				$erro[] = "O campo quantidade 2 {$this->qtd_2} entra em conflito com outros, ajuste a quantidade individualmente para fazer esse tipo de altera&ccedil;&atilde;o";
			}
			
		}
		
		return sizeof($erro)==0;

	}
}

?>