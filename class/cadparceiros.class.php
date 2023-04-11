<?php
class cadparceiros extends base {
	var
	$id
	,$nome
	,$imagem_acesa
	,$imagem_apagada
	,$link
	,$st_ativo
	,$ordem;

	
	public function salva(){
	
		if(parent::salva()){

			$file_imagem = @$_FILES['imagem_acesa'];
			$file_imagem_off = @$_FILES['imagem_apagada'];

			if($file_imagem['tmp_name']){

				$file = $file_imagem;

				list($width, $height) = getimagesize($file['tmp_name']);
				// printr("largura - ".$width);				
				// printr("altura - ".$height);				
				if($width>90||$height!=30){
					$_SESSION['erro'] = tag('p','A imagem ('.$file['name'].') deve ter no max 90px de largura e exatos 30px de altura.');
					return true;
				}
				
				$image_name = stringAsTag($this->nome.' '.$this->id).'.jpg' ;

				foreach ( array('img/parceiros/') as $path ){

					$path_fisico = "{$path}{$image_name}";

					@unlink($path_fisico);
					copy($file['tmp_name'], $path_fisico);

					query("UPDATE cadparceiros SET imagem_acesa = '{$image_name}' WHERE id = {$this->id} ");
				}
			}

			if($file_imagem_off['tmp_name']){

				$file = $file_imagem_off;

				list($width, $height) = getimagesize($file['tmp_name']);
				if($width>90||$height!=30){
					$_SESSION['erro'] = tag('p','A imagem_acesa ('.$file['name'].') deve ter no max 90px de largura e exatos 30px de altura.');
					return true;
				}
				
				$image_name = stringAsTag($this->nome.' off '.$this->id).'.jpg' ;

				foreach ( array('img/parceiros/') as $path ){

					$path_fisico = "{$path}{$image_name}";

					@unlink($path_fisico);
					copy($file['tmp_name'], $path_fisico);

					query("UPDATE cadparceiros SET imagem_apagada = '{$image_name}' WHERE id = {$this->id} ");
				}
			}

			return true;
		}
		return false;
	}
	
}


?>