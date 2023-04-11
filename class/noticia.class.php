<?php

class noticia extends base {

	var
		$id
		,$st_ativo
		,$st_fixo
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

	public function getDataPublicacaoFormatado(){
		return formata_data_br($this->data_publicacao);
	}

	public function salva(){
		$return = false;
		if(parent::salva()){
			$file_imagem = @$_FILES['file_imagem'];
			if($file_imagem['name']!='' ) {
				$image_name = "img/noticia/{$this->id}.jpg" ;
				$path_fisico = "../{$image_name}";
				//echo $path_fisico;
				@unlink($path_fisico);
				copy($file_imagem['tmp_name'], $path_fisico);

				query("UPDATE noticia SET imagem = '{$image_name}' WHERE id = {$this->id}");
			}
		}
		return $return;
	}
}
?>