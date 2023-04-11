<?php 

class faixapreco extends base {

	var
		$id
		,$st_ativo
		,$nome
		,$preco_de
		,$preco_ate;
		
	public function validaDados(&$erro=array()){

		if($this->preco_de==0){
			$erro[] = 'Os valores não podem ser iguais a zero';
		}
		elseif($this->preco_ate==0){
			$erro[] = 'Os valores não podem ser iguais a zero';
		}
		elseif($this->preco_de>$this->preco_ate){
			$erro[] = 'A quantidade inicial não deve ser maior que a quantidade final';
		}
		elseif($this->preco_de===$this->preco_ate){
			$erro[] = 'As quantidades não devem ser iguais';
		}
		elseif(rows(query($sql="SELECT * FROM faixapreco WHERE {$this->preco_de} BETWEEN preco_de AND preco_ate ".($this->id>0?"AND id <> {$this->id}":"")))>0){
			printr($sql);
			$erro[] = 'As varia&ccedil;&otilde;es de pre&ccedil;o est&atilde;o conflitantes';
		}

		return sizeof($erro)==0;

	}
}

?>