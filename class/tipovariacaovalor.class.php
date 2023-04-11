<?php

class tipovariacaovalor extends base {

	var
		$id
		,$tipovariacao_id
		,$nome
		;

	public function salva(){
		
		$query = query("SELECT * FROM tipovariacaovalor WHERE nome = '{$this->nome}'");
		if(!(rows($query)>0)){
			if(parent::salva()){
				// atualiza referencia de texto nos itens
				query("UPDATE item SET skuvariacao_valor1 = '{$this->nome}' WHERE skuvariacao_valor1_id = {$this->id}");
				query("UPDATE item SET skuvariacao_valor2 = '{$this->nome}' WHERE skuvariacao_valor2_id = {$this->id}");
				
				$_SESSION['sucesso'] = tag('p', 'Novo valor adicionado.');
			}
		}else{
			$_SESSION['erro'] = tag('p',"Já existe um valor cadastrado com '{$this->nome}'.");
		}

	}

	public function exclui(){

		// asdf
		if($this->id>0){

			// Checa se existem pedidos relacionados
			if(rows(query("select * from item where skuvariacao_valor1_id = {$this->id}"))>0){
				throw new Exception("Existem itens relacionados, não é possível excluir");
			}

			if(rows(query("select * from item where skuvariacao_valor2_id = {$this->id}"))>0){
				throw new Exception("Existem itens relacionados, não é possível excluir");
			}

			// Executa exclusao
			return parent::exclui();

		}

	}

}

?>