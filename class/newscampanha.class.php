<?php

class newscampanha extends base {

	var
		$id
		,$nome
		,$assunto
		,$html
		,$data_cadastro
		,$data_inicio
		,$data_fim
		,$lista_sql;

	public function validaDados(& $erros=array()){

		if($this->nome==""){
			$erros[] = "digite o nome da campanha";
		}
		if($this->assunto==""){
			$erros[] = "digite o assunto da campanha";
		}
		if($this->html==""){
			$erros[] = "html da campanha invalido";
		}

		return sizeof($erros)==0;

	}

}

?>