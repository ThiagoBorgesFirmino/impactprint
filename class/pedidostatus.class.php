<?php

class pedidostatus extends base {

	var
		$id
		,$st_fixo
		,$descricao
		,$ordem;

	public function validaDados(&$erro=array()){
		if($this->descricao==''){
			$erro[] = 'Descrição não pode estar vazia';
		}
		return sizeof($erro)==0;
	}
		
	static function opcoes(){
		$opcoes = array();
		$query = query($sql="SELECT id, descricao FROM pedidostatus ORDER BY ordem, descricao");
		while($fetch=fetch($query)){
			$opcoes[$fetch->id] = $fetch->descricao;
		}
		// printr($sql);
		// printr($opcoes);
		return $opcoes;
	}

	public function exclui(){

		$return = false;

		$id = intval($this->id);

		if($id>0){

			// Checa se tem pedidos associados
			if(rows(query("SELECT * FROM pedido WHERE pedidostatus_id = {$id}"))>0){
				throw new Exception("Não é possível excluir existem pedidos associados");
			}

			// Checa se o status é fixo ou não
			if($this->st_fixo=='S'){
				throw new Exception("Não é possível excluir esse status, ele é fixo para o sistema");
			}

			// Executa exclusao
			$return = parent::exclui();

		}

		return $return;

	}

}

?>