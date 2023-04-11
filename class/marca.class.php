<?php

class marca extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
		,$referencia
		,$nome
		,$nome_es
		,$nome_in
		,$descricao
		,$descricao_es
		,$descricao_in
		,$imagem
		,$imagem_off
		,$ordem
		,$data_cadastro
		,$link
		;

	// Salva marca no banco
	public function salva(){
	
		if(parent::salva()){

			$file_imagem = @$_FILES['imagem'];
			$file_imagem_off = @$_FILES['imagem_off'];

			if($file_imagem['tmp_name']){

				$file = $file_imagem;

				list($width, $height) = getimagesize($file['tmp_name']);
				if($width!=110||$height!=90){
					$_SESSION['erro'] = tag('p','A imagem ('.$file['name'].') deve ter 110x90px');
					return true;
				}
				
				$image_name = stringAsTag($this->nome.' '.$this->id).'.jpg' ;

				foreach ( array('img/marcas/') as $path ){

					$path_fisico = "{$path}{$image_name}";

					@unlink($path_fisico);
					copy($file['tmp_name'], $path_fisico);

					query("UPDATE marca SET imagem = '{$image_name}' WHERE id = {$this->id} ");
				}
			}

			if($file_imagem_off['tmp_name']){

				$file = $file_imagem_off;

				list($width, $height) = getimagesize($file['tmp_name']);
				if($width!=110||$height!=90){
					$_SESSION['erro'] = tag('p','A imagem ('.$file['name'].') deve ter 110x90px');
					return true;
				}
				
				$image_name = stringAsTag($this->nome.' off '.$this->id).'.jpg' ;

				foreach ( array('img/marcas/') as $path ){

					$path_fisico = "{$path}{$image_name}";

					@unlink($path_fisico);
					copy($file['tmp_name'], $path_fisico);

					query("UPDATE marca SET imagem_off = '{$image_name}' WHERE id = {$this->id} ");
				}
			}

			return true;
		}
		return false;
	}
		
	// Retorna link para todos os produtos da marca
	public function getLinkProdutos(){
		return INDEX.'prods/?marca[]='.urlencode($this->nome);
	}

	public function getNomeResumo(){
		$len = 9;
		$conteudo = strip_tags($this->nome);
		return strlen($conteudo)>$len?substr($conteudo,0,$len).'...':$conteudo;
	}
	
	// Retorna uma lista simples
	static function opcoes(){

		$return = array();
		$query = query($sql="SELECT * FROM marca ORDER BY nome");
		while($fetch=fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}
		return $return;

	}
	
}

?>