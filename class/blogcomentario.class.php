<?php

class blogcomentario extends base {

	var
		$id
		,$st_ativo
		,$st_aprovado
		,$blogpost_id
		,$nome
		,$twitter
		,$conteudo
		,$data_cadastro
		,$data_publicacao;

	public function getDataPublicacaoFormatado(){
		return formata_data_br($this->data_publicacao);
	}

	public function getDataPublicacaoExtenso(){
		return formata_data_extenso(formata_data_br($this->data_publicacao));
	}
}
?>