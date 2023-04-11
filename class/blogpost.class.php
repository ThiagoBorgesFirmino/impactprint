<?php

class blogpost extends base {

	var
		$id
		,$st_ativo
		,$autor
		,$titulo
		,$titulo_es
		,$titulo_in
		,$chamada
		,$chamada_es
		,$chamada_in
		,$conteudo
		,$conteudo_es
		,$conteudo_in
		,$data_cadastro
		,$data_publicacao;

	public function getLink(){
		return INDEX.'post/'.$this->id.'/'.urlencode($this->titulo);
	}
		
	public function getDataPublicacaoFormatado(){
		return formata_data_br($this->data_publicacao);
	}

	public function getDataPublicacaoExtenso(){
		return formata_data_extenso(formata_data_br($this->data_publicacao));
	}
	
	public function getDiaPublicacao(){
		list($yyyy, $mm, $dd) = explode('-', $this->data_publicacao);
		return substr($mm,0,2);
	}
	
	public function getMesPublicacaoAbreviado(){
		list($yyyy, $mm, $dd) = explode('-', $this->data_publicacao);
		$mm = get_mes($mm);
		return substr($mm,0,3);
	}
	
	public function getTagsHtml(){
	
		$return = '';
	
		$sql = 
			"
			SELECT 
				blogtag.id
				,blogtag.nome
			FROM
				blogtag
			INNER JOIN blogposttag ON (
				blogposttag.blogpost_id = {$this->id}
			AND	blogposttag.blogtag_id = blogtag.id
			)
			INNER JOIN blogpost ON (
				blogposttag.blogpost_id = {$this->id}
			AND	blogpost.st_ativo = 'S'
			)
			WHERE 
				blogtag.st_ativo = 'S'
			GROUP BY 
				blogtag.id
				,blogtag.nome
			ORDER BY
				nome			
			" ;
			
		$query = query($sql);
		while($fetch = fetch($query)){
			if($return !=''){
				$return .= ', ';
			}
			$return .= tag('a href="'.INDEX.'tag/'.$fetch->id.'/'.urlencode($fetch->nome).'"' ,$fetch->nome);
		}
	
		if($return !=''){
			$return = 'Tags: '.$return;
		}
	
		return $return;
	
	}

	public function qtdQtdComentarios(){
		return query_col(
			"
			SELECT 
				COUNT(id) 
			FROM 
				blogcomentario 
			WHERE 
				blogcomentario.blogpost_id = {$this->id}
			AND blogcomentario.st_aprovado = 'S' "
		);
	}
}
?>