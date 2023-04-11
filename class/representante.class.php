<?php

class representante extends base {

	var
		$id
		,$status
		,$cadastro_id
		,$endereco_id
		,$cod_integracao
		,$imagem
		,$st_aparece_site
		,$st_show_room;

	static function status(){
		return array( 'P' => 'Em analise', 'A' => 'Aprovado', 'R' => 'Reprovado' );
	}

	static function st_show_room(){
		return array( 'N' => 'Nao', 'S' => 'Sim' );
	}

	static function opcoes(){

		$return = array();

		$sql =
		"
		SELECT
			representante.id
			,cadastro.nome
		FROM
			representante
			,cadastro
		WHERE
			cadastro.id = representante.cadastro_id
		ORDER BY
			cadastro.nome
		";

		$query = query($sql);
		while($fetch=fetch($query)){
			$return[$fetch->id]=$fetch->nome;
		}

		return $return;

	}

	public function get_by_credenciais($email, $senha){

		$sql =
		"
		SELECT
			representante.*
		FROM
			representante
		INNER JOIN cadastro ON ( cadastro.id = representante.cadastro_id )
		AND representante.status = 'A'
		AND cadastro.email = '%1s'
		AND cadastro.senha = '%2s'
		";

		$query = query(sprintf($sql, $email, encode($senha)));

		$fetch = fetch($query);

		if(@$fetch->id){
			$this->load_by_fetch($fetch);
			return true;
		}

		return false;

	}

	public function is_email_cadastrado($email){

		$sql =
		"
		SELECT
			representante.*
		FROM
			representante
		INNER JOIN cadastro ON ( cadastro.id = representante.cadastro_id )
		AND cadastro.email = '%1s'
		";

		$query = query(sprintf($sql, $email));
		$fetch = fetch($query);

		if(@$fetch->id){
			return true;
		}

		return false;

	}

	public function is_valido_admin( $email, $senha ){

		$sql = "SELECT
					representante.id
				FROM
					representante
					,cadastro
				WHERE
					representante.cadastro_id = cadastro.id
				AND representante.status = 'A'
				AND cadastro.email = '%s'
				AND cadastro.senha = '%s'" ;

		$query = query(sprintf($sql, ($email), ($senha) ));
		$fetch=fetch($query);

		if(@$fetch->id){
			$this->get_by_id($fetch->id);
			return true;
		}

		return false;
	}

}

?>