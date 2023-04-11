<?php 

class linha extends base {

	var
		$id,
		$status,
		$fixo,
		$lista_menu,
		$titulo,
		$titulo_es,
		$titulo_in,
		$titulo_tag,
		$descricao,
		$descricao_es,
		$descricao_in,
		$ordem,
		$imagem,
		$data_cadastro;

	static function opcoes(){
	
		$return = array();

		$query = query('SELECT id, titulo FROM linha ORDER BY titulo');
		while($fetch=fetch($query)){
			$return[$fetch->id]=$fetch->titulo ;
		}

		return $return ;
	
	}

}

?>