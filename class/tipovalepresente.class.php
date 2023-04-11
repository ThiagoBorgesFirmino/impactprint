<?php

class tipovalepresente extends base {

	var
		$id
		,$st_ativo
		,$nome
		,$valor
		,$imagem1
		,$imagem2
		,$ordem
		,$data_cadastro;

	public function validaDados(&$erro=array()){

		// $this->valor = tofloat($this->valor);
	
		if($this->nome==''){
			$erro[] = 'Nome não pode estar vazio';
		}

		return sizeof($erro)==0;

	}

	public function getValorFormatado(){
		return money($this->valor);
	}
	
	public function getNomeTag(){
		return stringAsTag($this->nome);
	}

	public function getDescricaoHtml(){
		return nl2br($this->descricao);
	}
	
	public function getLink(){
		return INDEX.'prods/'.$this->id.'/'.urlencode($this->nome).'/#c';
	}
	
	public function salva(){
		if(parent::salva()){

			$file_imagem1 = @$_FILES['imagem1_'.$this->id];
			$file_imagem2 = @$_FILES['imagem2_'.$this->id];

			if($file_imagem1['tmp_name']){

				list($width, $height) = getimagesize($file_imagem1['tmp_name']);
				
				if($width!=315  && $height != 204){
					$_SESSION['erro'] = tag('p','A imagem ('.$file_imagem1['name'].') deve ter 315x204 pixels no tamanho');
					return false;
				}

				$image_name = stringAsTag($this->nome.' 1 '.$this->id).'.jpg' ;

				foreach ( array('img/tipovalepresente/') as $path ){

					$path_fisico = "{$path}{$image_name}";

					//echo $path_fisico;

					@unlink($path_fisico);
					copy($file_imagem1['tmp_name'], $path_fisico);

					query("UPDATE tipovalepresente SET imagem1 = '{$image_name}' WHERE id = {$this->id} ");
				}
			}

			if($file_imagem2['tmp_name']){

				list($width, $height) = getimagesize($file_imagem2['tmp_name']);
				
				if($width!=765){
					$_SESSION['erro'] = tag('p','A imagem ('.$file_imagem2['name'].') deve ter 765 pixels de largura');
					return false;
				}

				$image_name = stringAsTag($this->nome.' '.$this->id).'.jpg' ;

				foreach ( array('img/categoria/tema2/') as $path ){

					$path_fisico = "{$path}{$image_name}";

					//echo $path_fisico;

					@unlink($path_fisico);
					copy($file_imagem_tema2['tmp_name'], $path_fisico);

					//$temp = file_get_contents($path.'_tamanho.txt');

					//$width = (int) preg_replace( '/[a-zA-Z\/_-]/', '', $temp);
					//$height= (int) preg_replace( '/[a-zA-Z\/_-]/', '', $temp);

					//if($width!=

					//recortaImagem($path_fisico, $width, $height);
					query("UPDATE categoria SET imagem_tema2 = '{$image_name}' WHERE id = {$this->id} ");
				}

			}
			
			return true;
		}
		return false;
	}

	static function opcoes($categoria_id=0){

		$return = array();

		$query = query($sql = "SELECT * FROM categoria WHERE categoria_id = {$categoria_id} ORDER BY ordem ");

		while($fetch = fetch($query)){
			$return[$fetch->id] = $fetch->nome;
		}

		return $return;
	}
}

?>