<?php

class blogtag extends base {

	var
		$id
		,$st_ativo
		,$nome
		,$data_cadastro;
		
	public function qtdQtdPosts(){
		return query_col(
			"
			SELECT 
				COUNT(id) 
			FROM 
				blogposttag 
			INNER JOIN blogpost ON ( 
				blogposttag.blogpost_id = blogpost.id 
			AND blogpost.st_ativo = 'S' 
			) 
			WHERE 
				blogtag_id = {$this->id}"
		);
	}
	
}
?>