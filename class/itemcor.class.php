<?php

class itemcor extends base {

	var
		$id
		,$st_ativo
		,$item_id
		,$cor_id
		,$imagem
		,$referencia
		,$qtd_estoque
		,$st_default;

	private
		$file_imagem;

	public function set_file($file_imagem){
		$this->file_imagem = $file_imagem;
	}

	public function salva2(){
		return parent::salva();
	}
	public function salva(){

		if(parent::salva()){

			if($this->file_imagem&&$this->file_imagem['tmp_name']){

				$erros = array();

				if(!isImagemJPG($this->file_imagem['name'])){
					$erros[] = 'A imagem '.$file_imagem['name'].' deve ser .JPEG';
				}

				
				// if(!isImagemComTamanhoMinimo($this->file_imagem['tmp_name'],1000,1000)){
					// $erros[] = 'A imagem '.$this->file_imagem['name'].' deve ter no minimo 1000px por 1000px';
				// }

				if(sizeof($erros)==0){
				
					$this->imagem = "{$this->cor_id}_{$this->item_id}_{$this->id}.jpg";
					$path_fisico = 'img/produtos/'.$this->imagem;
					@unlink($path_fisico);
					move_uploaded_file($this->file_imagem['tmp_name'], $path_fisico);
						
					// foreach( array(PATH_PEQ, PATH_INT, PATH_MED, PATH_GRD, PATH_GIG) as $path ){

						// $path_fisico = $path.$this->item_id.'_'.$this->id.'.jpg';

						// copy($this->file_imagem['tmp_name'], $path_fisico);

						// $temp = file_get_contents($path.'_tamanho.txt');

						// $width = (int) preg_replace( '/[a-zA-Z\/_-]/', '', $temp);
						// $height= (int) preg_replace( '/[a-zA-Z\/_-]/', '', $temp);

						// recortaImagem($path_fisico, $width, $height);

						// if(config::get('HABILITA_MARCA_DAGUA')=='S'){

							// $path_marca_dagua = "{$path}_marca-dagua.png";
							// @unlink($path_marca_dagua);

							// if(!file_exists($path_marca_dagua)){
								// copy('img/produtos/_marca-dagua.png', $path_marca_dagua);
								// recortaImagemPng($path_marca_dagua, $width, $height);
							// }

							// $watermark = imagecreatefrompng($path_marca_dagua);
							// $image = imagecreatefromjpeg($path_fisico);

							// imagecopymerge_alpha($image, $watermark, 0, 0, 0, 0, $width, $height, 0);

							// imagejpeg($image, $path_fisico);
						// }
					// }

					query("UPDATE
								itemcor
							SET
								imagem = '{$this->imagem}'
							WHERE
								id={$this->id}");


				}
				else {
					$_SESSION['erro'] = join('<br />',$erros);
				}
			}

			if(@$this->st_default=='S'){
				//query("UPDATE item SET imagem = '{$this->imagem}' WHERE id = {$this->item_id}");
			}
		}
	}
}

?>