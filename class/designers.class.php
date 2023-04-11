<?php
class designers extends base {
	var
		$id
		,$nome
		,$descricao
		,$imagem
		,$st_ativo;
		


		// Retorna uma lista simples
	static function opcoes(){

		$return = array();
		$query = query($sql="SELECT * FROM designers ORDER BY nome");
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		return $return;

	}
		
}
?>