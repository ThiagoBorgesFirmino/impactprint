<?php

// Modelo de dados
class uf extends base {

	var
		$id
		,$sigla
		,$uf
		,$capital
	;

	static function opcoes(){

		$ret = array();

		$query = query($sql="SELECT * FROM uf ORDER BY sigla");
		while($fetch=fetch($query)){
			$uf = new uf();
			$uf->load_by_fetch($fetch);
			$ret[] = $uf;
		}

		return $ret;

	}

}

?>