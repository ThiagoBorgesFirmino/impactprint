<?php

class grupobrindes extends base {

	var
		$id
		,$grupo
		,$valor_inicial
		,$valor_final
		,$st_ativo;

	public function exclui(){

		if($this->id>0){

			// Checa se existem pedidos relacionados
			if(rows(query($sql="SELECT * FROM itemgrupobrindes WHERE grupobrindes_id = {$this->id}"))>0){
				throw new Exception("Itens relacionados, não é possível excluir ({$this->grupo})");
			}
			
			// query("DELETE FROM itemgrupobrindes WHERE item_id = {$this->id}");

			// Executa exclusao
			return parent::exclui();

		}

	}

}

?>