<?php

class materia_prima extends caracvalor {

	public function get_table_name(){
		return 'caracvalor';
	}

	public function __construct($id=0){
		parent::__construct($id);
	}

	static function opcoesByItem($item){

		$return = array();

		$query = query(
				$sql="SELECT
						caracvalor.id
						,caracvalor.nome
					FROM
						itemcarac
					INNER JOIN caracvalor ON (itemcarac.caracvalor_id = caracvalor.id)
					WHERE
						1=1
					AND st_ativo = 'S'
					AND itemcarac.item_id = ".intval($item->id)."
					AND itemcarac.carac_id = 1
					ORDER BY
						caracvalor.nome
					");

		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}

}

?>