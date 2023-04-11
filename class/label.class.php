<?php

class label extends base {

	var
		$id
		,$chave
		,$valor
		,$valor_in
		,$valor_es
		,$dt_alteracao
		,$dt_cadastro;

	public function __get($chave){

		$this->reset_vars();

		$this->get_by_chave($chave);

		if(!$this->id){
			return 'criar o label: '.$chave;
		}

		traduz($this);

		return $this->valor;

		//return nl2br(query_col("SELECT valor FROM config WHERE chave = '{$chave}'"));
	}

	static function get($chave){

		$this->reset_vars();

		$this->get_by_chave($chave);

		if(!$this->id){
			return 'criar o label: '.$chave;
		}

		traduz($this);

		return $this->valor;
	}
}

?>
