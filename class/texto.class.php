<?php

class texto extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$nome
		,$nome_es
		,$nome_in
		,$conteudo
		,$conteudo_es
		,$conteudo_in
		,$data_cadastro;

	const NOSSA_EMPRESA_ID = 2 ;
	const GARANTIA_ID = 3 ;

	public function __get($chave){

		traduz($this);

		// handler de metodo get_by_<nome_do_campo_no_model>
		if(preg_match('/([a-z]{1,})_(conteudo)/',$chave,$match)) {
			list(,$titulo,$campo) = $match;
			//printr($match);
			$sql="SELECT {$campo} FROM texto WHERE lower(titulo) = lower('{$titulo}')";
			//printr($sql);
			return query_col($sql);
		}

		if(preg_match('/([0-9]{1,})_(nome)/',$chave,$match)) {
			list(,$id,$campo) = $match;
			//printr($match);
			$sql="SELECT {$campo} FROM texto WHERE id = {$id}";
			//printr($sql);
			return query_col($sql);
		}

		if(preg_match('/([0-9]{1,})_(conteudo)/',$chave,$match)) {
			list(,$id,$campo) = $match;
			//printr($match);
			$sql="SELECT {$campo} FROM texto WHERE id = {$id}";
			//printr($sql);
			return query_col($sql);
		}
	}

}

?>