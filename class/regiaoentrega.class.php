<?php

// Modelo de dados
class regiaoentrega extends base {

	var
		$id
		,$tipo
		,$descricao
		,$uf
		,$cep_inicial
		,$cep_final
	;

	static function opcoes_tipo($categoria_id=0){

		return array(
			'ESTADO' => 'Por estado'
			,'FAIXACEP' => 'Por faixa de CEP'
		);

	}

	static function opcoes(){

		$ret = array();

		$query = query($sql="SELECT * FROM regiaoentrega ORDER BY descricao");
		while($fetch=fetch($query)){
			$regiaoentrega = new regiaoentrega();
			$regiaoentrega->load_by_fetch($fetch);
			$ret[] = $regiaoentrega;
		}

		return $ret;

	}

}

?>