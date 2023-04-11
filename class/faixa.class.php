<?php

class faixa extends base {

	var
		$id
		,$qtd_de
		,$qtd_ate;


	public function validaDados(&$erro=array()){

		if($this->qtd_de==0){
			$erro[] = 'Os valores não podem ser iguais a zero';
		}
		elseif($this->qtd_ate==0){
			$erro[] = 'Os valores não podem ser iguais a zero';
		}
		elseif($this->qtd_de>$this->qtd_ate){
			$erro[] = 'A quantidade inicial não deve ser maior que a quantidade final';
		}
		elseif($this->qtd_de===$this->qtd_ate){
			$erro[] = 'As quantidades não devem ser iguais';
		}
		elseif(rows(query("SELECT * FROM faixa WHERE {$this->qtd_de} BETWEEN qtd_de AND qtd_ate ".($this->id>0?"AND id <> {$this->id}":"")))>0){
			$erro[] = 'A quantidade inicial está em conflito com outra já cadastrada';
		}

		return sizeof($erro)==0;

	}

}

?>