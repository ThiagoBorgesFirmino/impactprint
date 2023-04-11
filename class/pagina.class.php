<?php

class pagina extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$st_tipopagina
		,$st_especial
		,$chave
		,$pagina_id
		,$nome
		,$nome_es
		,$nome_in
		
		,$telefone
		,$email
		
		,$conteudo
		,$conteudo_es
		,$conteudo_in
		,$imagem
		,$var_name
		,$var_value
		,$var_value_es
		,$var_value_in
		,$data_cadastro
		,$ordem;

	static function opcoes(){

		$return = array();

		$query = query("SELECT id, chave, nome FROM pagina WHERE st_ativo = 'S' ORDER BY nome");

		while($fetch=fetch($query)){
			$return[$fetch->chave] = $fetch->nome;
		}

		return $return ;
	}

	static function opcoesHTML(){

		$return = array();

		$query = query(
				"
				SELECT
					id
					,chave
					,nome
				FROM
					pagina
				WHERE
					st_ativo = 'S'
				AND pagina_id = 0
				AND st_tipopagina = 'THTML'
				".($_SESSION['CADASTRO']->email!='dev@ajung.com.br'?
					"AND pagina.chave IN (
						select valor from config where st_tipocampo = 'TPAGINA'
					)":'')."
				ORDER BY
					nome");

		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return ;
	}

}

?>